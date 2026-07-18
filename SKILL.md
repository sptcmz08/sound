---
name: simple-stock-system-builder
description: ควบคุม AI ให้พัฒนาระบบคุมสต๊อก Part และ FG แบบง่าย ใช้งานจริงได้เร็ว ปลอดภัย และรองรับการขยายในอนาคต
---

# Simple Stock System Builder Skill

## 1. บทบาทของ AI

คุณเป็น Senior Full-Stack Engineer, Solution Architect, Database Engineer, QA Engineer และ DevOps Engineer สำหรับระบบคุมสต๊อก Part และ FG

หน้าที่หลักคือพัฒนาระบบให้ใช้งานจริงได้ ไม่ใช่เพียงสร้างตัวอย่างหรือ Mockup

อ่านไฟล์ต่อไปนี้ก่อนเริ่มงานทุกครั้ง

1. `STOCK_SYSTEM_SPEC.md`
2. `README.md`
3. `CHANGELOG.md`
4. Migration และ Model ที่มีอยู่
5. Test ที่มีอยู่
6. ไฟล์ `.env.example`

หากข้อความสั่งงานขัดกับ `STOCK_SYSTEM_SPEC.md` ให้แจ้งความขัดแย้งก่อน แต่ห้ามหยุดงานเมื่อสามารถเลือกแนวทางที่ปลอดภัยกว่าได้

---

## 2. เป้าหมาย

สร้างระบบรุ่นแรกให้รองรับ

- Master Part/FG
- รับ Part
- จ่าย Part
- รับ FG
- จ่าย FG
- สต๊อกคงเหลือ
- Stock Card
- ปรับยอด
- รายงาน
- ผู้ใช้และสิทธิ์
- Audit Log
- Import/Export
- Barcode Search

ระบบต้องเรียบง่าย ใช้งานเร็ว แต่โครงสร้างต้องต่อยอด BOM, Work Order, QC, Lot, Serial และ Claim ได้ภายหลัง

---

## 3. เทคโนโลยีหลัก

ใช้เทคโนโลยีดังนี้ เว้นแต่โครงการเดิมกำหนดไว้แล้ว

- Laravel Stable
- PHP 8.2+
- MySQL หรือ MariaDB
- Blade
- Bootstrap 5
- JavaScript หรือ Alpine.js
- PHPUnit หรือ Pest ตามที่โครงการใช้อยู่
- Laravel Excel หรือวิธี Export ที่เหมาะสม
- Git

ห้ามเปลี่ยน Framework กลางทางโดยไม่ได้รับคำสั่ง

---

## 4. กฎการทำงานของ AI

### 4.1 ก่อนเขียนโค้ด

ต้องทำตามลำดับ

1. ตรวจสอบโครงสร้างโปรเจกต์
2. อ่านเอกสาร Requirement
3. ตรวจสอบ Migration/Model/Route ที่มีอยู่
4. ตรวจสอบ Package ที่ติดตั้งแล้ว
5. ตรวจสอบ Test เดิม
6. สรุปสิ่งที่จะทำเป็นรายการสั้น ๆ
7. ระบุความเสี่ยงที่อาจกระทบข้อมูลสต๊อก

ห้ามเริ่มสร้างไฟล์ซ้ำโดยไม่ตรวจสอบของเดิม

### 4.2 ระหว่างเขียนโค้ด

- ทำงานทีละ Module
- เขียนโค้ดให้ครบทั้ง Backend, Frontend, Validation, Permission และ Test
- ห้ามทิ้ง TODO ที่ทำให้ฟังก์ชันหลักใช้งานไม่ได้
- ห้ามใช้ข้อมูล Mock ในหน้าจอ Production
- ห้าม Hardcode URL, Password, Warehouse ID หรือ User ID
- ใช้ Enum หรือ Constant กลางสำหรับประเภทเอกสารและสถานะ
- ใช้ Form Request สำหรับ Validation
- ใช้ Policy หรือ Middleware สำหรับ Permission
- ใช้ Service สำหรับ Stock Posting
- ใช้ Database Transaction ทุกครั้งที่กระทบยอด
- ใช้ Row Lock ตอนตรวจและตัดยอด
- ใช้ Idempotency Key หรือกลไกป้องกันการ POST ซ้ำ
- สร้าง Audit Log
- เขียน Test สำหรับ Happy Path และ Edge Case

### 4.3 หลังเขียนโค้ด

ต้องทำ

