# Simple Part & FG Stock Control System
## เอกสารขอบเขตระบบสำหรับพัฒนาใช้งานจริงระยะเริ่มต้น

เวอร์ชันเอกสาร: 1.0  
เป้าหมาย: สร้างระบบคุมสต๊อก Part และ FG ที่ใช้งานง่าย เปิดใช้งานได้เร็ว และขยายระบบต่อในอนาคตได้โดยไม่ต้องรื้อฐานข้อมูลใหม่

---

## 1. เป้าหมายของระบบ

ระบบรุ่นแรกต้องรองรับกระบวนการหลักดังนี้

1. สร้างข้อมูลสินค้า Part และ FG
2. รับ Part เข้าสต๊อก
3. จ่าย Part ออกจากสต๊อก
4. รับ FG เข้าสต๊อก
5. จ่าย FG ออกจากสต๊อก
6. ดูยอดคงเหลือปัจจุบัน
7. ดูประวัติการเคลื่อนไหว Stock Card
8. ปรับยอดสต๊อกโดยผู้มีสิทธิ์
9. ออกรายงานและ Export Excel/CSV
10. จัดการผู้ใช้และสิทธิ์พื้นฐาน
11. เก็บ Audit Log ทุกการเปลี่ยนแปลงสำคัญ

ระบบต้องใช้งานง่าย ขั้นตอนน้อย เหมาะสำหรับพนักงานสต๊อก และรองรับการใช้งานผ่านคอมพิวเตอร์ แท็บเล็ต และมือถือ

---

## 2. ขอบเขตที่ต้องทำในรุ่นแรก

### 2.1 Dashboard

แสดงข้อมูลต่อไปนี้

- จำนวนรายการ Part ทั้งหมด
- จำนวนรายการ FG ทั้งหมด
- จำนวนรับเข้าวันนี้
- จำนวนจ่ายออกวันนี้
- จำนวนสินค้าใกล้หมด
- จำนวนสินค้าหมดสต๊อก
- รายการเคลื่อนไหวล่าสุด
- ปุ่มลัด:
  - รับ Part
  - จ่าย Part
  - รับ FG
  - จ่าย FG
  - ดูสต๊อกคงเหลือ

### 2.2 ข้อมูลสินค้า

ข้อมูลสินค้าอย่างน้อยต้องมี

- รหัสสินค้า
- Barcode
- ชื่อสินค้า
- ประเภทสินค้า: PART หรือ FG
- หน่วยนับ
- จำนวนขั้นต่ำ
- ตำแหน่งจัดเก็บ
- รูปสินค้า
- หมายเหตุ
- สถานะใช้งาน

เงื่อนไข

- รหัสสินค้าห้ามซ้ำ
- Barcode ถ้ามีต้องไม่ซ้ำ
- สินค้าที่มีประวัติแล้วห้ามลบจริง ให้เปลี่ยนเป็นไม่ใช้งาน
- รองรับ Import จาก Excel/CSV
- รองรับ Export รายการสินค้า

### 2.3 รับ Part เข้าสต๊อก

ข้อมูลหัวเอกสาร

- เลขที่เอกสารอัตโนมัติ
- วันที่รับเข้า
- Supplier หรือแหล่งที่มา
- เลขที่เอกสารอ้างอิง
- หมายเหตุ
- ผู้ทำรายการ

ข้อมูลรายการ

- รหัส Part
- ชื่อ Part
- จำนวนรับ
- หน่วย
- ตำแหน่งจัดเก็บ
- หมายเหตุรายการ

เงื่อนไข

- เลือกได้เฉพาะสินค้าประเภท PART
- จำนวนรับต้องมากกว่า 0
- รองรับหลายรายการในเอกสารเดียว
- เมื่อยืนยันเอกสาร ยอดคงเหลือต้องเพิ่มทันที
- เอกสารที่ยืนยันแล้วห้ามแก้ไขยอดโดยตรง
- หากยกเลิก ต้องสร้างรายการย้อนกลับใน Stock Transaction

### 2.4 จ่าย Part ออกจากสต๊อก

ข้อมูลหัวเอกสาร

- เลขที่เอกสารอัตโนมัติ
- วันที่จ่าย
- แผนกหรือผู้รับ
- วัตถุประสงค์
- เลขที่เอกสารอ้างอิง
- หมายเหตุ
- ผู้ทำรายการ

ข้อมูลรายการ

- รหัส Part
- ชื่อ Part
- ยอดคงเหลือปัจจุบัน
- จำนวนจ่าย
- หน่วย
- หมายเหตุรายการ

