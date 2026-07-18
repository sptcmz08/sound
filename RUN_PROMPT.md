# RUN PROMPT — Copy ทั้งหมดไปสั่ง AI Coding Agent

ให้คุณทำหน้าที่เป็น Senior Full-Stack Developer และพัฒนาระบบคุมสต๊อก Part และ FG ให้ใช้งานจริง

## เทคโนโลยีที่ต้องใช้

- Backend: PHP 8.2+ และ Laravel
- Database: MySQL 8 หรือ MariaDB
- Frontend: Laravel Blade, Bootstrap 5, JavaScript หรือ Alpine.js
- Authentication: Laravel Authentication
- Testing: PHPUnit หรือ Pest
- Deployment: รองรับ Plesk, DirectAdmin และ Ubuntu VPS
- ภาษาในระบบ: ภาษาไทย
- รูปแบบระบบ: Web Application แบบ Responsive

ห้ามเปลี่ยนเป็น Node.js, React SPA, Next.js, Python หรือ Framework อื่น เว้นแต่ได้รับคำสั่งใหม่จากฉัน

## ไฟล์ที่ต้องอ่านก่อนเริ่ม

เปิดและอ่านไฟล์ต่อไปนี้ทั้งหมดก่อนเขียนโค้ด

1. `SKILL.md`
2. `STOCK_SYSTEM_SPEC.md`
3. `README_AI_USAGE.md`
4. `README.md` ถ้ามี
5. `CHANGELOG.md` ถ้ามี
6. `.env.example` ถ้ามี
7. โครงสร้าง Source Code, Routes, Migrations, Models, Services และ Tests ที่มีอยู่

ให้ใช้ `SKILL.md` เป็นกฎควบคุมการทำงาน และใช้ `STOCK_SYSTEM_SPEC.md` เป็น Requirement หลัก

## เป้าหมายของระบบรุ่นแรก

สร้างระบบคุมสต๊อกที่ใช้งานง่ายและพร้อมใช้งานจริง โดยมีฟังก์ชันดังนี้

1. Login และจัดการผู้ใช้งาน
2. สิทธิ์ Administrator, Stock Staff และ Viewer
3. ข้อมูลหน่วยนับ
4. ข้อมูลคลังหลัก
5. ข้อมูลสินค้า แยกประเภท PART และ FG
6. รับ Part เข้าสต๊อก
7. จ่าย Part ออกจากสต๊อก
8. รับ FG เข้าสต๊อก
9. จ่าย FG ออกจากสต๊อก
10. ดูยอดสต๊อกคงเหลือ
11. ดู Stock Card
12. ปรับเพิ่มและปรับลดสต๊อก
13. ยกเลิกเอกสารด้วยรายการ Reversal
14. Dashboard
15. รายงานรับเข้า จ่ายออก คงเหลือ และสินค้าใกล้หมด
16. Import สินค้าจาก Excel/CSV
17. Export Excel/CSV
18. ค้นหาด้วยรหัสสินค้า ชื่อสินค้า และ Barcode
19. Audit Log
20. คู่มือติดตั้งและสำรองข้อมูล

## กฎสต๊อกที่ห้ามละเมิด

- ห้าม Controller แก้ยอดคงเหลือโดยตรง
- การเปลี่ยนยอดทั้งหมดต้องผ่าน `StockService`
- ทุกการรับ จ่าย ปรับยอด และย้อนรายการ ต้องสร้าง `stock_transactions`
- ใช้ Database Transaction ทุกครั้งที่เปลี่ยนยอด
- ใช้ Row Lock ตอนตรวจสอบและตัดยอด
- ห้ามจ่ายหรือปรับลดจนยอดติดลบ
- ป้องกันการกดบันทึกซ้ำและ Double Posting
- เอกสารที่ POSTED แล้วห้ามแก้ไขรายการ
- การยกเลิกเอกสารต้องสร้าง Reversal ห้ามลบ Transaction เดิม
- Stock Card ต้องอ่านจาก Stock Transaction
- สินค้าที่มีประวัติแล้วห้าม Hard Delete
- ทุกการเปลี่ยนแปลงสำคัญต้องมี Audit Log
- Validation ฝั่ง Server เป็นแหล่งข้อมูลจริง
- ห้ามใช้ JavaScript หรือค่าจาก Browser เป็นตัวตัดสินยอดสต๊อก
- ต้องรองรับผู้ใช้หลายคนทำรายการพร้อมกันโดยยอดไม่ผิด

## ขั้นตอนการทำงาน

### กรณีโฟลเดอร์ยังไม่มี Laravel Project

1. สร้าง Laravel Project ในโฟลเดอร์ปัจจุบัน
2. ติดตั้ง Authentication
3. ตั้งค่า Bootstrap 5
4. สร้าง `.env.example`
5. สร้าง Migration, Seeder และบัญชีทดสอบ
6. สร้างระบบตามลำดับ Module ที่กำหนดด้านล่าง
7. เขียน Test และรัน Test ทุก Module

### กรณีมี Project อยู่แล้ว

