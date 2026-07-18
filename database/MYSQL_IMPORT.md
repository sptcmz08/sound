# MySQL database

ไฟล์ `sound_db.sql` มีโครงสร้างตารางและข้อมูลธุรกิจจากฐานข้อมูล local พร้อมนำเข้าฐานข้อมูล `sound_db` บน MySQL/MariaDB ได้ทันที

```bash
mysql -u root -p -e "CREATE DATABASE sound_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p sound_db < database/sound_db.sql
```

ตั้งค่า `.env` สำหรับใช้งาน MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sound_db
DB_USERNAME=root
DB_PASSWORD=
```

ตารางชั่วคราว เช่น `sessions`, `cache`, `jobs` และ `password_reset_tokens` มีเฉพาะโครงสร้างและไม่ได้ส่งออกข้อมูล เพื่อไม่ให้นำ session หรือ token จากเครื่องพัฒนาไปใช้ในระบบใหม่

หากต้องการสร้างฐานข้อมูลว่างโดยไม่ใช้ข้อมูลตัวอย่าง ให้ตั้งค่า `.env` แล้วรัน:

```bash
php artisan migrate --force
```
