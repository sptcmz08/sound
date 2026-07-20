<?php

namespace Tests\Feature;

use App\Enums\ProductType;
use App\Enums\RequisitionStatus;
use App\Enums\RequisitionType;
use App\Models\Product;
use App\Models\Requisition;
use App\Models\StockBalance;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequisitionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private Unit $unit;

    private Warehouse $warehouse;

    private Product $part;

    private Product $wip;

    private function signatureData(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ADMIN, 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => User::STOCK_STAFF, 'is_active' => true]);
        $this->unit = Unit::create(['code' => 'PCS', 'name' => 'ชิ้น', 'is_active' => true]);
        $this->warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'คลังหลัก', 'is_active' => true]);
        $base = ['unit_id' => $this->unit->id, 'minimum_stock' => 0, 'is_active' => true, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id];
        $this->part = Product::create($base + ['code' => 'NUT-14', 'name' => 'น็อต 1/4 นิ้ว', 'product_type' => ProductType::PART]);
        $this->wip = Product::create($base + ['code' => 'WIP-01', 'name' => 'WIP ลำโพง A', 'product_type' => ProductType::WIP]);
        $this->wip->components()->attach($this->part->id, ['quantity' => 4]);
    }

    public function test_admin_keys_stock_receipt_without_barcode(): void
    {
        $this->actingAs($this->admin)->post(route('stock.receive.store'), [
            'product_id' => $this->part->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 100,
        ])->assertRedirect(route('products.index'));

        $this->assertSame('100', StockBalance::where('product_id', $this->part->id)->first()->quantity);
        $this->actingAs($this->admin)->get(route('products.index'))
            ->assertOk()
            ->assertSee('รับสินค้าเข้าสต็อก')
            ->assertSee('data-open-receive', false);
        $this->actingAs($this->staff)->get(route('stock.receive'))->assertForbidden();
    }

    public function test_withdraw_production_and_issue_menus_are_separated(): void
    {
        $this->actingAs($this->staff)
            ->get(route('requisitions.withdraw'))
            ->assertOk()
            ->assertSee('เบิกสินค้า')
            ->assertSee('PART')
            ->assertSee('WIP')
            ->assertSee('FG พร้อมขาย');

        $this->actingAs($this->staff)
            ->get(route('requisitions.production'))
            ->assertOk()
            ->assertSee('ผลิต WIP')
            ->assertSee('ผลิต FG');

        $this->actingAs($this->staff)
            ->get(route('requisitions.create', ['type' => RequisitionType::ISSUE_WIP->value]))
            ->assertOk()
            ->assertSee('value="ISSUE_WIP" checked', false)
            ->assertDontSee('value="GENERAL_ISSUE"', false)
            ->assertDontSee('3. ข้อมูลคำขอ')
            ->assertDontSee('วัตถุประสงค์');

        $this->actingAs($this->staff)->post(route('requisitions.store'), [
            'request_type' => RequisitionType::ISSUE_WIP->value,
            'warehouse_id' => $this->warehouse->id,
            'items' => [['product_id' => $this->wip->id, 'quantity' => 1]],
        ])->assertRedirect();

        $this->assertDatabaseHas('requisitions', [
            'request_type' => RequisitionType::ISSUE_WIP->value,
            'purpose' => RequisitionType::ISSUE_WIP->label(),
        ]);

        $this->actingAs($this->staff)->get(route('requisitions.issues'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('requisitions.issues'))->assertOk()->assertSee('จ่ายสินค้า');
    }

    public function test_staff_can_name_a_new_wip_and_pick_parts_without_request_fields(): void
    {
        $this->actingAs($this->staff)
            ->get(route('requisitions.create', ['type' => RequisitionType::BUILD_WIP->value]))
            ->assertRedirect(route('requisitions.wip.create'));

        $this->actingAs($this->staff)
            ->get(route('requisitions.wip.create'))
            ->assertOk()
            ->assertSee('ชื่อ WIP')
            ->assertSee($this->part->code)
            ->assertDontSee('แผนก / หน่วยงาน')
            ->assertDontSee('วัตถุประสงค์');

        $this->actingAs($this->staff)->post(route('requisitions.wip.store'), [
            'wip_name' => 'WIP ทดสอบรุ่นใหม่',
            'output_quantity' => 3,
            'warehouse_id' => $this->warehouse->id,
            'components' => [['product_id' => $this->part->id, 'quantity' => 2]],
        ])->assertRedirect();

        $newWip = Product::where('name', 'WIP ทดสอบรุ่นใหม่')->firstOrFail();
        $this->assertSame(ProductType::WIP, $newWip->product_type);
        $this->assertStringStartsWith('WIP-', $newWip->code);
        $this->assertEquals(2, $newWip->components()->first()->pivot->quantity);
        $this->assertSame('6', Requisition::latest('id')->firstOrFail()->items()->first()->quantity);
    }

    public function test_saved_wip_recipe_can_be_selected_and_reused_without_duplicate_product(): void
    {
        $this->actingAs($this->staff)
            ->get(route('requisitions.wip.create'))
            ->assertOk()
            ->assertSee('เลือก WIP ที่เคยสร้าง')
            ->assertSee($this->wip->name)
            ->assertSee('"product_id":'.$this->part->id, false);

        $wipCount = Product::where('product_type', ProductType::WIP)->count();

        $this->actingAs($this->staff)->post(route('requisitions.wip.store'), [
            'existing_wip_id' => $this->wip->id,
            'output_quantity' => 2,
            'warehouse_id' => $this->warehouse->id,
            'components' => [['product_id' => $this->part->id, 'quantity' => 5]],
        ])->assertRedirect();

        $this->assertSame($wipCount, Product::where('product_type', ProductType::WIP)->count());
        $this->assertEquals(5, $this->wip->components()->firstOrFail()->pivot->quantity);
        $requisition = Requisition::latest('id')->firstOrFail();
        $this->assertSame($this->wip->id, $requisition->target_product_id);
        $this->assertSame('10', $requisition->items()->firstOrFail()->quantity);
    }

    public function test_admin_created_wip_is_saved_and_approved_without_drawing_signature(): void
    {
        $this->actingAs($this->admin)->post(route('stock.receive.store'), [
            'product_id' => $this->part->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity' => 20,
        ]);

        $response = $this->actingAs($this->admin)->post(route('requisitions.wip.store'), [
            'wip_name' => 'WIP แอดมินสร้าง',
            'output_quantity' => 2,
            'warehouse_id' => $this->warehouse->id,
            'components' => [['product_id' => $this->part->id, 'quantity' => 2]],
        ])->assertRedirect();

        $product = Product::where('name', 'WIP แอดมินสร้าง')->firstOrFail();
        $requisition = Requisition::where('target_product_id', $product->id)->firstOrFail();
        $this->assertSame(RequisitionStatus::APPROVED, $requisition->status);
        $this->assertSame($this->admin->id, $requisition->approved_by);
        $this->assertNull($requisition->approval_signature);
        $this->assertSame('16', StockBalance::where('product_id', $this->part->id)->firstOrFail()->quantity);
        $this->assertSame('2', StockBalance::where('product_id', $product->id)->firstOrFail()->quantity);
        $response->assertRedirect(route('requisitions.index', ['focus' => $requisition->id]));

        $this->actingAs($this->admin)->get(route('requisitions.index', ['focus' => $requisition->id]))
            ->assertOk()
            ->assertSee('รายการเบิกและผลิต')
            ->assertSee('อนุมัติและปรับสต็อกแล้ว')
            ->assertSee('สร้างโดย Admin')
            ->assertSee('data-open-process', false)
            ->assertSee('ดาวน์โหลด PDF');

        $this->actingAs($this->admin)->get(route('requisitions.show', $requisition))
            ->assertOk()
            ->assertSee('ดาวน์โหลด PDF')
            ->assertDontSee(route('requisitions.approve', $requisition), false);

        $this->actingAs($this->admin)->get(route('requisitions.print', $requisition))
            ->assertOk()
            ->assertSee('ใบเบิกพัสดุ')
            ->assertSee('ชื่อพนักงานผู้เบิก')
            ->assertSee('อนุมัติโดยผู้ดูแลระบบ')
            ->assertSee('เอกสารนี้ไม่ต้องลงลายเซ็น')
            ->assertDontSee($this->admin->email)
            ->assertDontSee('เอกสารสร้างจากระบบ Simple Stock')
            ->assertDontSee('ลายเซ็นผู้ขอเบิก');

        $pdf = $this->actingAs($this->admin)->get(route('requisitions.pdf', $requisition));
        $pdf->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertGreaterThan(10_000, strlen($pdf->getContent()));
        $this->assertStringContainsString('attachment;', $pdf->headers->get('content-disposition'));
    }

    public function test_build_wip_request_is_approved_and_posts_stock_atomically(): void
    {
        $this->actingAs($this->admin)->post(route('stock.receive.store'), ['product_id' => $this->part->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 50]);
        $this->actingAs($this->staff)->post(route('requisitions.store'), [
            'request_type' => RequisitionType::BUILD_WIP->value,
            'warehouse_id' => $this->warehouse->id,
            'target_product_id' => $this->wip->id,
            'target_quantity' => 3,
            'purpose' => 'ผลิตทดสอบ',
        ])->assertRedirect();

        $request = Requisition::firstOrFail();
        $this->assertSame('12', $request->items->first()->quantity);
        $this->assertSame(RequisitionStatus::PENDING, $request->status);

        $this->actingAs($this->admin)
            ->from(route('requisitions.show', $request))
            ->post(route('requisitions.approve', $request))
            ->assertSessionHasErrors();
        $this->assertSame(RequisitionStatus::PENDING, $request->fresh()->status);

        $this->actingAs($this->staff)->post(route('signature.update'), [
            'signature_data' => $this->signatureData(),
            'pin' => '2468',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($this->staff)->post(route('requisitions.sign', $request), ['pin' => '2468'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNotNull($request->fresh()->requester_signed_at);

        $this->actingAs($this->admin)->get(route('requisitions.show', $request))
            ->assertOk()
            ->assertDontSee('canvas id="signature"', false)
            ->assertSee('อนุมัติและบันทึกสต็อก');

        $this->actingAs($this->admin)->post(route('requisitions.approve', $request))->assertRedirect();

        $this->assertSame('38', StockBalance::where('product_id', $this->part->id)->first()->quantity);
        $this->assertSame('3', StockBalance::where('product_id', $this->wip->id)->first()->quantity);
        $this->assertSame(RequisitionStatus::APPROVED, $request->fresh()->status);
        $this->actingAs($this->staff)->get(route('requisitions.print', $request))
            ->assertOk()
            ->assertSee($request->request_no)
            ->assertSee('ลายเซ็นผู้ขอเบิก')
            ->assertSee('ลายเซ็นผู้อนุมัติ');
    }

    public function test_shortage_keeps_request_pending_and_does_not_create_wip(): void
    {
        $this->actingAs($this->admin)->post(route('stock.receive.store'), ['product_id' => $this->part->id, 'warehouse_id' => $this->warehouse->id, 'quantity' => 2]);
        $this->actingAs($this->staff)->post(route('requisitions.store'), ['request_type' => RequisitionType::BUILD_WIP->value, 'warehouse_id' => $this->warehouse->id, 'target_product_id' => $this->wip->id, 'target_quantity' => 1, 'purpose' => 'ทดสอบ']);
        $request = Requisition::firstOrFail();

        $this->actingAs($this->staff)->post(route('signature.update'), ['signature_data' => $this->signatureData(), 'pin' => '2468']);
        $this->actingAs($this->staff)->post(route('requisitions.sign', $request), ['pin' => '2468']);

        $this->actingAs($this->admin)->from(route('requisitions.show', $request))->post(route('requisitions.approve', $request))->assertSessionHasErrors();

        $this->assertSame(RequisitionStatus::PENDING, $request->fresh()->status);
        $this->assertSame('2', StockBalance::where('product_id', $this->part->id)->first()->quantity);
        $this->assertNull(StockBalance::where('product_id', $this->wip->id)->first());
    }
}