1. วิเคราะห์โครงสร้างเดิมก่อน
2. รัน Test เดิม
3. ตรวจสอบสิ่งที่ทำแล้วและสิ่งที่ยังขาด
4. ห้ามสร้างไฟล์หรือฟังก์ชันซ้ำ
5. พัฒนาต่อจากสถานะล่าสุด
6. รัน Test เดิมและ Test ใหม่ทุกครั้ง

## ลำดับ Module ที่ต้องทำ

1. Project Setup
2. Authentication
3. Roles และ Permissions
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
14. Stock Adjustment
15. Cancellation และ Reversal
16. Dashboard
17. Reports
18. Import/Export
19. Audit Log
20. Installation, Deployment, Backup และ Restore Guide

ห้ามข้าม Stock Core Service แล้วเขียนหน้ารับหรือจ่ายด้วยการอัปเดตยอดโดยตรง

## สิ่งที่ต้องสร้างในแต่ละ Module

แต่ละ Module ต้องมีส่วนที่เกี่ยวข้องให้ครบ ได้แก่

- Migration
- Model และ Relationship
- Enum หรือ Constant
- Form Request Validation
- Service หรือ Action
- Controller
- Policy หรือ Permission
- Route
- Blade UI
- Responsive UI
- Automated Test
- README หรือ Documentation
- CHANGELOG

Controller ต้องบาง และ Business Logic ต้องอยู่ใน Service

## UX/UI

- เมนูและข้อความภาษาไทย
- ใช้งานง่ายสำหรับเจ้าหน้าที่สต๊อก
- ปุ่มรับ Part, จ่าย Part, รับ FG และจ่าย FG ต้องเห็นชัด
- เพิ่มสินค้าได้หลายรายการในเอกสารเดียว
- ช่อง Barcode ต้องพร้อมรับค่าจากเครื่องยิง Barcode
- แสดงยอดคงเหลือก่อนจ่าย
- แสดงยอดก่อนและหลังทำรายการ
- มีหน้าต่างยืนยันก่อน POST
- แสดงข้อความ Error ชัดเจน
- รองรับคอมพิวเตอร์ แท็บเล็ต และมือถือ
- ห้ามทำ UI ซับซ้อนเกิน Scope รุ่นแรก
- ห้ามใช้ Mock Data ในหน้าจอใช้งานจริง

## Automated Test ที่บังคับ

อย่างน้อยต้องทดสอบ

- สร้าง PART และ FG
- รหัสสินค้าและ Barcode ซ้ำไม่ได้
- รับ Part แล้วสต๊อกเพิ่มถูกต้อง
- จ่าย Part แล้วสต๊อกลดถูกต้อง
- จ่ายเกินยอดไม่ได้
- รับและจ่าย FG ถูกต้อง
- PART ใช้ในหน้า FG ไม่ได้
- FG ใช้ในหน้า PART ไม่ได้
- กด POST ซ้ำไม่เพิ่มหรือลดยอดซ้ำ
- ผู้ใช้สองคนจ่ายพร้อมกันแล้วสต๊อกไม่ติดลบ
- ปรับลดเกินยอดไม่ได้
- Viewer แก้ไขข้อมูลไม่ได้
- ยกเลิกเอกสารแล้ว Reversal และ Balance ถูกต้อง
- ยกเลิกซ้ำไม่ได้
- Stock Card ตรงกับ Stock Balance
- Import และ Export ทำงานได้

## วิธีรายงานผลหลังจบแต่ละ Module

ให้รายงานดังนี้

1. สิ่งที่ทำเสร็จ
2. ไฟล์ที่เพิ่ม
3. ไฟล์ที่แก้ไข
4. Migration ที่สร้าง
5. คำสั่งที่รัน
6. ผล Automated Test
7. วิธีทดสอบด้วยตนเอง
8. Error หรือข้อจำกัดที่พบ
9. Module ถัดไป

ห้ามตอบเพียงว่า “เสร็จแล้ว”

## คำสั่งเริ่มทำงาน

เริ่มทำงานทันที ไม่ต้องหยุดแค่การวางแผน

1. ตรวจสอบโฟลเดอร์และวิเคราะห์สถานะโปรเจกต์
2. อ่าน `SKILL.md` และ `STOCK_SYSTEM_SPEC.md`
3. สร้างแผนสั้น ๆ จากสถานะจริง
4. เริ่มสร้างหรือปรับ Laravel Project
5. พัฒนาตามลำดับ Module
6. รัน Migration และ Automated Test ใน Development Environment
7. แก้ Error ที่พบ
8. ทำงานต่อจนระบบตาม Scope ใช้งานได้
9. อัปเดต README และ CHANGELOG
10. สรุปบัญชีทดสอบ คำสั่งติดตั้ง และวิธีเปิดระบบ

ห้ามลบฐานข้อมูลจริง ห้ามใช้ `migrate:fresh` กับ Production และห้าม Deploy Production โดยไม่ได้รับคำสั่งชัดเจน

เริ่มตรวจสอบโปรเจกต์และลงมือพัฒนาตอนนี้
