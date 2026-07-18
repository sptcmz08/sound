# Changelog

## 1.2.0 - 2026-07-18

- เพิ่มระบบ Barcode Code 128: สร้างรหัสอัตโนมัติ พิมพ์ฉลาก และรองรับเครื่องสแกน USB
- ปรับการยิง Barcode ซ้ำให้เพิ่มจำนวนสินค้าอัตโนมัติ พร้อมเสียงและข้อความยืนยัน
- เปลี่ยนฟอนต์หลักเป็น IBM Plex Sans Thai ผ่าน Google Fonts API และขยายตัวอักษรทั้งระบบ
- ปรับ Dashboard เป็น KPI 3 คอลัมน์ เพิ่มขนาดตัวเลข ปุ่มรับ-จ่าย และการ์ดแนะนำขั้นตอนใช้งาน
- เพิ่ม contrast, spacing และปรับชื่อเมนูภาษาไทยให้อ่านและเข้าใจง่ายขึ้น
- เพิ่มหน้าแก้ไขและลบ/ปิดใช้งาน Unit, Warehouse, Product และ User ให้จัดการจาก UI ได้ครบ
- เพิ่มกฎรักษาประวัติ โดยข้อมูลหลักที่ถูกอ้างอิงจะปิดใช้งานแทนการลบ
- เพิ่มตัวช่วยเริ่มต้นบน Dashboard อธิบายลำดับ ตั้งค่า → เพิ่มสินค้า → รับเข้า → จ่ายออก
- ปรับหน้ารายการสินค้าให้ปุ่มเพิ่มสินค้า แก้ไข ลบ และ Import เห็นชัดเจน
- เพิ่ม validation ป้องกันการปิดคลังสุดท้าย ระงับบัญชีตัวเอง และลบ Administrator คนสุดท้าย
- เพิ่ม automated tests สำหรับ CRUD และการรักษาประวัติข้อมูล

## 1.1.0 - 2026-07-18

- เปลี่ยน UI ทั้งระบบจาก Bootstrap เป็น Tailwind CSS 4 ผ่าน Vite production build
- เพิ่ม responsive admin sidebar, mobile drawer, sticky topbar และ user panel
- ออกแบบ Dashboard, KPI cards, quick actions, tables, filters, forms และ status badges ใหม่
- ปรับหน้า Login, Products, Import, Stock Documents, Reports, Users, Settings และ Audit Log ใหม่ทั้งหมด
- เพิ่ม design tokens และ reusable component classes กลางใน `resources/css/app.css`

## 1.0.1 - 2026-07-18

- เพิ่ม Import สินค้าจาก Excel `.xlsx`/`.xls` และ CSV พร้อม transaction rollback และ validation รายแถว
- เพิ่มแบบฟอร์ม Excel พร้อม dropdown ประเภทสินค้าและแผ่นคู่มือ
- เพิ่ม Export Stock Balance เป็น `.xlsx` พร้อม filter, freeze header และรูปแบบตัวเลข
- ป้องกัน spreadsheet formula injection ในไฟล์ CSV/XLSX ที่ export
- เพิ่ม automated round-trip test สำหรับ template, XLSX import และ XLSX export

## 1.0.0 - 2026-07-18

- สร้าง Laravel 12 project พร้อม login และ rate limit
- เพิ่ม Roles: Administrator, Stock Staff และ Viewer
- เพิ่ม Units, Warehouses, Products พร้อมรูปสินค้า soft delete และ CSV import
- เพิ่ม Stock Core ด้วย transaction, row lock, decimal, ledger และ idempotency
- เพิ่ม Part In/Out, FG In/Out, Adjustment และ Cancellation/Reversal
- เพิ่ม Dashboard, Stock Balance, Stock Card, movement report และ CSV export
- เพิ่ม Audit Log, seeder, balance verification/rebuild command และ automated tests
- เพิ่ม Responsive Bootstrap 5 UI ภาษาไทยและคู่มือ deployment/backup/restore
