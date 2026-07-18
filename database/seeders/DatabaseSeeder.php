<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_USER_PASSWORD', 'ChangeMe123!');
        $admin = User::updateOrCreate(['email' => 'admin@example.com'], ['name' => 'ผู้ดูแลระบบ', 'password' => $password, 'role' => User::ADMIN, 'is_active' => true, 'must_change_password' => true]);
        User::updateOrCreate(['email' => 'stock@example.com'], ['name' => 'เจ้าหน้าที่สต๊อก', 'password' => $password, 'role' => User::STOCK_STAFF, 'is_active' => true, 'must_change_password' => true]);
        User::updateOrCreate(['email' => 'viewer@example.com'], ['name' => 'ผู้ดูรายงาน', 'password' => $password, 'role' => User::VIEWER, 'is_active' => true, 'must_change_password' => true]);
        foreach ([['PCS', 'ชิ้น'], ['BOX', 'กล่อง'], ['SET', 'ชุด']] as [$code,$name]) {
            Unit::updateOrCreate(compact('code'), compact('name') + ['is_active' => true]);
        }Warehouse::updateOrCreate(['code' => 'MAIN'], ['name' => 'คลังหลัก', 'is_active' => true]);
        foreach ([['PART-001', 'น็อต A', 'PART', 'PCS'], ['PART-002', 'สายไฟ B', 'PART', 'PCS'], ['FG-001', 'สินค้าสำเร็จรูป A', 'FG', 'PCS'], ['FG-002', 'สินค้าสำเร็จรูป B', 'FG', 'SET']] as [$code,$name,$type,$unit]) {
            Product::updateOrCreate(compact('code'), ['name' => $name, 'product_type' => $type, 'unit_id' => Unit::where('code', $unit)->value('id'), 'minimum_stock' => 0, 'is_active' => true, 'created_by' => $admin->id, 'updated_by' => $admin->id]);
        }
    }
}
