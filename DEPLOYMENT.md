# การติดตั้ง Production และสำรองข้อมูล

## ค่าพื้นฐาน Production

ใช้ PHP 8.2+, MySQL 8/MariaDB, extensions `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath` และ Composer 2 จากนั้นตั้ง document root ไปที่โฟลเดอร์ `public` เท่านั้น

```bash
composer install --no-dev --classmap-authoritative
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

ตั้ง `.env`: `APP_ENV=production`, `APP_DEBUG=false`, HTTPS URL, MySQL credentials, `SESSION_SECURE_COOKIE=true` และรหัส `SEED_USER_PASSWORD` ที่สุ่มยาวก่อน seed ครั้งแรก ห้ามรัน `migrate:fresh` หรือ seed ซ้ำโดยไม่ตรวจผลกระทบ

## Plesk / DirectAdmin

1. เลือก PHP 8.2+ และเปิด extensions ด้านบน
2. ชี้ Document Root ไป `{project}/public`
3. อัปโหลด project โดยไม่รวม `.env` แล้วสร้าง `.env` บน server
4. รันคำสั่งติดตั้งผ่าน SSH; หากไม่มี SSH ให้ build `vendor` ใน environment PHP รุ่นเดียวกันก่อนอัปโหลด
5. ให้ web user เขียนได้เฉพาะ `storage` และ `bootstrap/cache`
6. ตั้ง cron ทุกนาที: `php /path/to/artisan schedule:run`

## Ubuntu VPS

ใช้ Nginx/Apache + PHP-FPM และตั้ง virtual host root เป็น `/var/www/simple-stock/public`; ปิด directory listing, บังคับ HTTPS และให้ owner ของ `storage`/`bootstrap/cache` เป็น web user จากนั้นตั้ง queue worker เมื่อเปิดใช้ queue ในอนาคต

## Backup

ก่อน migrate หรือ deploy ทุกครั้ง:

```bash
chmod +x scripts/backup-database.sh
./scripts/backup-database.sh
tar -czf storage-$(date +%F-%H%M).tar.gz storage/app
```

เก็บไฟล์ backup แบบเข้ารหัสนอกเครื่องและทดสอบ restore เป็นระยะ สคริปต์อ่าน DB credentials จาก `.env` และไม่พิมพ์รหัสผ่านลง console

## Restore

1. เปิด maintenance: `php artisan down`
2. สำรองฐานปัจจุบันอีกครั้ง
3. สร้างฐานเปล่าและ import: `mysql -h HOST -u USER -p DATABASE < backup.sql`
4. คืน `storage/app`, รัน `php artisan migrate --force` และ `php artisan optimize:clear`
5. ตรวจ `php artisan stock:rebuild-balances --verify` และทดสอบ login/report
6. เปิดระบบ: `php artisan up`
