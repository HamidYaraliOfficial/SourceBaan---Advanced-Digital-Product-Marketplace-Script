# SourceBaan

انجمن پیشرفته اشتراک‌گذاری سورس کد با PHP و ذخیره‌سازی JSON

## راه‌اندازی سریع

1) کل پوشه را روی هاست cPanel آپلود کنید (ریشه دامنه یا ساب‌دامین)
2) مطمئن شوید `data/` و `uploads/` قابل نوشتن هستند (Permission 775)
3) سایت را باز کنید: `index.php`

## ورود ادمین

- ایمیل: `admin@sourcebaan.local`
- رمز: `SourceBaan@123`

پس از ورود به اکانت ادمین، به مسیر `/admin/` بروید تا ارسال‌های در انتظار را تایید/رد کنید.

## محدودیت آپلود
- حداکثر حجم: 1MB
- پسوندهای مجاز: zip, rar, js, py, php, java, cpp, html, css, txt

## ساختار داده‌ها (JSON)
- `data/users.json`: کاربران
- `data/projects.json`: پروژه‌های تایید شده
- `data/submissions.json`: ارسال‌های در انتظار
- `uploads/pending`: فایل‌های ارسال شده تا قبل از تایید
- `uploads/approved`: فایل‌های تایید شده
