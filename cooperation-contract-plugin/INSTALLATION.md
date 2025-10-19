# راهنمای نصب سریع - Quick Installation Guide

## فارسی

### مرحله 1: آپلود افزونه

**روش الف: از طریق پنل مدیریت**

1. وارد پنل مدیریت وردپرس شوید
2. به **افزونه‌ها** > **افزودن** بروید
3. روی **بارگذاری افزونه** کلیک کنید
4. فایل ZIP افزونه را انتخاب و آپلود کنید
5. روی **نصب** کلیک کنید
6. افزونه را **فعال** کنید

**روش ب: از طریق FTP**

1. پوشه `cooperation-contract-plugin` را در مسیر `/wp-content/plugins/` آپلود کنید
2. وارد پنل مدیریت شوید
3. به **افزونه‌ها** بروید
4. افزونه "قرارداد همکاری" را فعال کنید

### مرحله 2: تنظیمات

1. در پنل مدیریت به **قراردادهای همکاری** > **تنظیمات** بروید
2. متن قرارداد خود را وارد کنید (یک نمونه در فایل `sample-contract.txt` موجود است)
3. روی **ذخیره تنظیمات** کلیک کنید

### مرحله 3: ایجاد صفحه قرارداد

1. به **صفحات** > **افزودن** بروید
2. عنوان صفحه را وارد کنید (مثلاً "قرارداد همکاری")
3. در محتوا، شورت‌کد زیر را وارد کنید:
   ```
   [cooperation_contract]
   ```
4. صفحه را **منتشر** کنید

### مرحله 4: تست

1. صفحه منتشر شده را باز کنید
2. فرم را با اطلاعات تستی پر کنید
3. امضای تست بزنید
4. قرارداد را ثبت کنید
5. به پنل مدیریت بروید و قرارداد را مشاهده کنید

---

## English

### Step 1: Upload Plugin

**Method A: Through WordPress Admin Panel**

1. Login to WordPress admin panel
2. Go to **Plugins** > **Add New**
3. Click **Upload Plugin**
4. Choose the plugin ZIP file and upload
5. Click **Install Now**
6. **Activate** the plugin

**Method B: Via FTP**

1. Upload `cooperation-contract-plugin` folder to `/wp-content/plugins/`
2. Login to admin panel
3. Go to **Plugins**
4. Activate "قرارداد همکاری" plugin

### Step 2: Settings

1. Go to **قراردادهای همکاری** > **تنظیمات** in admin panel
2. Enter your contract text (a sample is available in `sample-contract.txt`)
3. Click **Save Settings**

### Step 3: Create Contract Page

1. Go to **Pages** > **Add New**
2. Enter page title (e.g., "Cooperation Contract")
3. Add the following shortcode to content:
   ```
   [cooperation_contract]
   ```
4. **Publish** the page

### Step 4: Test

1. Open the published page
2. Fill the form with test data
3. Add a test signature
4. Submit the contract
5. Go to admin panel and view the submitted contract

---

## نیازمندی‌ها / Requirements

- WordPress 5.0+
- PHP 7.0+
- MySQL 5.6+

## پشتیبانی / Support

برای مشکلات یا سوالات، به فایل README.md مراجعه کنید.
For issues or questions, refer to README.md file.
