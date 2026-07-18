# Simple Stock AI Kit

ชุดไฟล์นี้ใช้สำหรับสั่ง AI Coding Agent ให้สร้างระบบคุมสต๊อก Part และ FG

## ไฟล์ภายใน

- `STOCK_SYSTEM_SPEC.md` — Requirement และโครงสร้างระบบ
- `SKILL.md` — กฎควบคุมวิธีทำงานของ AI
- `MASTER_PROMPT.md` — Prompt หลักสำหรับเริ่มพัฒนาระบบ
- `README_AI_USAGE.md` — วิธีใช้งานชุดคำสั่งนี้

## วิธีใช้งานกับ AI Coding Agent

1. สร้างโฟลเดอร์โปรเจกต์
2. นำไฟล์ทั้ง 4 ไฟล์วางไว้ที่ Root ของโปรเจกต์
3. เปิดโฟลเดอร์ด้วย AI Coding Agent เช่น Cline, Codex หรือ IDE Agent ที่รองรับอ่าน/เขียนไฟล์และรันคำสั่ง
4. เปิดไฟล์ `MASTER_PROMPT.md`
5. Copy เนื้อหาทั้งหมดไปสั่ง AI
6. อนุญาตให้ AI อ่านไฟล์ สร้างโค้ด รัน Migration และ Test ใน Environment สำหรับพัฒนา
7. ตรวจผลสรุปของ AI หลังจบแต่ละ Module
8. Commit Git หลัง Module ผ่าน Test

## คำแนะนำ

- ควรใช้ Git ตั้งแต่เริ่มต้น
- ควรแยก Development, Staging และ Production
- ห้ามให้ AI รันคำสั่งลบฐานข้อมูลบน Production
- ก่อน Deploy ให้สำรอง Database และไฟล์
- ให้ AI แสดง Migration และผล Test ทุกครั้ง
- อย่าใช้ `php artisan migrate:fresh` กับฐานข้อมูลจริง
- เปลี่ยนข้อมูลบัญชีตัวอย่างก่อนเปิดใช้งาน

## Prompt สำหรับทำงานต่อหลังจาก AI หยุด

```text
อ่าน SKILL.md, STOCK_SYSTEM_SPEC.md, README.md และ CHANGELOG.md อีกครั้ง
ตรวจสอบสถานะงานล่าสุดจาก Git และ Test
ทำ Module ถัดไปตามลำดับใน SKILL.md
ห้ามทำซ้ำ Module ที่เสร็จแล้ว
รัน Test ทั้งหมดหลังแก้ไข
อัปเดต README.md และ CHANGELOG.md
สรุปไฟล์ที่แก้ คำสั่งที่รัน ผล Test และวิธีทดสอบ
```

## Prompt สำหรับแก้ Bug

```text
อ่าน SKILL.md และ STOCK_SYSTEM_SPEC.md ก่อน
วิเคราะห์ Bug จาก Error, Log และขั้นตอนที่ทำให้เกิดปัญหา
สร้าง Automated Test ที่ทำให้ Bug เกิดซ้ำ
แก้ Root Cause โดยไม่ปิด Validation, Permission หรือ Security
รัน Test ที่เกี่ยวข้องและ Test ทั้งระบบ
สรุป Root Cause ไฟล์ที่แก้ ผล Test และวิธีตรวจสอบ
```

## Prompt สำหรับเพิ่มฟังก์ชันในอนาคต

```text
อ่าน SKILL.md, STOCK_SYSTEM_SPEC.md และโครงสร้างฐานข้อมูลปัจจุบัน
วิเคราะห์ผลกระทบของฟังก์ชันใหม่ต่อ Stock Transaction, Stock Balance,
Permission, Audit Log, Report และข้อมูลเดิม
เสนอ Migration แบบไม่ทำลายข้อมูล
สร้าง Test ก่อนหรือพร้อมกับโค้ด
ห้ามแก้ Migration เก่าที่ใช้งาน Production แล้ว
อัปเดต Documentation และ CHANGELOG
```

## Prompt ตรวจรับระบบ

```text
ตรวจระบบทั้งหมดตาม Acceptance Criteria ใน STOCK_SYSTEM_SPEC.md
รัน Automated Test ทั้งหมด
ตรวจ Permission ทุก Role
ตรวจ Double Submit
ตรวจ Concurrent Stock Out
ตรวจ Reversal
ตรวจ Stock Card เทียบ Stock Balance
ตรวจ Import/Export
ตรวจ Responsive UI
ตรวจ Production Security Configuration
สรุปสิ่งที่ผ่าน ไม่ผ่าน และคำสั่งแก้ไข
```
