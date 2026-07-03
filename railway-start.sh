#!/usr/bin/env bash
#
# Railway startup script (resilient).
# ينتظر جاهزية قاعدة البيانات مع إعادة المحاولة، ولا يُسقط الحاوية إن لم تجهز:
#   - يعيد المحاولة عدّة مرات مع انتظار (sleep).
#   - عند أول اتصال ناجح: migrate (أو migrate:fresh عند DB_FRESH=1) ثم ProductionSeeder.
#   - إذا بقيت القاعدة غير متاحة: يبدأ التطبيق بدون migration (يبقى Online).
#   - الكاش وstorage:link والخادم تعمل في كل الأحوال.
#
# لا يُعدّل أي شيء في الكود/الـ Models/Controllers/Filament/Routes.

set -o pipefail

MAX_ATTEMPTS="${DB_WAIT_ATTEMPTS:-20}"
SLEEP_SECONDS="${DB_WAIT_SLEEP:-3}"
DB_READY=0

echo "==> [startup] في انتظار جاهزية قاعدة البيانات (حتى ${MAX_ATTEMPTS} محاولة)..."

for i in $(seq 1 "${MAX_ATTEMPTS}"); do
    if php -r '$h=getenv("DB_HOST")?:"127.0.0.1"; $p=getenv("DB_PORT")?:"3306"; try { new PDO("mysql:host=$h;port=$p", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [PDO::ATTR_TIMEOUT => 3]); } catch (Throwable $e) { exit(1); }' >/dev/null 2>&1; then
        DB_READY=1
        echo "==> [startup] قاعدة البيانات متاحة (المحاولة ${i})."
        break
    fi
    echo "==> [startup] القاعدة غير جاهزة (${i}/${MAX_ATTEMPTS})، إعادة المحاولة بعد ${SLEEP_SECONDS}ث..."
    sleep "${SLEEP_SECONDS}"
done

if [ "${DB_READY}" = "1" ]; then
    echo "==> [startup] تنفيذ الترحيلات والتهيئة."
    if [ "${DB_FRESH}" = "1" ]; then
        php artisan migrate:fresh --force || echo "==> [startup] migrate:fresh فشل (المتابعة دون إسقاط)."
    else
        php artisan migrate --force || echo "==> [startup] migrate فشل (المتابعة دون إسقاط)."
    fi
    php artisan db:seed --class=ProductionSeeder --force || echo "==> [startup] db:seed فشل (المتابعة)."
else
    echo "==> [startup] تحذير: القاعدة غير متاحة بعد ${MAX_ATTEMPTS} محاولة — تشغيل التطبيق بدون migration."
fi

# لا تحتاج قاعدة بيانات — تعمل دائماً:
php artisan storage:link --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "==> [startup] تشغيل خادم الويب على المنفذ ${PORT}."
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
