<div align="center">
<img src="assets/quantum-logo.png" alt="Quantum Dev Team" width="180" />

# 06 — دليل النشر
### Qurtuba Project Tracker · Deployment Guide
<sub>الإصدار 1.0.0 · 2026-07-05 · Quantum Dev Team</sub>
</div>

---

## 1. المتطلبات

| المكوّن | الإصدار / القيمة | ملاحظات |
|---|---|---|
| PHP | **8.2+** | إضافات: `intl`, `zip`, `gd`, `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`, `xml` |
| Laravel | 10.x | مثبّت عبر Composer |
| Composer | 2.x | لإدارة الاعتماديات |
| قاعدة البيانات | MySQL 8 / MariaDB 10.4+ | ترميز `utf8mb4` |
| Node.js / NPM | **غير مطلوب للتشغيل** | Filament يشحن أصولًا مُجمَّعة؛ Node للتطوير الاختياري فقط |
| Redis | غير مطلوب | `CACHE_DRIVER=file`, `QUEUE_CONNECTION=sync` افتراضيًا |
| Queue Worker | غير مطلوب | لا مهام غير متزامنة حاليًا |
| Scheduler (Cron) | غير مطلوب | لا مهام مجدولة (`Console/Kernel` فارغ) |
| Mail | اختياري | لا يعتمد النظام على البريد في التشغيل الأساسي |

---

## 2. متغيّرات البيئة (Environment Variables)

أهم المتغيّرات (من `.env.example`) — اضبطها في الإنتاج:

```env
APP_NAME="Qurtuba Project Tracker"
APP_ENV=production
APP_KEY=            # ولّده بـ php artisan key:generate
APP_DEBUG=false     # مهم في الإنتاج
APP_URL=https://<domain>

DB_CONNECTION=mysql
DB_HOST=<host>
DB_PORT=3306
DB_DATABASE=<db>
DB_USERNAME=<user>
DB_PASSWORD=<secret>

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
FILESYSTEM_DISK=local

# بيانات الأدمن الأولى (تستخدمها ProductionSeeder)
ADMIN_EMAIL=admin@qurtuba.test
ADMIN_PASSWORD=<strong-password>
ADMIN_NAME="مدير النظام"
```

**متغيّرات خاصة بالنشر المرن (اختيارية، يقرؤها `railway-start.sh`):**
| المتغيّر | الافتراضي | الوظيفة |
|---|---|---|
| `PORT` | (يوفّره Railway) | منفذ الخادم |
| `DB_WAIT_ATTEMPTS` | 20 | عدد محاولات انتظار قاعدة البيانات |
| `DB_WAIT_SLEEP` | 3 | ثوانٍ بين المحاولات |
| `DB_FRESH` | (غير مضبوط) | إذا `=1` ينفّذ `migrate:fresh` (⚠️ يمسح البيانات — لمرة واحدة فقط) |

> 🔐 **لا تُودِع `.env` أو الأسرار في Git.** اضبطها في لوحة متغيّرات Railway.

---

## 3. التشغيل المحلي من الصفر

```bash
composer install
cp .env.example .env
php artisan key:generate
# اضبط DB_* في .env وأنشئ القاعدة (utf8mb4)
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
php artisan storage:link
php artisan serve
```

راجع [`INSTALLATION.md`](../INSTALLATION.md) لشرح كل أمر.

### شرح الأوامر
| الأمر | الوظيفة |
|---|---|
| `composer install` | تثبيت اعتماديات PHP. |
| `php artisan key:generate` | توليد `APP_KEY` (تشفير الجلسات/الكوكيز). |
| `php artisan migrate --force` | إنشاء الجداول. |
| `php artisan db:seed --class=ProductionSeeder --force` | البيانات الأولية (idempotent). |
| `php artisan storage:link` | ربط `storage/app/public` بـ `public/storage` (الصور/المرفقات). |
| `php artisan filament:assets` | نشر أصول Filament (CSS/JS). |
| `php artisan optimize` | بناء كاش الإعداد/المسارات/العروض. |
| `php artisan serve` | خادم التطوير المدمج. |

> 🏭 في الإنتاج استخدم **Apache/Nginx + PHP-FPM** أو خادم Railway، وليس `php artisan serve` (أحادي الخيط).

---

## 4. النشر على Railway (الطريقة المعتمدة)

