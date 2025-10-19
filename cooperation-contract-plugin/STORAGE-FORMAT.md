# فرمت ذخیره‌سازی قراردادها - Storage Format Documentation

## نحوه ذخیره‌سازی

هنگامی که یک قرارداد ثبت می‌شود، اطلاعات به شکل زیر ذخیره می‌گردد:

---

## 📊 دیتابیس MySQL

### جدول: `wp_cooperation_contracts`

تمام قراردادها در یک جدول MySQL با ساختار زیر ذخیره می‌شوند:

| ستون | نوع | توضیحات |
|------|-----|---------|
| `id` | mediumint(9) | شناسه یکتای قرارداد (Auto Increment) |
| `first_name` | varchar(100) | نام فرد |
| `last_name` | varchar(100) | نام خانوادگی فرد |
| `national_id` | varchar(10) | کد ملی 10 رقمی |
| `institution_name` | varchar(255) | نام آموزشگاه یا سازمان |
| `position` | varchar(100) | سمت یا عنوان شغلی |
| `address` | text | آدرس کامل |
| `contract_date` | varchar(50) | تاریخ قرارداد (شمسی، فرمت: YYYY/MM/DD) |
| `selected_plan` | varchar(50) | طرح انتخابی (طرح سایت / طرح گروه / طرح طلایی) |
| `signature_data` | longtext | داده امضای دیجیتال (Base64) |
| `created_at` | datetime | زمان ثبت قرارداد (تاریخ میلادی) |
| `user_id` | bigint(20) | شناسه کاربر وردپرس (در صورت ورود) |
| `ip_address` | varchar(45) | آدرس IP ثبت‌کننده |

---

## 🖊️ فرمت امضای دیجیتال

### ذخیره‌سازی:
امضا به صورت **Data URL (Base64)** ذخیره می‌شود:

```
data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA...
```

### مشخصات:
- **فرمت تصویر**: PNG
- **کدگذاری**: Base64
- **پس‌زمینه**: سفید (RGB: 255, 255, 255)
- **رنگ خط امضا**: مشکی (RGB: 0, 0, 0)
- **کیفیت**: HD (تطبیق با DPI صفحه)

### حجم تقریبی:
- امضای ساده: 5-15 KB
- امضای پیچیده: 15-50 KB

---

## 📅 فرمت تاریخ

### تاریخ قرارداد (`contract_date`):
```
فرمت: YYYY/MM/DD (شمسی)
مثال: 1403/08/01
```

### تاریخ ثبت (`created_at`):
```
فرمت: YYYY-MM-DD HH:MM:SS (میلادی)
مثال: 2025-10-19 14:30:25
```

---

## 💾 نمونه رکورد

```sql
INSERT INTO wp_cooperation_contracts VALUES (
    1,                                  -- id
    'علی',                             -- first_name
    'محمدی',                           -- last_name
    '1234567890',                      -- national_id
    'آموزشگاه علم و دانش',            -- institution_name
    'مدیر آموزش',                      -- position
    'تهران، خیابان ولیعصر، پلاک 123', -- address
    '1403/08/01',                      -- contract_date
    'طرح گروه',                        -- selected_plan
    'data:image/png;base64,iVBOR...',  -- signature_data
    '2025-10-19 14:30:25',            -- created_at
    5,                                 -- user_id (یا NULL)
    '192.168.1.1'                      -- ip_address
);
```

---

## 🔍 استخراج و بازیابی داده‌ها

### روش 1: از طریق پنل مدیریت وردپرس
```
داشبورد → قراردادهای همکاری → مشاهده
```

### روش 2: Query مستقیم MySQL
```sql
SELECT 
    id,
    CONCAT(first_name, ' ', last_name) AS full_name,
    national_id,
    institution_name,
    position,
    contract_date,
    selected_plan,
    created_at
FROM wp_cooperation_contracts
ORDER BY created_at DESC;
```

### روش 3: Export امضا به فایل تصویری