เงื่อนไข

- เลือกได้เฉพาะสินค้าประเภท PART
- ห้ามจ่ายเกินยอดคงเหลือ
- จำนวนจ่ายต้องมากกว่า 0
- รองรับหลายรายการในเอกสารเดียว
- แสดงยอดก่อนและหลังทำรายการ
- การตรวจสอบยอดและการตัดยอดต้องอยู่ใน Database Transaction
- ป้องกันการจ่ายซ้ำจากการกดบันทึกหลายครั้ง

### 2.5 รับ FG เข้าสต๊อก

ใช้หน้าจอและหลักการเหมือนรับ Part แต่เลือกได้เฉพาะ FG

ข้อมูลแหล่งที่มา เช่น

- ผลิตเสร็จ
- รับคืน
- รับโอน
- อื่น ๆ

รุ่นแรกยังไม่ต้องตัด Part ตาม BOM อัตโนมัติ

### 2.6 จ่าย FG ออกจากสต๊อก

ข้อมูลเพิ่มเติม

- ลูกค้าหรือผู้รับ
- เลขที่ใบส่งของ
- จุดประสงค์การจ่าย

เงื่อนไข

- เลือกได้เฉพาะ FG
- ห้ามจ่ายเกินยอดคงเหลือ
- รองรับหลายรายการ
- เก็บประวัติผู้ทำรายการ
- ป้องกันการบันทึกซ้ำ

### 2.7 สต๊อกคงเหลือ

แสดงข้อมูล

- รหัสสินค้า
- Barcode
- ชื่อสินค้า
- ประเภท
- รับเข้ารวม
- จ่ายออกรวม
- ปรับเพิ่ม
- ปรับลด
- ยอดคงเหลือ
- หน่วย
- จำนวนขั้นต่ำ
- ตำแหน่ง
- สถานะสต๊อก

สถานะ

- ปกติ
- ใกล้หมด
- หมดสต๊อก
- ไม่ใช้งาน

ตัวกรอง

- รหัส
- ชื่อ
- Barcode
- PART/FG
- สถานะสต๊อก
- ตำแหน่งจัดเก็บ

### 2.8 Stock Card

แสดงประวัติสินค้าตามลำดับเวลา

- วันเวลา
- เลขที่เอกสาร
- ประเภทรายการ
- รับเข้า
- จ่ายออก
- ยอดคงเหลือหลังรายการ
- ผู้ทำรายการ
- หมายเหตุ
- ลิงก์ไปยังเอกสารต้นทาง

ประวัติ Stock Card ห้ามแก้ไขหรือลบโดยตรง

### 2.9 ปรับยอดสต๊อก

รองรับ

- ปรับเพิ่ม
- ปรับลด

ข้อมูล

- วันที่
- สินค้า
- ยอดก่อนปรับ
- จำนวนที่ปรับ
- ยอดหลังปรับ
- เหตุผล
- หมายเหตุ
- ผู้ทำรายการ
- ผู้อนุมัติ ถ้ามี

เงื่อนไข

- จำกัดสิทธิ์เฉพาะ Admin หรือ Stock Manager
- ห้ามแก้ยอดคงเหลือโดยตรงในตารางสินค้า
- การปรับทุกครั้งต้องสร้าง Stock Transaction
- ห้ามปรับลดจนยอดติดลบ

### 2.10 รายงาน

รายงานรุ่นแรก

1. สต๊อกคงเหลือ
2. รับเข้าตามช่วงวันที่
3. จ่ายออกตามช่วงวันที่
4. Stock Card รายสินค้า
5. สินค้าใกล้หมด
6. สินค้าหมดสต๊อก
7. ประวัติการปรับยอด
8. ประวัติการทำรายการตามผู้ใช้

รองรับ

- ค้นหา
- กรองวันที่
- พิมพ์
- Export CSV
- Export Excel
- PDF เป็นฟังก์ชันเสริมได้

### 2.11 ผู้ใช้และสิทธิ์

บทบาทเริ่มต้น

#### Administrator

- ใช้งานได้ทุกเมนู
- จัดการสินค้า
- จัดการผู้ใช้
- รับและจ่าย
- ปรับยอด
- ยกเลิกเอกสาร
- ดูรายงาน
- ดู Audit Log

#### Stock Staff

- รับ Part/FG
- จ่าย Part/FG
- ดูสต๊อก
- ดู Stock Card
- ดูรายงานที่ได้รับอนุญาต
- ห้ามจัดการผู้ใช้
- ห้ามปรับยอด เว้นแต่ได้รับสิทธิ์

