# دليل التثبيت — INSTALLATION

دليل مختصر لتثبيت **Qurtuba Project Tracker** محليًا. للنشر على السحابة راجع [`Documentation/06-Deployment-Guide.md`](Documentation/06-Deployment-Guide.md).

---

## 1) المتطلبات

| المتطلب | الإصدار |
|---|---|
| PHP | 8.2 أو أحدث |
| Composer | 2.x |
| MySQL / MariaDB | MySQL 8 / MariaDB 10.4+ |
| إضافات PHP | `intl`, `zip`, `gd`, `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`, `xml` |
| Node.js (اختياري) | 18+ (للتطوير فقط — غير مطلوب للتشغيل) |

> على Windows مع XAMPP: فعّل `extension=intl` و`extension=gd` و`extension=zip` في `php.ini`.

---

## 2) الخطوات

```bash
# استنساخ المشروع
git clone <repo-url> QurtubaProjectTracker
cd QurtubaProjectTracker

# تثبيت اعتماديات PHP
composer install

# ملف البيئة ومفتاح التطبيق
cp .env.example .env
php artisan key:generate
```

### اضبط قاعدة البيانات في `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qurtuba
DB_USERNAME=root
DB_PASSWORD=

# بيانات الأدمن الأولى (تُستخدم في البذرة)
ADMIN_EMAIL=admin@qurtuba.test
ADMIN_PASSWORD=change-me
ADMIN_NAME=مدير النظام
```

أنشئ قاعدة البيانات:

```sql
CREATE DATABASE qurtuba CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### الترحيل والبذر

```bash
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
```

`ProductionSeeder` **idempotent**: يمكن تشغيله مرارًا دون تكرار البيانات أو مسح تعديلات المستخدمين. ينشئ:
- مستخدم الأدمن (من `ADMIN_EMAIL` / `ADMIN_PASSWORD`).
- 8 أقسام + 7 موظفين حقيقيين (كلمة المرور الأولية `123456`).
- مشروع «تشغيل مباني قرطبة» + 58 مهمة.

### ربط التخزين والتشغيل

```bash
php artisan storage:link
php artisan serve
```

افتح: `http://127.0.0.1:8000/admin`

---

## 3) التحقق من التثبيت

```bash
php artisan test        # تشغيل الاختبارات
php artisan about       # ملخص البيئة
```

يجب أن تمرّ حزمة الاختبارات (منها `AdminSmokeTest` التي تفتح كل صفحات اللوحة).

---

## 4) مشاكل شائعة

| المشكلة | الحل |
|---|---|
| `could not find driver` | فعّل `pdo_mysql` في `php.ini`. |
| صفحات بلا تنسيق (CSS/JS) | نفّذ `php artisan filament:assets` ثم `php artisan storage:link`. |
| `ext-intl` مفقود لـ Filament | فعّل `extension=intl` في `php.ini`. |
| رفع الملفات يتجمّد على `php artisan serve` (Windows) | خادم PHP المدمج أحادي الخيط؛ استخدم Apache/Nginx أو عدّة عمّال، أو اختبر على الخادم الحقيقي. |
| بطء أول تحميل | نفّذ `php artisan optimize` لبناء الكاش. |

للمزيد راجع [`Documentation/06-Deployment-Guide.md`](Documentation/06-Deployment-Guide.md).