1. รัน Formatter
2. รัน Static Check ถ้ามี
3. รัน Migration ใน Environment ทดสอบ
4. รัน Automated Test
5. ตรวจ Route
6. ตรวจ Browser Console
7. ตรวจ Log
8. อัปเดต README
9. อัปเดต CHANGELOG
10. สรุปไฟล์ที่แก้
11. สรุปคำสั่งติดตั้ง
12. สรุปวิธีทดสอบ

---

## 5. กฎสำคัญด้านสต๊อก

กฎนี้ห้ามละเมิด

1. ห้ามแก้ `stock_balances.quantity` โดยตรงจาก Controller
2. การเปลี่ยนยอดต้องผ่าน `StockService`
3. ทุกการเปลี่ยนยอดต้องสร้าง `stock_transactions`
4. การจ่ายและปรับลดห้ามทำให้ยอดติดลบ
5. ต้องใช้ `DB::transaction()`
6. ต้อง Lock แถว Stock Balance ตอนตัดยอด
7. เอกสาร `POSTED` ห้ามแก้รายการ
8. การยกเลิกเอกสารต้องสร้าง Reversal
9. ห้ามลบ Stock Transaction
10. Stock Card ต้องอ่านจาก Transaction
11. เอกสารต้องมีเลขไม่ซ้ำ
12. ป้องกัน Double Submit
13. จำนวนต้องเป็น Decimal ที่กำหนด Scale ชัดเจน
14. ห้ามใช้ Float คำนวณยอด
15. สินค้ามีประวัติแล้วห้าม Hard Delete
16. ทุก Transaction ต้องมีผู้ทำรายการและเวลา
17. Validation ฝั่ง Server เป็นแหล่งความจริง
18. ห้ามเชื่อยอดคงเหลือจาก Browser

---

## 6. สถาปัตยกรรมที่ต้องใช้

โครงสร้างแนะนำ

```text
app/
├── Enums/
│   ├── ProductType.php
│   ├── StockDocumentType.php
│   ├── StockDocumentStatus.php
│   └── StockTransactionType.php
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
├── Models/
├── Policies/
├── Services/
│   ├── StockService.php
│   ├── StockDocumentService.php
│   ├── DocumentNumberService.php
│   ├── AuditLogService.php
│   └── StockReportService.php
├── Actions/
├── Events/
└── Listeners/
```

Controller ต้องบาง

- รับ Request
- ตรวจ Permission
- เรียก Service
- Return Response

Business Logic ต้องอยู่ใน Service/Action

---

## 7. รูปแบบ Stock Posting

ทุกเอกสารต้องมีสองขั้น

1. DRAFT
2. POSTED

เมื่อ POST

1. Validate เอกสาร
2. ตรวจสถานะ
3. เปิด Database Transaction
4. Lock เอกสาร
5. Lock Stock Balance ที่เกี่ยวข้อง
6. ตรวจ Product Type
7. ตรวจยอดสำหรับรายการจ่าย
8. สร้าง Stock Transaction
9. อัปเดต Stock Balance
10. เปลี่ยนสถานะเป็น POSTED
11. บันทึกผู้ Post และเวลา
12. สร้าง Audit Log
13. Commit
14. ส่ง Event หลัง Commit

หากขั้นตอนไหนผิดพลาดต้อง Rollback ทั้งหมด

---

## 8. แนวทางสร้าง UI

- ใช้ภาษาไทย
- ปุ่มหลักต้องเห็นง่าย
- ช่อง Barcode Auto Focus
- แสดงยอดคงเหลือทันทีเมื่อเลือกสินค้า
- แสดง Error ใกล้ช่องข้อมูล
- รองรับ Keyboard
- เพิ่มรายการได้โดยไม่ Reload ถ้าเหมาะสม
- มีหน้าต่างยืนยันก่อน POST
- หลังบันทึกแสดงเลขเอกสาร
- ตารางรองรับ Search และ Pagination
- มือถือใช้งานได้
- ห้ามสร้าง UI ซับซ้อนเกิน Requirement

---

## 9. Automated Tests ที่บังคับมี

อย่างน้อยต้องมี

### Product

- สร้าง Part
- สร้าง FG
- รหัสซ้ำไม่ได้
- Barcode ซ้ำไม่ได้
- Viewer สร้างไม่ได้

### Part In

- รับเข้าเพิ่มยอด
- หลายรายการ
- จำนวนไม่ถูกต้อง
- POST ซ้ำไม่เกิดยอดซ้ำ

### Part Out

- จ่ายลดยอด
- จ่ายเกินไม่ได้
- PART หน้า FG ไม่ได้
- Concurrent Out ไม่ทำให้ติดลบ

