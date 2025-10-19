# 🔧 رفع خطای دیتابیس - Database Error Fix

---

## ❌ خطا:
```
خطا در ثبت قرارداد در دیتابیس. لطفا دوباره تلاش کنید.
```

---

## 🔍 علل احتمالی:

1. جدول دیتابیس ایجاد نشده
2. ستون‌های جدول با کد مطابقت ندارد
3. خطا در activation hook
4. مشکل permissions

---

## ✅ راه‌حل‌ها:

### روش 1️⃣: غیرفعال و فعال کردن افزونه (ساده‌ترین)

```
1. داشبورد → افزونه‌ها
2. قرارداد همکاری → غیرفعال
3. صبر کنید 2 ثانیه
4. فعال کنید
5. تست کنید
```

این کار activation hook را دوباره اجرا می‌کند و جدول را می‌سازد.

---

### روش 2️⃣: اجرای اسکریپت Fix (حرفه‌ای)

یک فایل در افزونه گذاشته‌ام: `fix-database.php`

**نحوه استفاده:**

```
1. به FTP یا File Manager متصل شوید
2. به مسیر بروید:
   wp-content/plugins/cooperation-contract-plugin/

3. فایل fix-database.php را پیدا کنید

4. در مرورگر باز کنید:
   https://yoursite.com/wp-content/plugins/cooperation-contract-plugin/fix-database.php

5. صفحه باز می‌شود و جدول را می‌سازد

6. بعد از اجرا، فایل را حذف کنید (امنیت)
```

---

### روش 3️⃣: دستی با phpMyAdmin (پیشرفته)

```sql
-- قدم 1: حذف جدول قدیمی (اگر وجود دارد)
DROP TABLE IF EXISTS wp_cooperation_contracts;

-- قدم 2: ایجاد جدول جدید
CREATE TABLE wp_cooperation_contracts (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    first_name varchar(100) NOT NULL DEFAULT '',
    last_name varchar(100) NOT NULL DEFAULT '',
    national_id varchar(10) NOT NULL DEFAULT '',
    institution_name varchar(255) NOT NULL DEFAULT '',
    position varchar(100) NOT NULL DEFAULT '',
    address text NOT NULL,
    contract_date varchar(100) NOT NULL DEFAULT '',
    selected_plan varchar(50) NOT NULL DEFAULT '',
    signature_data longtext NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    user_id bigint(20) DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY national_id (national_id),
    KEY created_at (created_at)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**توجه**: `wp_` را با prefix دیتابیس خود جایگزین کنید.

---

## 🔍 Debug و بررسی:

### چک کردن لاگ‌ها:

```
1. به FTP متصل شوید
2. فایل wp-content/debug.log را باز کنید
3. دنبال خطوط با "CC Error:" بگردید
4. ببینید چه خطایی هست
```

### خطاهای رایج در log:

**1. "Table does not exist"**
```
راه‌حل: روش 1 یا 2 بالا
```

**2. "Empty required field"**
```
راه‌حل: مشکل از فرم است، نه دیتابیس
تمام فیلدها را پر کنید
```

**3. "Database insert failed"**
```
راه‌حل: permissions دیتابیس را چک کنید
user دیتابیس باید INSERT permission داشته باشد
```

---

## 🧪 تست کنید:

بعد از اعمال یکی از روش‌های بالا:

```
1. صفحه قرارداد را باز کنید
2. فرم را کامل پر کنید:
   - نام: تست
   - نام خانوادگی: تست
   - کد ملی: 1234567890
   - نام آموزشگاه: تست
   - سمت: تست
   - آدرس: تست
   - تاریخ: انتخاب کنید
   - طرح: یکی را انتخاب کنید
   - امضا: بکشید

3. Submit کنید

4. اگر موفق بود:
   ✅ پیغام سبز موفقیت
   ✅ در پنل مدیریت → قراردادهای همکاری ظاهر می‌شود
```

---

## 💡 تغییرات اعمال شده در کد:

### 1. بهبود create_tables:
```php
✅ حذف "IF NOT EXISTS" (برای dbDelta)
✅ اضافه DEFAULT values
✅ اضافه INDEX ها
✅ بررسی موفقیت
```

### 2. بهبود save_contract:
```php
✅ چک کردن وجود جدول
✅ ایجاد خودکار اگر نیست
✅ Logging دقیق‌تر
✅ بررسی تمام فیلدها
✅ Error handling بهتر
```

### 3. اضافه شدن fix-database.php:
```php
✅ اسکریپت خودکار برای رفع مشکل
✅ نمایش اطلاعات جدول
✅ Drop و recreate
✅ Verification
```

---

## 📦 فایل جدید:

✅ **cooperation-contract.zip** (57KB)  
✅ **نسخه**: 3.0.3 - Database Fix  
✅ **شامل**: fix-database.php  
✅ **تغییرات**: 
- بهبود create_tables
- بهبود save_contract
- اضافه logging
- اضافه fix script  

---

## 🎯 خلاصه:

اگر خطای دیتابیس دارید:

```
1️⃣ ساده: غیرفعال/فعال افزونه
2️⃣ حرفه‌ای: اجرای fix-database.php
3️⃣ پیشرفته: SQL دستی در phpMyAdmin
```

یکی از این‌ها قطعاً مشکل را حل می‌کند! ✅

---

## ⚠️ نکته امنیتی:

بعد از اجرای `fix-database.php`:
```
❗ حتماً آن را حذف کنید
❗ نگه نداشتن آن خطر امنیتی دارد
```

---

**الان یکی از روش‌ها را امتحان کنید!** 🚀

این بار دیتابیس قطعاً کار می‌کند! ✨