النظام منشور على **Railway** باستخدام **Nixpacks**. الملفات الثلاثة التي تحكم النشر:

### 4.1 `railway.toml`
```toml
[build]
builder = "NIXPACKS"

[deploy]
startCommand = "/bin/bash railway-start.sh"
```

### 4.2 `nixpacks.toml`
- **setup:** يثبّت `php82` + إضافات `intl/zip/gd` + `composer` (لأن تعريف `nixPkgs` يستبدل الافتراضي).
- **install:** `composer install --no-dev --optimize-autoloader --ignore-platform-reqs`.
- **build:** لا بناء واجهة أمامية (Filament مُجمَّع مسبقًا) — يتجاوز npm.

### 4.3 `railway-start.sh` (سكربت الإقلاع المرن)
منطق الإقلاع خطوة بخطوة:
1. **انتظار قاعدة البيانات:** حتى `DB_WAIT_ATTEMPTS` محاولة (فحص اتصال PDO فعلي) مع `sleep`.
2. عند الجاهزية: `migrate --force` (أو `migrate:fresh` إذا `DB_FRESH=1`) ثم `db:seed --class=ProductionSeeder --force`.
3. إذا لم تجهز القاعدة: يبدأ التطبيق **بدون** ترحيل (يبقى Online بدل إسقاط الحاوية).
4. **دائمًا:** `storage:link` + `filament:assets` + `config:cache` + `route:cache` + `view:cache`.
5. `exec php artisan serve --host=0.0.0.0 --port=$PORT`.

### 4.4 خطوات النشر
1. اربط مستودع GitHub بمشروع Railway.
2. أضِف خدمة **MySQL** في Railway وانسخ متغيّراتها.
3. اضبط متغيّرات البيئة (القسم 2) في إعدادات الخدمة.
4. ادفع إلى فرع `main` → Railway يبني وينشر تلقائيًا.
5. تابع سجلّات الإقلاع (`==> [startup] ...`) للتأكد من الترحيل والبذر.

---

## 5. HTTPS وروابط الأصول

خلف وكيل Railway (TLS إنهاء عند الحافة)، تُمرَّر الطلبات كـ HTTP داخليًا. لذلك:
- **`app/Http/Middleware/TrustProxies.php`** يضبط `$proxies = '*'` حتى تولّد Laravel روابط `https://` صحيحة للأصول (وإلا تُحجب لمحتوى مختلط).
- `filament:assets` في الإقلاع يضمن مطابقة الأصول لإصدار الحزمة.

> إذا ظهرت الواجهة **بلا تنسيق**: تحقّق من `APP_URL=https://...`، ونفّذ `filament:assets` + `storage:link`، وأعد النشر.

---

## 6. أول نشر / إعادة تعيين لمرة واحدة

- **قاعدة نظيفة أول مرة:** اضبط `DB_FRESH=1` لنشرة واحدة ثم **أزِله فورًا** (وإلا سيمسح البيانات في كل نشر).
- بعد ذلك يعمل `migrate --force` بشكل تراكمي و`ProductionSeeder` بشكل idempotent — إعادة النشر **لا تمسح** تعديلات المستخدمين.

---

## 7. ما بعد النشر — قائمة تحقّق

- [ ] فتح `/admin` وتسجيل الدخول بالأدمن.
- [ ] ظهور الواجهة **بتنسيق كامل** (CSS/JS).
- [ ] ظهور المستخدمين الـ7 والمشروع والمهام (من البذرة).
- [ ] رفع صورة في الملف الشخصي يعمل (تأكيد `storage:link`).
- [ ] الطباعة/التصدير في التقارير تعمل.
- [ ] الشعار الكبير يظهر في صفحة الدخول.

---

## 8. النسخ الاحتياطي والصيانة

```bash
# نسخة احتياطية لقاعدة البيانات
mysqldump -u<user> -p <db> > backup-$(date +%F).sql

# تنظيف/إعادة بناء الكاش بعد تغييرات
php artisan optimize:clear && php artisan optimize
```

- **الرفوعات** في `storage/app/public` — أدرجها في النسخ الاحتياطي.
- **السجلّات** في `storage/logs/laravel.log`.

---

<div align="center"><sub>© Quantum Dev Team — CODE BEYOND BOUNDARIES</sub></div>