#### Viewer

- ดู Dashboard
- ดูสต๊อก
- ดู Stock Card
- ดูรายงาน
- ห้ามสร้างหรือแก้ไขข้อมูล

---

## 3. เมนูของระบบ

1. Dashboard
2. สินค้า
3. รับ Part
4. จ่าย Part
5. รับ FG
6. จ่าย FG
7. สต๊อกคงเหลือ
8. Stock Card
9. ปรับยอด
10. รายงาน
11. ผู้ใช้งาน
12. Audit Log
13. ตั้งค่า

---

## 4. เทคโนโลยีแนะนำ

ระบบต้องติดตั้งบน Hosting หรือ VPS ที่รองรับ PHP และ MySQL ได้ง่าย

### Backend

- Laravel รุ่น Stable
- PHP 8.2 ขึ้นไป
- MySQL 8 หรือ MariaDB รุ่นที่รองรับ
- Laravel Authentication
- Role/Permission
- Database Transaction
- Queue สามารถเพิ่มภายหลัง

### Frontend

- Blade Template
- Bootstrap 5
- JavaScript หรือ Alpine.js
- AJAX สำหรับค้นหาสินค้าและเพิ่มรายการ
- Responsive Design
- ไม่ใช้ SPA ในรุ่นแรก เพื่อลดความซับซ้อน

### Deployment

- รองรับ Plesk
- รองรับ DirectAdmin
- รองรับ Ubuntu VPS
- ใช้ `.env`
- มี migration และ seeder
- มีคำสั่งติดตั้งที่ชัดเจน
- มี backup database script

---

## 5. โครงสร้างฐานข้อมูล

### 5.1 users

ใช้ตารางผู้ใช้ของ Laravel และเพิ่มข้อมูลที่จำเป็น

- id
- name
- email
- password
- role
- is_active
- last_login_at
- created_at
- updated_at

### 5.2 products

- id
- code
- barcode nullable
- name
- product_type enum: PART, FG
- unit_id
- minimum_stock decimal
- location_text nullable
- image_path nullable
- note nullable
- is_active boolean
- created_by
- updated_by
- created_at
- updated_at
- deleted_at nullable

Index

- unique(code)
- unique(barcode) แบบ nullable
- index(product_type)
- index(is_active)

### 5.3 units

- id
- code
- name
- is_active
- created_at
- updated_at

### 5.4 warehouses

รุ่นแรกสร้างคลังหลักอย่างน้อย 1 แห่ง แต่เตรียมรองรับหลายคลัง

- id
- code
- name
- address nullable
- is_active
- created_at
- updated_at

### 5.5 stock_documents

ตารางหัวเอกสาร

- id
- document_no
- document_type
  - PART_IN
  - PART_OUT
  - FG_IN
  - FG_OUT
  - ADJUST_IN
  - ADJUST_OUT
  - REVERSAL
- document_date
- warehouse_id
- reference_no nullable
- contact_name nullable
- department_name nullable
- purpose nullable
- note nullable
- status: DRAFT, POSTED, CANCELLED
- idempotency_key nullable
- created_by
- posted_by nullable
- posted_at nullable
- cancelled_by nullable
- cancelled_at nullable
- created_at
- updated_at

Index

- unique(document_no)
- unique(idempotency_key) แบบ nullable
- index(document_type, document_date)
- index(status)

### 5.6 stock_document_items

- id
- stock_document_id
- product_id
- quantity decimal
- unit_id
- note nullable
- created_at
- updated_at

เงื่อนไข

- quantity > 0
- ห้ามมีสินค้าซ้ำในเอกสารเดียว หรือรวมจำนวนให้อัตโนมัติ

### 5.7 stock_transactions

เป็นตารางสำคัญที่สุดและเป็นแหล่งประวัติที่แก้ไขไม่ได้

- id
- transaction_uuid
- stock_document_id
- stock_document_item_id
- product_id
- warehouse_id
- transaction_type
- quantity_in decimal default 0
- quantity_out decimal default 0
- balance_after decimal
- occurred_at
- created_by
- note nullable
- created_at

Index

- unique(transaction_uuid)
- index(product_id, warehouse_id, occurred_at)
- index(stock_document_id)
- index(transaction_type)

### 5.8 stock_balances

เก็บยอดปัจจุบันเพื่อให้ค้นหาเร็ว

- id
- product_id
- warehouse_id
- quantity decimal
- updated_at

Index

- unique(product_id, warehouse_id)

หลักการ

