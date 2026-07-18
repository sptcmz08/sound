# MASTER PROMPT — สั่ง AI พัฒนาระบบคุมสต๊อก Part และ FG

คุณกำลังทำงานในโปรเจกต์ระบบคุมสต๊อก Part และ FG สำหรับใช้งานจริง

ก่อนเริ่มงาน ให้เปิดและอ่านไฟล์ต่อไปนี้ทั้งหมด

1. `SKILL.md`
2. `STOCK_SYSTEM_SPEC.md`
3. `README.md`
4. `CHANGELOG.md`
5. `.env.example`
6. Routes
7. Migrations
8. Models
9. Controllers
10. Services
11. Tests

ให้ปฏิบัติตาม `SKILL.md` และ `STOCK_SYSTEM_SPEC.md` เป็นกฎหลักของโปรเจกต์

## เป้าหมาย

สร้างระบบคุมสต๊อกแบบง่าย ใช้งานได้เร็ว แต่เป็น Production-Ready และรองรับการพัฒนาต่อในอนาคต

ฟังก์ชันรุ่นแรก

- Master สินค้า Part และ FG
- รับ Part
- จ่าย Part
- รับ FG
- จ่าย FG
- สต๊อกคงเหลือ
- Stock Card
- ปรับยอด
- ยกเลิกเอกสารด้วย Reversal
- Dashboard
- รายงาน
- Import/Export
- ผู้ใช้และสิทธิ์
- Audit Log
- Barcode Search

## เทคโนโลยี

- Laravel Stable
- PHP 8.2+
- MySQL หรือ MariaDB
- Blade
- Bootstrap 5
- JavaScript หรือ Alpine.js
- Automated Tests
- รองรับติดตั้งบน Plesk, DirectAdmin และ Ubuntu VPS

## วิธีทำงาน

1. ตรวจสอบโค้ดเดิมก่อนสร้างไฟล์
2. สรุปสถานะปัจจุบันของโปรเจกต์
3. สร้างแผนงานตามลำดับใน `SKILL.md`
4. ทำงานต่อเนื่องโดยไม่หยุดแค่โครง
5. ทุก Module ต้องมี:
   - Migration
   - Model
   - Enum
   - Form Request
   - Controller
   - Service
   - Policy/Permission
   - Route
   - Blade UI
   - Automated Test
   - Documentation
6. รัน Test หลังจบแต่ละ Module
7. แก้ Error ให้หมดก่อนทำ Module ต่อไป
8. อัปเดต `README.md` และ `CHANGELOG.md`
9. ห้ามใช้ Mock Data ในหน้าจอจริง
10. ห้ามทิ้ง TODO ที่ทำให้ระบบใช้งานไม่ได้

## กฎสต๊อกบังคับ

- ห้ามแก้ Stock Balance จาก Controller โดยตรง
- ใช้ StockService เท่านั้น
- ใช้ Database Transaction
- ใช้ Row Lock ตอนตัดยอด
- ห้ามยอดติดลบ
- ป้องกัน Double Submit
- POSTED Document ห้ามแก้
- การยกเลิกต้องสร้าง Reversal
- ทุกการเปลี่ยนยอดต้องมี Stock Transaction
- Stock Card ต้องมาจาก Stock Transaction
- ทุกการเปลี่ยนแปลงสำคัญต้องมี Audit Log
- ห้าม Hard Delete สินค้าที่มีประวัติ

## UX/UI

- ภาษาไทย
- Responsive
- ใช้งานง่าย
- ปุ่มรับและจ่ายเด่น
- Barcode Auto Focus
- เพิ่มหลายรายการในเอกสารเดียว
- แสดงยอดคงเหลือก่อนจ่าย
- มี Confirm ก่อน POST
- แสดง Error ชัดเจน
- ใช้ Bootstrap 5
- ไม่ทำ SPA ในรุ่นแรก

## การดำเนินงานรอบแรก

เริ่มจากตรวจสอบว่าโฟลเดอร์ปัจจุบันมี Laravel Project อยู่แล้วหรือไม่

### กรณียังไม่มี Project

ให้สร้าง Project ใหม่และทำสิ่งต่อไปนี้

1. Laravel Project
2. ตั้งค่า `.env.example`
3. Authentication
4. Roles/Permissions
5. Layout ภาษาไทย
6. Migration พื้นฐาน
7. Seeder
8. Test Environment
9. README Installation
10. เริ่ม Module ตามลำดับใน `SKILL.md`

### กรณีมี Project แล้ว

1. วิเคราะห์โครงสร้างเดิม
2. ห้ามเขียนทับฟังก์ชันที่ใช้งานได้
3. ระบุสิ่งที่ขาด
4. ทำ Module ถัดไป
5. รัน Test เดิมก่อนแก้และหลังแก้

## รูปแบบรายงานความคืบหน้า

หลังจบแต่ละ Module ให้รายงาน

- สิ่งที่ทำเสร็จ
- ไฟล์ที่เพิ่ม
- ไฟล์ที่แก้
- Migration
- คำสั่งที่รัน
- ผล Test
- วิธีทดสอบ
- ปัญหาที่พบ
- งานถัดไป

## คำสั่งเริ่มงาน

เริ่มทำงานทันทีตามลำดับดังนี้

1. วิเคราะห์โปรเจกต์
2. สร้างหรือปรับ Project Setup
3. ทำ Authentication และ Roles
4. ทำ Units, Warehouses และ Products
5. ทำ Stock Core Service พร้อม Automated Test
6. ทำ Part In และ Part Out
7. ทำ FG In และ FG Out
8. ทำ Stock Balance และ Stock Card
9. ทำ Adjustment และ Reversal
10. ทำ Dashboard และ Reports
11. ทำ Import/Export
12. ทำ Audit Log
13. ทำ Deployment Guide และ Backup Guide
14. รัน Test ทั้งระบบ
15. แก้ข้อผิดพลาดทั้งหมด
16. สรุประบบพร้อมข้อมูลบัญชีทดสอบและวิธีติดตั้ง

อย่าหยุดเพียงการอธิบายหรือสร้างแผน ให้สร้างโค้ดจริงในโปรเจกต์ ตรวจสอบ และทดสอบจนระบบตาม Scope ใช้งานได้
