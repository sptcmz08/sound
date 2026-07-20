# PART / WIP / FG Stock Control

ระบบคุมสต็อกภาษาไทยบน Laravel 12 / PHP 8.2 สำหรับ PART, WIP และ FG รองรับรับเข้าจาก Supplier, เบิก-จ่าย, ผลิต WIP/FG ตามสูตร, ขาย, เคลม, ของเสีย, ต้นทุน-กำไร, Reversal, Stock Card, รายงาน Excel/CSV, Import สินค้า, ผู้ใช้ 3 ระดับ และ Audit Log

Frontend ใช้ Laravel Blade, Tailwind CSS 4 และ Vite พร้อม responsive admin sidebar สำหรับ desktop, tablet และ mobile

## ติดตั้งสำหรับ Development

```bash
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
# ตั้งค่า DB_* และ SEED_USER_PASSWORD ใน .env
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

เปิด `http://127.0.0.1:8000` บัญชีทดสอบคือ `admin@example.com`, `stock@example.com`, `viewer@example.com` และใช้รหัสจาก `SEED_USER_PASSWORD` (ค่า development เริ่มต้น `ChangeMe123!`) ทุกบัญชีถูกกำหนดให้เปลี่ยนรหัสก่อนใช้จริง ห้ามใช้รหัสนี้ใน Production

## ลำดับการเริ่มใช้งาน

1. เข้า **ตั้งค่าระบบ** เพื่อเพิ่มหน่วยนับและคลังสินค้า
2. เข้า **สินค้า** แล้วกด **เพิ่มสินค้าใหม่** หรือ Import Excel/CSV (ข้อมูลบันทึกในฐานข้อมูล ไม่ได้ hardcode)
3. ใช้เมนู **รับเข้า (Supplier)** เพื่อรับ PART พร้อมต้นทุนเข้าสต็อก
4. ใช้เมนู **ส่งเข้า WIP / FG** เพื่อผลิตตามสูตร ระบบจะตัดส่วนประกอบและเพิ่มชิ้นงานที่ผลิตเสร็จเข้าสต็อกพร้อมกัน
5. ใช้เมนู **เบิก-จ่าย**, **ขาย**, **เคลม** และ **ของเสีย** ตามงานจริง ระบบจะไม่อนุญาตให้ยอดติดลบ
6. ตรวจยอดที่ **สต็อกคงเหลือ** และดูรายงานต้นทุน-กำไร เบิก-จ่าย ขาย เคลม และของเสีย

## การใช้เครื่องสแกน Barcode

สินค้าใหม่ที่ไม่ได้กรอก Barcode จะได้รับรหัส Code 128 รูปแบบ `STK-00000001` อัตโนมัติ เข้าเมนู **สินค้า** แล้วกด **ฉลาก** เพื่อเลือกจำนวนและพิมพ์ฉลาก จากนั้นนำเครื่องสแกน USB แบบ HID Keyboard ไปยิงที่ช่อง Barcode ในหน้ารับหรือจ่ายสินค้า เครื่องสแกนควรตั้ง suffix เป็น Enter หรือ Tab เมื่อยิงครั้งแรกระบบจะเพิ่มสินค้าและจำนวน 1 เมื่อยิงสินค้าเดิมซ้ำระบบจะเพิ่มจำนวนเป็น 2, 3, 4 ตามลำดับ พร้อมเสียงและข้อความยืนยัน

Administrator เพิ่มและแก้ไขหน่วยนับ คลัง สินค้า และผู้ใช้ได้ทั้งหมด รายการที่ยังไม่มีประวัติสามารถลบได้ ส่วนรายการที่ถูกอ้างอิงในประวัติสต็อกจะถูกปิดใช้งานแทน เพื่อรักษาความถูกต้องของรายงานย้อนหลัง เอกสารที่ POSTED แล้วใช้การยกเลิกแบบ REVERSAL แทนการแก้หรือลบโดยตรง

## คำสั่งตรวจสอบ

```bash
vendor/bin/pint --test app bootstrap config database routes tests
php artisan test
php artisan route:list
php artisan stock:rebuild-balances --verify
```

`stock:rebuild-balances --verify` ตรวจยอดโดยไม่แก้ข้อมูล หากต้องการสร้าง balance cache ใหม่จาก ledger ให้สำรองฐานข้อมูลก่อนแล้วรันโดยไม่ใส่ `--verify`

## Import/Export Excel และ CSV

หน้า Import รองรับ `.xlsx`, `.xls`, `.csv` และ `.txt` ขนาดไม่เกิน 10 MB สูงสุด 5,000 รายการ ดาวน์โหลดแบบฟอร์ม Excel จากหน้า Import ได้ หัวตารางคือ:

```csv
code,name,product_type,unit_code,barcode,minimum_stock,location_text
P-100,ชิ้นส่วนตัวอย่าง,PART,PCS,885000000001,5,A-01
```

รหัส `unit_code` ต้องมีในระบบแล้ว รายการรหัสสินค้าเดิมจะถูกอัปเดต และการนำเข้าทั้งไฟล์ทำใน database transaction หากมีแถวใดผิดจะ rollback ทั้งไฟล์

หน้าสต๊อกคงเหลือ Export ได้ทั้ง UTF-8 CSV และ Excel `.xlsx` โดย Excel มีหัวตาราง, filter, freeze header, รูปแบบตัวเลข และสถานะสต๊อกพร้อมใช้งาน

## หลักความปลอดภัยของสต๊อก

- Controller ไม่แก้ยอดโดยตรง ทุกยอดผ่าน `StockService`
- ใช้ database transaction, row lock และ decimal 18,4
- POST ซ้ำถูกกันด้วย UUID idempotency key
- Transaction เดิมไม่ถูกลบ การยกเลิกสร้าง REVERSAL
- POSTED document แก้รายการไม่ได้ และการตัดยอดติดลบถูกปฏิเสธฝั่ง server

ดูขั้นตอน Production, Plesk, DirectAdmin, Ubuntu, backup และ restore ที่ [DEPLOYMENT.md](DEPLOYMENT.md)
