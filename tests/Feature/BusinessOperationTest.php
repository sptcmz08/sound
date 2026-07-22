<?php

namespace Tests\Feature;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessOperationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Warehouse $warehouse;

    private Product $part;

    private Product $supply;

    private Product $wip;

    private Product $fg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ADMIN, 'is_active' => true]);
        $unit = Unit::create(['code' => 'PCS', 'name' => 'ชิ้น']);
        $this->warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'คลังหลัก']);
        $base = ['unit_id' => $unit->id, 'minimum_stock' => 0, 'standard_cost' => 0, 'sale_price' => 0, 'is_active' => true, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id];
        $this->part = Product::create($base + ['code' => 'PART-01', 'name' => 'PART ทดสอบ', 'product_type' => ProductType::PART]);
        $this->supply = Product::create($base + ['code' => 'SUP-01', 'name' => 'SUPPLY ทดสอบ', 'product_type' => ProductType::SUPPLY]);
        $this->wip = Product::create($base + ['code' => 'WIP-01', 'name' => 'WIP ทดสอบ', 'product_type' => ProductType::WIP]);
        $this->fg = Product::create(array_merge($base, ['code' => 'FG-01', 'name' => 'FG ทดสอบ', 'product_type' => ProductType::FG, 'standard_cost' => 8, 'sale_price' => 20]));
    }

    public function test_new_sidebar_pages_and_reports_render(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertSee('เบิก-จ่าย')->assertSee('ต้นทุน - กำไร');
        $this->actingAs($this->admin)->get(route('operations.create', 'supplier-receive'))
            ->assertOk()
            ->assertSee($this->part->code)
            ->assertSee($this->supply->code)
            ->assertSee($this->wip->code)
            ->assertSee($this->fg->code);
        foreach (['supplier-receive', 'sale', 'claim', 'waste'] as $operation) {
            $this->actingAs($this->admin)->get(route('operations.create', $operation))->assertOk();
        }
        foreach (['reports.cost-profit', 'reports.issue', 'reports.sales', 'reports.claims', 'reports.waste'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
        $this->actingAs($this->admin)->get(route('products.create'))
            ->assertOk()
            ->assertSee('สูตรผลิต FG (BOM)')
            ->assertSee('Option สำหรับหน้าขาย');
    }

    public function test_supplier_sale_claim_and_waste_update_stock_and_financial_data(): void
    {
        $this->postOperation('supplier-receive', $this->part, 10, ['unit_cost' => 5, 'contact_name' => 'Supplier A'])->assertRedirect();
        $this->postOperation('supplier-receive', $this->supply, 4, ['unit_cost' => 2, 'contact_name' => 'Supplier A'])->assertRedirect();
        $this->postOperation('supplier-receive', $this->wip, 3, ['unit_cost' => 7, 'contact_name' => 'Supplier B'])->assertRedirect();
        $this->postOperation('supplier-receive', $this->fg, 5, ['unit_cost' => 8, 'contact_name' => 'Supplier B'])->assertRedirect();
        $this->assertSame('10', StockBalance::where('product_id', $this->part->id)->value('quantity'));
        $this->assertSame('4', StockBalance::where('product_id', $this->supply->id)->value('quantity'));
        $this->assertSame('3', StockBalance::where('product_id', $this->wip->id)->value('quantity'));
        $this->assertSame('5', StockBalance::where('product_id', $this->fg->id)->value('quantity'));
        $this->assertSame('5', $this->part->fresh()->standard_cost);

        $this->postOperation('sale', $this->fg, 2, ['unit_price' => 20, 'contact_name' => 'Customer A'])->assertRedirect();
        $this->assertSame('3', StockBalance::where('product_id', $this->fg->id)->value('quantity'));
        $this->assertDatabaseHas('stock_document_items', ['product_id' => $this->fg->id, 'quantity' => 2, 'unit_cost' => 8, 'unit_price' => 20]);

        $this->postOperation('claim', $this->wip, 2, ['contact_name' => 'Customer B', 'note' => 'ชำรุดจากลูกค้า'])->assertRedirect();
        $this->assertSame('5', StockBalance::where('product_id', $this->wip->id)->value('quantity'));

        $this->postOperation('waste', $this->part, 1, ['note' => 'เสียจากการผลิต'])->assertRedirect();
        $this->assertSame('9', StockBalance::where('product_id', $this->part->id)->value('quantity'));
        $this->actingAs($this->admin)->get(route('reports.cost-profit'))->assertOk()->assertSee('24.00');
    }

    public function test_sale_with_fg_options_deducts_selected_part_and_wip_and_includes_option_cost(): void
    {
        $this->postOperation('supplier-receive', $this->part, 10, ['unit_cost' => 5, 'contact_name' => 'Supplier A']);
        $this->postOperation('supplier-receive', $this->wip, 10, ['unit_cost' => 7, 'contact_name' => 'Supplier A']);
        $this->postOperation('supplier-receive', $this->fg, 5, ['unit_cost' => 8, 'contact_name' => 'Supplier A']);

        $handleGroup = $this->fg->optionGroups()->create(['name' => 'รูปแบบการถือ', 'is_required' => true, 'sort_order' => 0]);
        $handleOption = $handleGroup->items()->create([
            'option_product_id' => $this->part->id,
            'quantity' => 2,
            'additional_price' => 50,
            'sort_order' => 0,
        ]);
        $karaokeGroup = $this->fg->optionGroups()->create(['name' => 'ระบบคาราโอเกะ', 'is_required' => false, 'sort_order' => 1]);
        $karaokeOption = $karaokeGroup->items()->create([
            'option_product_id' => $this->wip->id,
            'quantity' => 1,
            'additional_price' => 100,
            'sort_order' => 0,
        ]);

        $this->actingAs($this->admin)->post(route('operations.store', 'sale'), [
            'document_date' => today()->format('Y-m-d'),
            'warehouse_id' => $this->warehouse->id,
            'contact_name' => 'ลูกค้าทดสอบ',
            'idempotency_key' => (string) Str::uuid(),
            'items' => [[
                'product_id' => $this->fg->id,
                'quantity' => 2,
                'unit_price' => 170,
                'options' => [
                    ['product_option_item_id' => $handleOption->id],
                    ['product_option_item_id' => $karaokeOption->id],
                ],
            ]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('3', StockBalance::where('product_id', $this->fg->id)->value('quantity'));
        $this->assertSame('6', StockBalance::where('product_id', $this->part->id)->value('quantity'));
        $this->assertSame('8', StockBalance::where('product_id', $this->wip->id)->value('quantity'));
        $this->assertDatabaseHas('sale_item_options', ['product_option_item_id' => $handleOption->id, 'quantity' => 4]);
        $this->assertDatabaseHas('sale_item_options', ['product_option_item_id' => $karaokeOption->id, 'quantity' => 2]);
        $this->assertDatabaseHas('stock_document_items', ['product_id' => $this->fg->id, 'unit_cost' => 25, 'unit_price' => 170]);
    }

    public function test_sale_rejects_option_from_another_fg(): void
    {
        $otherFg = Product::create([
            'code' => 'FG-02', 'name' => 'FG อื่น', 'product_type' => ProductType::FG,
            'unit_id' => $this->part->unit_id, 'minimum_stock' => 0, 'standard_cost' => 0,
            'sale_price' => 0, 'is_active' => true, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id,
        ]);
        $foreignGroup = $otherFg->optionGroups()->create(['name' => 'Option ของสินค้าอื่น', 'is_required' => false]);
        $foreignOption = $foreignGroup->items()->create(['option_product_id' => $this->part->id, 'quantity' => 1]);

        $this->actingAs($this->admin)->post(route('operations.store', 'sale'), [
            'document_date' => today()->format('Y-m-d'),
            'warehouse_id' => $this->warehouse->id,
            'contact_name' => 'ลูกค้าทดสอบ',
            'idempotency_key' => (string) Str::uuid(),
            'items' => [[
                'product_id' => $this->fg->id,
                'quantity' => 1,
                'unit_price' => 20,
                'options' => [['product_option_item_id' => $foreignOption->id]],
            ]],
        ])->assertSessionHasErrors('items');

        $this->assertDatabaseCount('stock_documents', 0);
    }

    private function postOperation(string $operation, Product $product, int $quantity, array $extra = [])
    {
        return $this->actingAs($this->admin)->post(route('operations.store', $operation), array_merge([
            'document_date' => today()->format('Y-m-d'),
            'warehouse_id' => $this->warehouse->id,
            'idempotency_key' => (string) Str::uuid(),
            'items' => [['product_id' => $product->id, 'quantity' => $quantity] + array_intersect_key($extra, ['unit_cost' => true, 'unit_price' => true])],
        ], array_diff_key($extra, ['unit_cost' => true, 'unit_price' => true])));
    }

}