- ยอดใน stock_balances ต้องเปลี่ยนผ่าน StockService เท่านั้น
- ทุกครั้งต้องบันทึก stock_transactions พร้อมกันใน Database Transaction
- ต้องมีคำสั่ง Rebuild Balance จาก stock_transactions สำหรับตรวจสอบและซ่อมยอด

### 5.9 audit_logs

- id
- user_id nullable
- action
- entity_type
- entity_id nullable
- old_values json nullable
- new_values json nullable
- ip_address nullable
- user_agent nullable
- created_at

---

## 6. กฎทางธุรกิจที่ห้ามละเมิด

1. ห้ามแก้ยอด stock_balances โดยตรงจาก Controller
2. การรับ จ่าย ปรับ และยกเลิก ต้องผ่าน StockService
3. ห้ามจ่ายหรือปรับลดจนยอดติดลบ
4. การตัดยอดต้องใช้ Database Transaction และ Row Lock
5. เอกสาร POSTED ห้ามแก้รายการ
6. การยกเลิกเอกสาร POSTED ต้องสร้างรายการย้อนกลับ
7. ทุกเอกสารต้องมีเลขที่ไม่ซ้ำ
8. ทุกคำสั่ง POST ต้องป้องกันการส่งซ้ำ
9. ทุกการเปลี่ยนแปลงสำคัญต้องมี Audit Log
10. สินค้าที่มีประวัติแล้วห้าม Hard Delete
11. Stock Card ต้องมาจาก stock_transactions
12. ยอดคงเหลือต้องตรวจสอบเทียบกับผลรวม Transaction ได้เสมอ
13. ผู้ใช้ต้องเห็นเฉพาะเมนูตามสิทธิ์
14. Input ทุกจุดต้อง Validate ฝั่ง Server
15. ห้ามเชื่อถือค่าประเภทสินค้าและยอดคงเหลือจาก Browser

---

## 7. การสร้างเลขที่เอกสาร

รูปแบบแนะนำ

- รับ Part: `PIN-YYYYMM-000001`
- จ่าย Part: `POUT-YYYYMM-000001`
- รับ FG: `FGIN-YYYYMM-000001`
- จ่าย FG: `FGOUT-YYYYMM-000001`
- ปรับเพิ่ม: `ADJIN-YYYYMM-000001`
- ปรับลด: `ADJOUT-YYYYMM-000001`
- ย้อนรายการ: `REV-YYYYMM-000001`

การสร้างเลขต้องป้องกันเลขซ้ำเมื่อมีผู้ใช้ทำรายการพร้อมกัน

---

## 8. UX/UI

แนวทางหน้าจอ

- เมนูภาษาไทย
- ปุ่มใหญ่ อ่านง่าย
- สีสถานะแยกชัดเจน
- ตารางค้นหาได้
- ฟอร์มรับ/จ่ายเพิ่มหลายรายการได้
- ยิง Barcode แล้วเลือกสินค้าได้ทันที
- แสดงยอดคงเหลือก่อนกรอกจำนวนจ่าย
- แจ้งเตือนก่อนยืนยันเอกสาร
- หลังบันทึกสำเร็จแสดงเลขที่เอกสาร
- รองรับหน้าจอมือถือ
- ไม่ใช้หน้าจอซับซ้อนเกินความจำเป็น

---

## 9. Security

- CSRF Protection
- Authentication
- Role/Permission Middleware
- Password Hash
- Rate Limit หน้า Login
- Validate File Upload
- จำกัดชนิดและขนาดรูป
- SQL Injection ป้องกันผ่าน ORM/Prepared Statement
- XSS Escape
- Session Secure Cookie ใน Production
- Audit Login และการทำรายการสำคัญ
- Backup Database
- ห้ามแสดง Error Stack ใน Production

---

## 10. สิ่งที่ยังไม่ทำในรุ่นแรก

- BOM
- Work Order
- ตัด Part อัตโนมัติจากการผลิต
- QC Workflow
- Lot
- Serial Number
- Customer Claim
- Supplier Claim
- Purchase Order
- Sales Order
- หลายขั้นอนุมัติ
- Mobile App
- Offline Mode
- ระบบบัญชี
- ต้นทุนเฉลี่ย
- MRP
- Forecast

แต่โค้ดและฐานข้อมูลต้องไม่ปิดทางต่อยอดฟังก์ชันเหล่านี้

---

## 11. แนวทางรองรับการพัฒนาในอนาคต

ห้ามเขียนเงื่อนไข PART/FG กระจายทั่วระบบโดยไม่มี Service หรือ Enum กลาง

ควรเตรียม