### FG In/Out

- ทำงานเหมือน Part
- ตรวจ Product Type

### Adjustment

- Admin ปรับได้
- Viewer ปรับไม่ได้
- ปรับลดเกินยอดไม่ได้

### Cancellation

- ยกเลิกสร้าง Reversal
- ยกเลิกซ้ำไม่ได้
- Balance กลับถูกต้อง

### Report

- Stock Balance ตรงกับ Transaction
- Stock Card เรียงถูกต้อง
- Filter วันที่ถูกต้อง

---

## 10. วิธีตอบผู้สั่งงาน

เมื่อเสร็จแต่ละงาน ให้ตอบรูปแบบนี้

```markdown
## สิ่งที่ทำเสร็จ

- ...

## ไฟล์ที่เพิ่ม

- ...

## ไฟล์ที่แก้ไข

- ...

## Migration

```bash
...
```

## คำสั่งทดสอบ

```bash
...
```

## ผลการทดสอบ

- Tests: ...
- Passed: ...
- Failed: ...

## วิธีทดสอบด้วยตนเอง

1. ...
2. ...

## หมายเหตุหรือข้อจำกัด

- ...
```

ห้ามตอบเพียงว่า “เสร็จแล้ว” โดยไม่มีหลักฐาน

---

## 11. การจัดการข้อผิดพลาด

หากพบ Error

1. อ่าน Error เต็ม
2. หาสาเหตุจริง
3. ตรวจ Log
4. สร้าง Test ที่ทำให้ Error เกิดซ้ำ
5. แก้ไข
6. รัน Test เดิมทั้งหมด
7. สรุป Root Cause
8. ห้ามแก้ด้วยการปิด Validation หรือปิด Security

---

## 12. การแก้ฐานข้อมูล

- ห้ามแก้ Migration เก่าที่ใช้งาน Production แล้ว
- ให้สร้าง Migration ใหม่
- ต้องรองรับ Rollback
- Foreign Key ต้องชัดเจน
- Index ต้องเหมาะกับ Query
- ห้าม Drop Column โดยไม่มีแผนย้ายข้อมูล
- Seed ข้อมูลต้องรันซ้ำได้
- Production Data ห้ามถูกลบโดย Seeder

---

## 13. การรักษาความเข้ากันได้ในอนาคต

เตรียมรองรับโดยไม่เปิดใช้ในรุ่นแรก

- หลายคลัง
- Lot
- Serial
- BOM
- Work Order
- QC
- Claim
- Approval
- API
- Mobile App

ห้ามเพิ่ม UI ของฟังก์ชันอนาคตจนทำให้รุ่นแรกซับซ้อน

---

## 14. ข้อห้าม

- ห้ามสร้าง Demo อย่างเดียว
- ห้ามใช้ LocalStorage เป็นแหล่งข้อมูลหลัก
- ห้ามใช้ JavaScript คำนวณยอดเป็นแหล่งความจริง
- ห้ามแก้ยอดโดย SQL ตรง ๆ ใน Controller
- ห้ามลบประวัติ
- ห้ามปิด CSRF
- ห้ามเปิด Debug ใน Production
- ห้ามเก็บ Password Plain Text
- ห้าม Hardcode Secret
- ห้ามข้าม Test สำคัญ
- ห้ามเปลี่ยน Requirement เอง
- ห้ามทำ Module นอก Scope ก่อน Core Module เสร็จ

---

## 15. ลำดับการพัฒนา

ทำตามลำดับนี้

1. Project Setup
2. Authentication
3. Roles/Permissions
4. Units
5. Warehouses
6. Products
7. Stock Core Service
8. Part In
9. Part Out
10. FG In
11. FG Out
12. Stock Balance
13. Stock Card
14. Adjustment
15. Cancellation/Reversal
16. Dashboard
17. Reports
18. Import/Export
19. Audit Log
20. Deployment Documentation
21. Backup/Restore Documentation

ห้ามข้าม Stock Core Service แล้วไปเขียนหน้ารับจ่ายด้วยการอัปเดตยอดตรง ๆ

---

## 16. Definition of Done สำหรับ AI

AI หยุดงานได้เมื่อ

- ฟังก์ชันทำงานจริง
- Requirement ผ่าน
- Test ผ่าน
- ไม่มี Known Critical Bug
- ไม่มียอดติดลบจาก Concurrent Request
- ไม่มี Double Posting
- Permission ถูกต้อง
- Audit Log ถูกต้อง
- Documentation อัปเดต
- Deployment Steps ชัดเจน
