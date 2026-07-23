<?php

namespace Tests\Feature;

use App\Enums\ProductType;
use App\Enums\RequisitionType;

use App\Models\AlternativeMaterial;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Services\RequisitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlternativeMaterialAndLocationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $warehouse;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'ADMIN']);
        $this->warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'คลังหลัก', 'is_active' => true]);
        $this->unit = Unit::create(['code' => 'PCS', 'name' => 'ชิ้น', 'is_active' => true]);
    }

    public function test_can_create_warehouse_location(): void
    {
        $location = WarehouseLocation::create([
            'warehouse_id' => $this->warehouse->id,
            'code' => 'A-01-2',
            'name' => 'โซน A ชั้น 1 ล็อก 2',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('warehouse_locations', [
            'code' => 'A-01-2',
            'name' => 'โซน A ชั้น 1 ล็อก 2',
        ]);

        $product = Product::create([
            'code' => 'PART-LOC-1',
            'name' => 'อะไหล่ระบุล็อก',
            'product_type' => ProductType::PART,
            'unit_id' => $this->unit->id,
            'warehouse_location_id' => $location->id,
            'standard_cost' => 10,
            'sale_price' => 20,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $this->assertEquals('A-01-2', $product->location->code);
    }

    public function test_build_requisition_uses_alternative_material_when_primary_short(): void
    {
        $primaryPart = Product::create([
            'code' => 'P-MAIN',
            'name' => 'ดอกลำโพงหลัก',
            'product_type' => ProductType::PART,
            'unit_id' => $this->unit->id,
            'standard_cost' => 100,
            'sale_price' => 150,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $altPart = Product::create([
            'code' => 'P-ALT',
            'name' => 'ดอกลำโพงทดแทน',
            'product_type' => ProductType::PART,
            'unit_id' => $this->unit->id,
            'standard_cost' => 95,
            'sale_price' => 140,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $wip = Product::create([
            'code' => 'WIP-SPEAKER',
            'name' => 'ตู้ลำโพงประกอบ',
            'product_type' => ProductType::WIP,
            'unit_id' => $this->unit->id,
            'standard_cost' => 200,
            'sale_price' => 300,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $wip->components()->attach($primaryPart->id, ['quantity' => 1]);

        AlternativeMaterial::create([
            'product_id' => $wip->id,
            'primary_product_id' => $primaryPart->id,
            'alternative_product_id' => $altPart->id,
            'conversion_factor' => 1.0000,
            'note' => 'ใช้อะไหล่สำรอง P-ALT แทน P-MAIN',
        ]);

        // Give stock only to alternative part
        StockBalance::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $altPart->id,
            'quantity' => 50,
        ]);

        StockBalance::create([
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $primaryPart->id,
            'quantity' => 0,
        ]);

        $service = app(RequisitionService::class);
        $requisition = $service->create([
            'request_type' => RequisitionType::BUILD_WIP->value,
            'warehouse_id' => $this->warehouse->id,
            'target_product_id' => $wip->id,
            'target_quantity' => 2,
            'purpose' => 'ประกอบตู้ลำโพงทดสอบ',
        ], $this->admin);

        $this->assertCount(1, $requisition->items);
        $item = $requisition->items->first();
        $this->assertEquals($altPart->id, $item->product_id);
        $this->assertEquals(2, (float)$item->quantity);
        $this->assertStringContainsString('ส่วนประกอบทดแทนตามสูตร', $item->note);
    }
}
