<div align="center">

<img src="public/images/quantum-logo.png" alt="Quantum Dev Team" width="260" />

# نظام إدارة المشاريع — Qurtuba Project Tracker

**نظام مؤسسي لإدارة المشاريع والمهام والفرق والأقسام، مبني على Laravel 10 + Filament 3، بواجهة عربية كاملة (RTL).**

<sub>Developed by **Quantum Dev Team** — CODE BEYOND BOUNDARIES</sub>

</div>

---

## 📖 نظرة عامة

**Qurtuba Project Tracker** نظام ويب لإدارة دورة حياة المشاريع بالكامل: من إنشاء المشروع وتوزيع المهام على الأقسام والموظفين، مرورًا بمتابعة الإنجاز والتأخير والعوائق، وصولًا إلى التقارير القابلة للطباعة والتصدير. النظام يعمل بالكامل من خلال لوحة تحكم حديثة على المسار `/admin` (لا توجد واجهة أمامية منفصلة).

يخدم النظام حاليًا مشروع **«تشغيل مباني قرطبة»** التابع لمدارس غراس الأخلاق، لكنه مصمَّم ليكون **منتجًا عامًا** قابلًا لإعادة الاستخدام لأي مؤسسة (اسم النظام والشعار والألوان قابلة للتغيير من داخل النظام).

> 📚 **التوثيق الكامل** موجود في مجلد [`Documentation/`](Documentation/00-Index.md).

---

## ✨ المميزات

- **لوحة تحكم (Dashboard)** بمؤشرات أداء (KPIs) ورسوم بيانية (حالة المشاريع، حالة المهام، المهام حسب الأولوية، أحدث المهام).
- **إدارة المشاريع** الكاملة مع المدير، الأولوية، نسبة الإنجاز، التواريخ، الأقسام والفرق المشاركة، والملفات.
- **إدارة المهام** المتقدمة: الحالة، الأولوية، نسبة الإنجاز، المسؤول، القسم الرئيسي + **الأقسام المعنية**، الاعتماديات (Dependencies)، العوائق (Obstacles)، التعليقات، المرفقات، الملاحظات، وأسباب/احتياجات التأخير.
- **لوحة كانبان (Kanban)** لسحب المهام بين الحالات، و**تقويم (Calendar)** للمهام حسب تواريخ الاستحقاق.
- **الأقسام والفرق والمستخدمون** مع أدوار وصلاحيات.
- **صفحة «مهامي»** لعرض مهام المستخدم والمرتبطة به.
- **التقارير** (المشاريع، المهام، أداء الموظفين، التأخير، الأقسام) مع **فلاتر ذكية** (النطاق/القسم/الحالة) و**طباعة/PDF** و**تصدير Excel**.
- **الإشعارات** الفورية داخل النظام (جرس علوي + صفحة إشعارات).
- **سجل التدقيق (Audit Log)** لكل تغيير على المشاريع/المهام/المستخدمين/الأقسام.
- **الملف الشخصي** (صورة، بيانات تواصل، تغيير كلمة المرور).
- **إعدادات المظهر والهوية**: اسم النظام، الشعار، اللون الأساسي، ونمط السايد بار — كلها ديناميكية.
- **دعم عربي كامل (RTL)** وخط Cairo.

---

## 🧰 التقنيات المستخدمة

| الطبقة | التقنية |
|---|---|
| اللغة | PHP `^8.2` |
| الإطار | Laravel `^10.10` |
| لوحة التحكم | Filament `^3.0` (Livewire 3 + Alpine.js + Tailwind — أصول مُجمَّعة مسبقًا) |
| قاعدة البيانات | MySQL / MariaDB |
| المصادقة | Laravel session-based (عبر Filament) + Sanctum مُثبّت |
| النشر | Railway (Nixpacks) |
| الخط | Cairo (RTL) |

> ملاحظة: النظام **لا يستخدم Vite/Node في وقت التشغيل** — Filament يشحن أصولًا مُجمَّعة مسبقًا. حزم Node موجودة للتطوير الاختياري فقط.