امضا را می‌توان به این روش استخراج کرد:

```php
<?php
// دریافت امضا از دیتابیس
$signature_base64 = $contract->signature_data;

// حذف prefix
$image_data = str_replace('data:image/png;base64,', '', $signature_base64);
$image_data = base64_decode($image_data);

// ذخیره به فایل
file_put_contents('signature_' . $contract->id . '.png', $image_data);
?>
```

---

## 📤 Export و Backup

### Backup دیتابیس:
```bash
mysqldump -u username -p database_name wp_cooperation_contracts > contracts_backup.sql
```

### Export به CSV:
از پنل phpMyAdmin یا با query زیر:

```sql
SELECT 
    id,
    first_name,
    last_name,
    national_id,
    institution_name,
    position,
    address,
    contract_date,
    selected_plan,
    created_at,
    ip_address
FROM wp_cooperation_contracts
INTO OUTFILE '/tmp/contracts.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

**توجه**: امضاها در CSV ذخیره نمی‌شوند. برای Export امضاها باید از روش‌های دیگر استفاده شود.

---

## 🔒 امنیت داده‌ها

### رمزنگاری:
- داده‌ها در دیتابیس به صورت Plain Text ذخیره می‌شوند
- برای امنیت بیشتر، از SSL/TLS برای اتصال دیتابیس استفاده کنید

### محافظت:
- تمام ورودی‌ها Sanitize می‌شوند
- از Prepared Statements استفاده می‌شود
- آدرس IP برای Audit Trail ذخیره می‌شود

### توصیه‌ها:
1. Backup منظم از دیتابیس
2. محدود کردن دسترسی به جدول
3. استفاده از HTTPS برای سایت
4. لاگ‌گیری از تغییرات

---

## 📦 حجم ذخیره‌سازی

### تخمین فضای مورد نیاز:

**هر قرارداد**:
- متن‌ها (بدون امضا): ~1-2 KB
- امضای دیجیتال: ~10-30 KB
- **مجموع**: ~15-35 KB

**1000 قرارداد**:
- فضای مورد نیاز: ~15-35 MB

**10,000 قرارداد**:
- فضای مورد نیاز: ~150-350 MB

---

## 🔄 Migration و انتقال داده

برای انتقال قراردادها به سرور دیگر:

```sql
-- Export
mysqldump -u username -p --tables wp_cooperation_contracts > contracts.sql

-- Import در سرور جدید
mysql -u username -p database_name < contracts.sql
```

---

## 📊 گزارش‌گیری

### تعداد کل قراردادها:
```sql
SELECT COUNT(*) FROM wp_cooperation_contracts;
```

### قراردادهای امروز:
```sql
SELECT * FROM wp_cooperation_contracts 
WHERE DATE(created_at) = CURDATE();
```

### آمار بر اساس طرح:
```sql
SELECT selected_plan, COUNT(*) as count 
FROM wp_cooperation_contracts 
GROUP BY selected_plan;
```

---

## 💡 نکات مهم

1. **امضاها حجم زیادی دارند**: در نظر بگیرید که longtext می‌تواند تا 4GB داده نگه دارد
2. **Index**: برای جستجوی سریع‌تر، می‌توانید Index روی `national_id` و `created_at` بگذارید
3. **Cleanup**: قراردادهای قدیمی را Archive کنید تا عملکرد دیتابیس بهتر شود
4. **Privacy**: داده‌های شخصی (کد ملی) محرمانه هستند - رعایت GDPR و قوانین حفاظت از داده‌ها

---

## 🛠️ توسعه و سفارشی‌سازی

برای اضافه کردن فیلدهای جدید:

1. تغییر Schema در `includes/class-database.php`
2. اضافه کردن به فرم در `includes/class-frontend.php`
3. آپدیت متد `save_contract()`
4. غیرفعال و فعال مجدد افزونه برای اجرای Migration

---

**تاریخ به‌روزرسانی**: 1403/08/01