- Product Type Enum
- Document Type Enum
- Document Status Enum
- Stock Transaction Type Enum
- StockService
- DocumentNumberService
- AuditLogService
- Export Service
- Repository หรือ Query Service สำหรับรายงาน
- API Layer สามารถเพิ่มภายหลัง
- warehouse_id ในทุก Transaction
- UUID สำหรับ Transaction
- Event หลัง Post เอกสาร
- Migration แบบเพิ่ม ไม่แก้ไฟล์ Migration เก่าหลัง Production

---

## 12. Acceptance Criteria

ระบบถือว่าพร้อมใช้งานเมื่อผ่านเงื่อนไขต่อไปนี้

1. สร้าง Part และ FG ได้
2. Import สินค้าได้
3. รับ Part หลายรายการแล้วสต๊อกเพิ่มถูกต้อง
4. จ่าย Part หลายรายการแล้วสต๊อกลดถูกต้อง
5. รับและจ่าย FG ได้
6. ไม่สามารถจ่ายเกินยอดได้
7. ผู้ใช้สองคนจ่ายสินค้าพร้อมกันแล้วไม่ทำให้ยอดติดลบ
8. การกดบันทึกซ้ำไม่สร้างเอกสารซ้ำ
9. Stock Card แสดงยอดหลังรายการถูกต้อง
10. ยกเลิกเอกสารแล้วสร้างรายการย้อนกลับ
11. ปรับยอดมีเหตุผลและ Audit Log
12. Viewer ไม่สามารถแก้ไขข้อมูล
13. Export รายงานได้
14. Rebuild Balance แล้วได้ยอดเท่ากับ Transaction
15. มี Automated Test สำหรับกฎสต๊อกสำคัญ
16. ติดตั้งบน Plesk หรือ Ubuntu ได้ตามคู่มือ
17. ไม่มี Error ใน Browser Console
18. ไม่มี Debug Mode ใน Production

---

## 13. Test Cases สำคัญ

### รับเข้า

- รับ 100 ยอดเดิม 0 ต้องเหลือ 100
- รับหลายสินค้าในเอกสารเดียว
- กดบันทึกซ้ำต้องได้เอกสารเดียว
- จำนวน 0 หรือติดลบต้องบันทึกไม่ได้

### จ่ายออก

- ยอด 100 จ่าย 30 ต้องเหลือ 70
- ยอด 20 จ่าย 21 ต้องไม่ได้
- ผู้ใช้สองคนจ่ายพร้อมกันรวมเกินยอด ต้องมีเพียงรายการที่ยอดพอเท่านั้นที่สำเร็จ
- จ่าย PART ด้วยหน้า FG ต้องไม่ได้
- จ่าย FG ด้วยหน้า PART ต้องไม่ได้

### ยกเลิก

- รับ 100 แล้วยกเลิก ยอดต้องกลับค่าเดิม
- จ่าย 20 แล้วยกเลิก ยอดต้องคืน 20
- ยกเลิกซ้ำต้องไม่ได้

### สิทธิ์

- Viewer เปิดหน้า POST ต้องถูกปฏิเสธ
- Stock Staff จัดการผู้ใช้ไม่ได้
- ผู้ไม่มีสิทธิ์ปรับยอดต้องทำรายการไม่ได้

---

## 14. ข้อมูลตัวอย่างสำหรับ Seeder

ผู้ใช้

- admin@example.com / role Administrator
- stock@example.com / role Stock Staff
- viewer@example.com / role Viewer

หน่วย

- PCS / ชิ้น
- BOX / กล่อง
- SET / ชุด

สินค้า

- PART-001 / น็อต A / PART / PCS
- PART-002 / สายไฟ B / PART / PCS
- FG-001 / สินค้าสำเร็จรูป A / FG / PCS
- FG-002 / สินค้าสำเร็จรูป B / FG / SET

คลัง

- MAIN / คลังหลัก

ห้ามใช้รหัสผ่านตัวอย่างเดิมใน Production และต้องบังคับเปลี่ยนรหัสผ่าน

---

## 15. Definition of Done

งานหนึ่งรายการถือว่าเสร็จเมื่อ

- โค้ดทำงานจริง
- มี Validation
- มี Permission
- มี Automated Test
- ไม่มี Error Log
- UI ใช้งานบนมือถือได้
- Migration และ Seeder รันได้
- ไม่ทำให้ Test เดิมพัง
- อัปเดต README
- อัปเดต CHANGELOG
- ระบุไฟล์ที่เพิ่มหรือแก้ไข
- มีขั้นตอนทดสอบด้วยตนเอง