---

## ⚙️ متطلبات التشغيل

- PHP **8.2+** مع الإضافات: `intl`, `zip`, `gd`, `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `ctype`, `fileinfo`, `tokenizer`, `xml`.
- Composer 2.
- MySQL 8 / MariaDB 10.4+.
- (اختياري للتطوير) Node.js 18+ و npm.

---

## 🚀 التثبيت السريع

```bash
# 1) تثبيت الاعتماديات
composer install

# 2) تجهيز البيئة
cp .env.example .env
php artisan key:generate

# 3) اضبط قاعدة البيانات في .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# 4) الترحيلات + البذور (يُنشئ الأدمن + البيانات التشغيلية بشكل idempotent)
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force

# 5) ربط التخزين العام (للصور والمرفقات)
php artisan storage:link

# 6) التشغيل
php artisan serve
```

ثم افتح `http://127.0.0.1:8000/admin`.

تفاصيل أوفى في [`INSTALLATION.md`](INSTALLATION.md) و[`Documentation/06-Deployment-Guide.md`](Documentation/06-Deployment-Guide.md).

---

## 🗂️ هيكل المشروع (مختصر)

```
app/
  Filament/         لوحة التحكم: Resources (7) + Pages (8) + Widgets (5)
  Models/           16 نموذجًا (User, Project, Task, Department, Team ...)
  Observers/        4 مراقبات (Audit + Task + TaskComment + TaskDepartment)
  Services/         NotificationService, ReportService
  Policies/         ProjectPolicy, TaskPolicy
  Http/             ReportController + Middleware
  Providers/        AppServiceProvider + Filament/AdminPanelProvider
database/
  migrations/       22 ترحيلًا (19 جدولًا تطبيقيًا)
  seeders/          ProductionSeeder (الإنتاج) + بذور محلية
Documentation/      هذا التوثيق
```

شرح تفصيلي في [`Documentation/03-Developer-Guide.md`](Documentation/03-Developer-Guide.md).

---

## 🔐 الأدوار والصلاحيات

| الدور | القدرات |
|---|---|
| `admin` | صلاحية كاملة على كل الوحدات + الإعدادات + سجل التدقيق. |
| `manager` | إدارة المشاريع والمهام والفرق والتقارير. |
| `user` | عرض/تعديل مهامه والمرتبطة به، تقرير مهامه الشخصي، ملفه الشخصي. |

التفاصيل في [`Documentation/01-System-Overview.md`](Documentation/01-System-Overview.md).

---

## 🗄️ قاعدة البيانات

19 جدولًا تطبيقيًا حول `users` و`projects` و`tasks`. مخطط العلاقات الكامل (ERD) وشرح كل جدول/عمود في [`Documentation/04-Database-Documentation.md`](Documentation/04-Database-Documentation.md).

---

## ☁️ النشر

النظام منشور على **Railway** عبر Nixpacks. سكربت الإقلاع `railway-start.sh` ينتظر جاهزية قاعدة البيانات، ثم يُرحّل ويبذر ويبني الكاش. التفاصيل في [`Documentation/06-Deployment-Guide.md`](Documentation/06-Deployment-Guide.md).

---

## 🤝 المساهمة

راجع [`CONTRIBUTING.md`](CONTRIBUTING.md) لأسلوب الكود، تسمية الفروع، رسائل الـ Commit، ومراجعة الأكواد.

---

## 📜 الترخيص والحقوق

© Quantum Dev Team — جميع حقوق التصميم والتطوير والبرمجة محفوظة. يُمنع إعادة الاستخدام أو التوزيع دون إذن الفريق.

---

## 📞 التواصل

**Quantum Dev Team** — فريق تطوير برمجي مستقل.
- صفحة الفريق داخل النظام: `/admin/dev-team`
- توثيق الفريق: [`Documentation/08-Quantum-Dev-Team.md`](Documentation/08-Quantum-Dev-Team.md)
