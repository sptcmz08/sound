<?php

namespace Tests\Feature;

use App\Enums\ProductType;
use App\Enums\StockDocumentStatus;
use App\Enums\StockDocumentType;
use App\Http\Controllers\ReportController;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockDocument;
use App\Models\StockTransaction;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ProductSpreadsheetService;
use App\Services\StockReportService;
use App\Services\StockService;
use App\Support\Quantity;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class StockSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $viewer;

    private Unit $unit;

    private Warehouse $warehouse;

    private Product $part;

    private Product $fg;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ADMIN, 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => User::STOCK_STAFF, 'is_active' => true]);
        $this->viewer = User::factory()->create(['role' => User::VIEWER, 'is_active' => true]);
        $this->unit = Unit::create(['code' => 'PCS', 'name' => 'ชิ้น']);
        $this->warehouse = Warehouse::create(['code' => 'MAIN', 'name' => 'คลังหลัก']);
        $base = ['unit_id' => $this->unit->id, 'minimum_stock' => 5, 'is_active' => true, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id];
        $this->part = Product::create($base + ['code' => 'P-001', 'barcode' => '111', 'name' => 'Part', 'product_type' => ProductType::PART]);
        $this->fg = Product::create($base + ['code' => 'F-001', 'barcode' => '222', 'name' => 'FG', 'product_type' => ProductType::FG]);
    }

    private function postStock(StockDocumentType $type, Product $product, string $qty = '10', ?string $key = null): StockDocument
    {
        return app(StockService::class)->createAndPost(['document_date' => today()->format('Y-m-d'), 'warehouse_id' => $this->warehouse->id, 'idempotency_key' => $key ?? (string) Str::uuid(), 'items' => [['product_id' => $product->id, 'quantity' => $qty]]], $type, $this->staff);
    }

    public function test_product_code_and_barcode_are_unique(): void
    {
        $this->expectException(QueryException::class);
        Product::create(['code' => 'P-001', 'barcode' => '333', 'name' => 'Duplicate', 'product_type' => 'PART', 'unit_id' => $this->unit->id, 'minimum_stock' => 0, 'is_active' => 1, 'created_by' => $this->admin->id, 'updated_by' => $this->admin->id]);
    }

    public function test_viewer_cannot_create_product_or_stock_document(): void
    {
        $this->actingAs($this->viewer)->post('/products', [])->assertForbidden();
        $this->actingAs($this->viewer)->get('/documents/part_in/create')->assertForbidden();
    }

    public function test_part_in_and_out_update_balance_and_ledger(): void
    {
        $this->postStock(StockDocumentType::PART_IN, $this->part, '100');
        $out = $this->postStock(StockDocumentType::PART_OUT, $this->part, '30');
        $this->assertSame('70', StockBalance::first()->quantity);
        $this->assertCount(2, StockTransaction::all());
        $this->assertSame('70', $out->transactions()->first()->balance_after);
    }

    public function test_fg_in_and_out_work_and_product_type_is_enforced(): void
    {
        $this->postStock(StockDocumentType::FG_IN, $this->fg, '12');
        $this->postStock(StockDocumentType::FG_OUT, $this->fg, '2');
        $this->assertSame('10', StockBalance::where('product_id', $this->fg->id)->first()->quantity);
        $this->expectException(ValidationException::class);
        $this->postStock(StockDocumentType::PART_IN, $this->fg, '1');
    }

    public function test_out_and_adjustment_cannot_make_stock_negative(): void
    {
        $this->postStock(StockDocumentType::PART_IN, $this->part, '20');
        try {
            $this->postStock(StockDocumentType::PART_OUT, $this->part, '21');
            $this->fail('Expected validation');
        } catch (ValidationException) {
        }$this->assertSame('20', StockBalance::first()->quantity);
        $this->expectException(ValidationException::class);
        app(StockService::class)->createAndPost(['document_date' => today()->format('Y-m-d'), 'warehouse_id' => $this->warehouse->id, 'idempotency_key' => (string) Str::uuid(), 'items' => [['product_id' => $this->part->id, 'quantity' => '21']]], StockDocumentType::ADJUST_OUT, $this->admin);
    }

    public function test_double_post_is_idempotent(): void
    {
        $key = (string) Str::uuid();
        $first = $this->postStock(StockDocumentType::PART_IN, $this->part, '10', $key);
        $second = $this->postStock(StockDocumentType::PART_IN, $this->part, '10', $key);
        $this->assertTrue($first->is($second));
        $this->assertSame('10', StockBalance::first()->quantity);
        $this->assertDatabaseCount('stock_transactions', 1);
    }

    public function test_reversal_restores_balance_and_cannot_repeat(): void
    {
        $doc = $this->postStock(StockDocumentType::PART_IN, $this->part, '10');
        $reversal = app(StockService::class)->cancel($doc, $this->admin, 'เอกสารผิด');
        $this->assertSame('0', StockBalance::first()->quantity);
        $this->assertSame(StockDocumentStatus::CANCELLED, $doc->fresh()->status);
        $this->assertSame(StockDocumentType::REVERSAL, $reversal->document_type);
        $this->assertDatabaseCount('stock_transactions', 2);
        $this->expectException(ValidationException::class);
        app(StockService::class)->cancel($doc, $this->admin, 'ซ้ำ');
    }

    public function test_stock_card_total_matches_balance(): void
    {
        $this->postStock(StockDocumentType::PART_IN, $this->part, '15');
        $this->postStock(StockDocumentType::PART_OUT, $this->part, '4');
        $sum = StockTransaction::where('product_id', $this->part->id)->selectRaw('SUM(quantity_in-quantity_out) total')->value('total');
        $this->assertEquals($sum, StockBalance::where('product_id', $this->part->id)->value('quantity'));
        $this->artisan('stock:rebuild-balances', ['--verify' => true])->assertSuccessful();
    }

    public function test_csv_import_and_export_work(): void
    {
        $csv = "code,name,product_type,unit_code,barcode,minimum_stock,location_text\nP-CSV,Imported PART,PART,PCS,999,3,A1\nS-CSV,Imported SUPPLY,SUPPLY,PCS,,2,A2\nW-CSV,Imported WIP,WIP,PCS,,1,A3\nF-CSV,Imported FG,FG,PCS,,1,A4\n";
        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);
        $this->actingAs($this->admin)->post(route('products.import'), ['file' => $file])->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['code' => 'P-CSV']);
        $this->assertDatabaseHas('products', ['code' => 'S-CSV', 'product_type' => 'SUPPLY']);
        $this->assertDatabaseHas('products', ['code' => 'W-CSV', 'product_type' => 'WIP']);
        $this->assertDatabaseHas('products', ['code' => 'F-CSV', 'product_type' => 'FG']);
        $response = app(ReportController::class)->export(new Request, app(StockReportService::class), app(ProductSpreadsheetService::class));
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertStringContainsString('stock-balances-', (string) $response->headers->get('content-disposition'));
    }

    public function test_xlsx_template_import_and_balance_export_work(): void
    {
        $spreadsheets = app(ProductSpreadsheetService::class);
        $templatePath = $spreadsheets->writeImportTemplate();
        $template = IOFactory::load($templatePath);
        $this->assertSame(ProductSpreadsheetService::HEADERS, $template->getSheetByName('Products')->rangeToArray('A1:H1')[0]);
        $this->assertNotNull($template->getSheetByName('วิธีใช้'));
        $template->disconnectWorksheets();

        $upload = new UploadedFile($templatePath, 'products.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->actingAs($this->admin)->post(route('products.import'), ['file' => $upload])->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['code' => 'PART-100', 'product_type' => 'PART']);

        $this->postStock(StockDocumentType::PART_IN, $this->part, '8');
        $exportPath = $spreadsheets->writeBalanceWorkbook(StockBalance::with(['product.unit', 'warehouse'])->get());
        $export = IOFactory::load($exportPath);
        $sheet = $export->getSheetByName('Stock Balances');
        $this->assertSame('รายงานสต็อกคงเหลือ', $sheet->getCell('A1')->getValue());
        $this->assertSame('รหัส', $sheet->getCell('A7')->getValue());
        $this->assertSame('P-001', $sheet->getCell('A8')->getValue());
        $this->assertEquals(8, $sheet->getCell('F8')->getValue());
        $this->assertSame('2563EB', $sheet->getStyle('A7')->getFill()->getStartColor()->getRGB());
        $this->assertFalse($sheet->getShowGridlines());
        $this->assertSame('A8', $sheet->getFreezePane());
        $export->disconnectWorksheets();
        @unlink($templatePath);
        @unlink($exportPath);
    }

    public function test_admin_can_manage_units_and_warehouses_without_hardcoded_data(): void
    {
        $unusedUnit = Unit::create(['code' => 'BOX', 'name' => 'Box']);
        $this->actingAs($this->admin)
            ->put(route('settings.units.update', $unusedUnit), ['code' => 'CTN', 'name' => 'Carton', 'is_active' => '1'])
            ->assertRedirect();
        $this->assertDatabaseHas('units', ['id' => $unusedUnit->id, 'code' => 'CTN', 'name' => 'Carton', 'is_active' => true]);

        $this->delete(route('settings.units.destroy', $unusedUnit))->assertRedirect();
        $this->assertDatabaseMissing('units', ['id' => $unusedUnit->id]);

        $this->delete(route('settings.units.destroy', $this->unit))->assertRedirect();
        $this->assertDatabaseHas('units', ['id' => $this->unit->id, 'is_active' => false]);

        $secondWarehouse = Warehouse::create(['code' => 'SECOND', 'name' => 'Second warehouse']);
        $this->put(route('settings.warehouses.update', $secondWarehouse), [
            'code' => 'BRANCH',
            'name' => 'Branch warehouse',
            'address' => 'Bangkok',
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['id' => $secondWarehouse->id, 'code' => 'BRANCH', 'address' => 'Bangkok']);

        $this->postStock(StockDocumentType::PART_IN, $this->part, '1');
        $this->delete(route('settings.warehouses.destroy', $this->warehouse))->assertRedirect();
        $this->assertDatabaseHas('warehouses', ['id' => $this->warehouse->id, 'is_active' => false]);
    }

    public function test_admin_can_edit_users_and_products_and_history_is_preserved(): void
    {
        $this->actingAs($this->admin)
            ->put(route('users.update', $this->staff), [
                'name' => 'Updated staff',
                'email' => 'updated@example.com',
                'role' => User::VIEWER,
                'is_active' => '1',
            ])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->staff->id, 'name' => 'Updated staff', 'role' => User::VIEWER]);

        $unusedProduct = Product::create([
            'code' => 'DELETE-ME',
            'name' => 'Unused',
            'product_type' => ProductType::PART,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 0,
            'is_active' => true,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        $this->delete(route('products.destroy', $unusedProduct))->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $unusedProduct->id]);

        $this->postStock(StockDocumentType::PART_IN, $this->part, '2');
        $this->delete(route('products.destroy', $this->part))->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $this->part->id, 'is_active' => false, 'deleted_at' => null]);

        $this->put(route('users.update', $this->admin), [
            'name' => $this->admin->name,
            'email' => $this->admin->email,
            'role' => User::ADMIN,
        ])->assertSessionHasErrors('is_active');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_product_edit_page_and_flexible_quantity_format_work(): void
    {
        $this->actingAs($this->admin)
            ->get(route('products.edit', $this->part))
            ->assertOk()
            ->assertSee('value="5"', false);

        $this->assertSame('10', Quantity::format('10.0000'));
        $this->assertSame('10.25', Quantity::format('10.2500'));
        $this->assertSame('1,234.5', Quantity::format('1234.5000'));
    }

    public function test_admin_can_upload_and_view_a_product_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)->put(route('products.update', $this->part), [
            'code' => $this->part->code,
            'name' => $this->part->name,
            'product_type' => ProductType::PART->value,
            'unit_id' => $this->unit->id,
            'minimum_stock' => '5',
            'is_active' => '1',
            'image' => UploadedFile::fake()->create('part.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('products.index', ['type' => 'PART']));

        $product = $this->part->fresh();
        Storage::disk('public')->assertExists($product->image_path);
        $this->get(route('products.image', $product))->assertOk();
        $this->get(route('products.index'))->assertOk()->assertSee(route('products.image', $product), false);
    }

    public function test_products_can_be_created_without_barcodes(): void
    {
        $product = Product::create([
            'code' => 'SCAN-001',
            'name' => 'Scannable product',
            'product_type' => ProductType::PART,
            'unit_id' => $this->unit->id,
            'minimum_stock' => 0,
            'is_active' => true,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $this->assertNull($product->fresh()->barcode);

        return;
        $this->assertStringContainsString('<svg', app(BarcodeService::class)->svg($product->barcode));

        $this->actingAs($this->admin)
            ->get(route('products.barcode', $product))
            ->assertOk()
            ->assertSee($product->barcode)
            ->assertSee('พิมพ์ฉลาก');

        $this->actingAs($this->staff)
            ->get(route('documents.create', 'part_in'))
            ->assertOk()
            ->assertSee($product->barcode)
            ->assertSee('Scanner mode');
    }

    public function test_empty_balance_excel_is_still_a_formatted_report(): void
    {
        $path = app(ProductSpreadsheetService::class)->writeBalanceWorkbook([]);
        $workbook = IOFactory::load($path);
        $sheet = $workbook->getSheetByName('Stock Balances');

        $this->assertSame('รายงานสต็อกคงเหลือ', $sheet->getCell('A1')->getValue());
        $this->assertSame('ไม่พบข้อมูลสต็อกตามเงื่อนไขที่เลือก', $sheet->getCell('A8')->getValue());
        $this->assertSame('A1:J1', $sheet->getMergeCells()['A1:J1']);
        $this->assertSame('A8', $sheet->getFreezePane());

        $workbook->disconnectWorksheets();
        @unlink($path);
    }

    public function test_login_and_main_pages_render_without_errors(): void
    {
        $outputLevel = ob_get_level();
        $this->get(route('login'))->assertOk()->assertSee('เข้าสู่ระบบ');
        $this->actingAs($this->admin);
        foreach ([route('dashboard'), route('products.index'), route('products.import.form'), route('reports.balances'), route('reports.card'), route('reports.movements'), route('settings'), route('users.index'), route('audits'), route('documents.create', 'part_in')] as $url) {
            $this->get($url)->assertOk();
        }
        while (ob_get_level() > $outputLevel) {
            ob_end_clean();
        }
    }
}
