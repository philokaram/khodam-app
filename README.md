## نقل المشروع إلى استضافة أخرى

1. انسخ كل محتويات المجلد إلى الاستضافة الجديدة.
2. تأكد أن **document root** يشير إلى مجلد `public/`.
   - إذا لم يكن ممكناً، اترك `.htaccess` الجذري يقوم بإعادة التوجيه.
3. استورد قاعدة البيانات:
   ```bash
   mysql -u USER -p DB_NAME < database/schema.sql
   mysql -u USER -p DB_NAME < database/seeds/initial.sql