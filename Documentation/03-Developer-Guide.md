<div align="center">
<img src="assets/quantum-logo.png" alt="Quantum Dev Team" width="180" />

# 03 — دليل المطوّر
### Qurtuba Project Tracker · Developer Guide
<sub>الإصدار 1.0.0 · 2026-07-05 · Quantum Dev Team</sub>
</div>

---

## 1. نظرة تقنية

| العنصر | القيمة |
|---|---|
| اللغة | PHP `^8.2` |
| الإطار | Laravel `^10.10` |
| لوحة التحكم | Filament `^3.0` (Livewire 3 + Alpine + Tailmind مُجمَّع) |
| المصادقة | Session (عبر Filament) + `laravel/sanctum ^3.3` مُثبّت |
| قاعدة البيانات | MySQL / MariaDB |
| الاختبار | PHPUnit `^10.1` + Pint |

**مبدأ معماري:** النظام كله **لوحة Filament واحدة** على `/admin`. لا توجد واجهة Blade أمامية (حُذفت). المنطق موزّع على: Filament Resources/Pages/Widgets ← Policies ← Models ← Observers ← Services ← Database.

---

## 2. هيكل المشروع الكامل

```
QurtubaProjectTracker/
├── app/
│   ├── Console/
│   │   └── Kernel.php                 جدولة الأوامر (لا مهام مجدوَلة حاليًا)
│   ├── Exceptions/Handler.php         معالجة الاستثناءات (افتراضي)
│   ├── Filament/                      ★ قلب النظام
│   │   ├── Resources/                 7 موارد (CRUD)
│   │   ├── Pages/                     8 صفحات مخصّصة
│   │   └── Widgets/                   5 ودجات للوحة التحكم
│   ├── Http/
│   │   ├── Controllers/               ReportController فقط (+ Controller الأساس)
│   │   └── Middleware/                وسائط (منها TrustProxies, Localization, RoleMiddleware)
│   ├── Models/                        16 نموذج Eloquent
│   ├── Observers/                     4 مراقبات
│   ├── Policies/                      ProjectPolicy, TaskPolicy
│   ├── Providers/                     مزوّدات الخدمة (+ Filament/AdminPanelProvider)
│   └── Services/                      NotificationService, ReportService
├── config/                           إعدادات Laravel/Filament
├── database/
│   ├── factories/                    UserFactory
│   ├── migrations/                   22 ترحيلًا
│   └── seeders/                       ProductionSeeder + بذور محلية
├── lang/ , resources/lang/ar.json    الترجمة العربية (المصدر الفعّال: resources/lang/ar.json)
├── resources/views/
│   ├── filament/                     Blade لصفحات/ودجات Filament + brand.blade.php
│   └── reports/                      print.blade.php, excel.blade.php
├── routes/
│   ├── web.php                       تحويل / → /admin + مساري التقارير
│   ├── api.php , console.php , channels.php
├── public/                           نقطة الدخول + public/images/quantum-logo.png
├── storage/app/public/               الرفوعات (الصور/المرفقات)
├── tests/                            Feature + Unit
├── Documentation/                    هذا التوثيق
├── nixpacks.toml , railway.toml , railway-start.sh   ملفات النشر
└── composer.json , package.json
```

---

## 3. شرح وظيفة كل مجلد/طبقة

### 3.1 `app/Filament/Resources/` — الموارد (CRUD)
كل مورد يمثّل نموذجًا ويولّد شاشات القائمة/الإنشاء/التعديل/العرض. الموارد السبعة:

| المورد | النموذج | مجموعة التنقّل | ملاحظات |
|---|---|---|---|
| `ProjectResource` | `Project` | إدارة المشاريع | Relation Managers: أعضاء/فرق/أقسام/مهام/ملفات |
| `TaskResource` | `Task` | إدارة المشاريع | Infolist للعرض + فلاتر مثبّتة بالجلسة + Relation Managers |
| `TeamResource` | `Team` | إدارة المشاريع | أعضاء + مشاريع |
| `UserResource` | `User` | الإدارة والإعدادات | أدوار/أقسام/تفعيل |
| `DepartmentResource` | `Department` | الإدارة والإعدادات | مدير + موظفون + مهام |
| `NotificationResource` | `Notification` | الإدارة والإعدادات | إدارة الإشعارات |
| `AuditLogResource` | `AuditLog` | الإدارة والإعدادات | للعرض فقط (سجل التدقيق) |

بنية المورد: `XResource.php` (النموذج، التنقّل، `form()`, `table()`, `infolist()`, `getRelations()`, `getPages()`) + مجلد `XResource/Pages/` (`ListX`, `CreateX`, `EditX`, `ViewX`) + `XResource/RelationManagers/`.

### 3.2 `app/Filament/Pages/` — الصفحات المخصّصة
صفحات ليست CRUD مباشرًا:

| الصفحة | الوصف | نقطة مهمة |
|---|---|---|
| `Reports` | التقارير + الفلاتر | يستدعي `ReportService`؛ يقيّد `user` بنطاق «مهامي» |
| `KanbanBoard` | كانبان | Blade مخصّص `kanban-board.blade.php` |
| `TasksCalendar` | تقويم المهام | — |
| `MyTasks` | مهام المستخدم والمرتبطة به | استعلام النطاق الشخصي |
| `MyNotifications` | إشعارات المستخدم | — |
| `Profile` | الملف الشخصي | رفع صورة + تغيير كلمة المرور بتحقّق `Hash::check` |
| `ManageAppearance` | الهوية | يكتب في `Setting::current()` |
| `DevTeam` | صفحة Quantum | Blade `dev-team.blade.php` |

### 3.3 `app/Filament/Widgets/` — الودجات
`StatsOverview`, `ProjectsStatusChart`, `TasksStatusChart`, `TasksByPriorityChart`, `LatestTasks` — تظهر في لوحة التحكم.

### 3.4 `app/Http/Controllers/`
**`ReportController`** فقط (خارج Filament) — يخدم الطباعة والتصدير:
- `print(Request)` → يبني التقرير عبر `ReportService` ويعرض `reports.print`.
- `export(Request)` → يعيد HTML بترويسة Excel (`application/vnd.ms-excel`).
- يفرض النطاق الشخصي للمستخدم العادي عبر `filters()` و`type()`.

### 3.5 `app/Http/Middleware/`
وسائط Laravel القياسية + المخصّصة:
- **`TrustProxies`** (`$proxies = '*'`) — أساسي لإصلاح روابط الأصول HTTPS خلف وكيل Railway.
- **`Localization`** — ضبط اللغة العربية.
- **`RoleMiddleware`**, **`AuthMiddleware`** — بوابات أدوار (متوفّرة؛ الحماية الفعلية للوحة عبر Policies و`canAccess`).

> Filament يسجّل وسائطه الخاصة في `AdminPanelProvider::middleware()` و`authMiddleware()`.

### 3.6 `app/Models/` — النماذج (16)
`User, Project, Task, Team, Department, Setting, Notification, AuditLog, TaskDepartment, TaskObstacle, TaskComment, TaskActivity, TaskFile, ProjectFile, ProjectMember, TeamMember`.

نقاط بارزة:
- `Task`: خصائص محسوبة `is_delayed`, `days_delayed`, `is_blocked`؛ علاقات `departmentLinks`, `dependencies`, `obstacles`, `comments`, `files`, `activities`.
- `Project`: `is_delayed`, `completion_rate`؛ علاقات `users`, `teams`, `departments`, `tasks`, `files`.
- `User`: يطبّق `FilamentUser, HasAvatar`؛ `canAccessPanel()` يتطلب `is_active`؛ `getSelectLabelAttribute()` للتسمية في القوائم.
- `Setting`: `current()` تُنشئ/تعيد صف الإعدادات الوحيد.
- `TaskDepartment`: ثابت `RESPONSIBILITIES`.

### 3.7 `app/Observers/` — المراقبات (منطق تلقائي)
| المراقب | يراقب | الوظيفة |
|---|---|---|
| `AuditObserver` | Project, Task, User, Department | كتابة سجل تدقيق على created/updated/deleted (يُخفي `password`/`remember_token`) |
| `TaskObserver` | Task | سجل زمني (`TaskActivity`) + إشعارات تلقائية عند الإنشاء/الإسناد/تغيير الحالة/الإكمال |
| `TaskCommentObserver` | TaskComment | منطق التعليقات (إشعار/سجل) |
| `TaskDepartmentObserver` | TaskDepartment | منطق الأقسام المعنية |

التسجيل في `AppServiceProvider::boot()`.

### 3.8 `app/Services/` — الخدمات
- **`NotificationService`**: `send()`, `sendMany()`, `notifyManagers()` — مصدر مركزي لإنشاء الإشعارات.
- **`ReportService`**: ثابت `TYPES` (5 تقارير) + `build($type, $filters)` + `applyTaskFilters()` (النطاق/القسم/الحالة) + `filterSuffix()`.

### 3.9 `app/Policies/`
`ProjectPolicy` و`TaskPolicy` — تُطبّق تلقائيًا في Filament. `admin/manager` صلاحيات واسعة؛ `user` مقيّد بمهامه.

### 3.10 `app/Providers/`
- `AppServiceProvider` — لغة Carbon العربية + تسجيل كل المراقبات.
- `AdminPanelProvider` (Filament) — **أهم مزوّد**: يقرأ `Setting` لبناء الهوية (اسم/شعار/لون/سايد بار)، يحدّد المسار `/admin`، الخط Cairo، مجموعات التنقّل، جرس الإشعارات، تنسيق صفحة الدخول، واكتشاف الموارد/الصفحات/الودجات.
- `EventServiceProvider` — يربط `Registered → SendEmailVerificationNotification` فقط (اكتشاف الأحداث معطّل).
- `AuthServiceProvider`, `RouteServiceProvider`, `BroadcastServiceProvider` — قياسية.

### 3.11 `database/`
- **migrations/** — 22 ترحيلًا (تفاصيلها في [توثيق قاعدة البيانات](04-Database-Documentation.md)).
- **seeders/** — `ProductionSeeder` (الإنتاج، idempotent) + `DatabaseSeeder` + بذور محلية (`RealUsersSeeder`, `QurtubaProjectSeeder` — **هدّامة، للتطوير فقط**).
- **factories/** — `UserFactory` فقط.

### 3.12 `resources/`
- `views/filament/` — Blade لصفحات/ودجات مخصّصة + `brand.blade.php` (شعار العلامة).
- `views/reports/` — `print.blade.php`, `excel.blade.php`.
- `lang/ar.json` — **مصدر الترجمة الفعّال** (ملف `lang/ar.json` القديم متجاهَل).

### 3.13 `routes/`
- `web.php` — `/` → `/admin`، و`/reports/print`، `/reports/export`.
- `api.php` — لا مسارات مخصّصة حاليًا (راجع [API](05-API-Documentation.md)).

---

## 4. مكوّنات Laravel: المستخدَم مقابل غير المستخدَم

يوضّح الجدول ما يستخدمه المشروع فعليًا (لتفادي البحث عن أشياء غير موجودة):

| المكوّن | الحالة | التفصيل |
|---|---|---|
| Controllers | ✅ محدود | `ReportController` فقط؛ بقية الـ CRUD عبر Filament. |
| Services | ✅ | `NotificationService`, `ReportService`. |
| Models | ✅ | 16 نموذجًا. |
| Middleware | ✅ | قياسية + `TrustProxies`, `Localization`, `RoleMiddleware`. |
| Policies | ✅ | `ProjectPolicy`, `TaskPolicy`. |
| Observers | ✅ | 4 مراقبات. |
| Seeders / Migrations / Factories | ✅ | كما أعلاه. |
| **Form Requests** | ⚠️ غير مستخدم | التحقّق داخل نماذج Filament (`form()`); لإضافته: `php artisan make:request`. |
| **Events / Listeners** | ⚠️ افتراضي فقط | لا أحداث مخصّصة؛ التفاعلات عبر Observers. |
| **Notifications (Laravel)** | ⚠️ غير مستخدم | يُستخدم نموذج `Notification` مخصّص + `NotificationService` بدلًا من قناة Laravel. |
| **Jobs / Queues** | ⚠️ غير مستخدم | لا مهام غير متزامنة؛ `QUEUE_CONNECTION=sync`. |
| **Console Commands** | ⚠️ لا مخصّص | `Console/Kernel` بلا جدولة؛ أضِف في `Console/Commands`. |
| **Repositories / Traits / Helpers** | ⚠️ غير مستخدم | المنطق في Models/Services؛ أضِفها عند الحاجة تحت `app/`. |

> عند الحاجة لأيٍّ من «غير المستخدَم»، الأماكن القياسية موضّحة أعلاه؛ هذا **قرار معماري مقصود** لإبقاء النظام بسيطًا حول Filament.

---

## 5. دورة البيانات داخل النظام (Data Lifecycle)

مثال: **إنشاء مهمة وإسنادها**
```
1. المستخدم يملأ نموذج المهمة في TaskResource::form()  (Livewire)
2. Filament يتحقّق من الحقول ثم يستدعي Task::create()
3. Eloquent يحفظ الصف في جدول tasks
4. تُطلق أحداث النموذج → تلتقطها المراقبات:
   ├─ TaskObserver::created()  → TaskActivity (سجل) + NotificationService::send() (إشعار للمسؤول)
   └─ AuditObserver::created() → AuditLog (تدقيق)
5. الأقسام المعنية تُحفظ في task_departments → TaskDepartmentObserver
6. عند التعديل: TaskObserver::updated() يرصد wasChanged('status'/'assigned_to') ويُشعر
```

مثال: **عرض تقرير مفلتَر**
```
Reports Page (Livewire) → getFilters() → ReportService::build($type,$filters)
   → applyTaskFilters() (scope/department/status) → استعلام Eloquent → صفوف
   → عرض في reports.blade / أو ReportController للطباعة/التصدير
```

---

## 6. أفضل الممارسات المتبعة

- **فصل الاهتمامات**: منطق الأعمال في Services/Observers لا في Blade.
- **Idempotency**: `ProductionSeeder` يستخدم `firstOrCreate` فيُعاد تشغيله بأمان.
- **الأمان أولًا**: Policies + `canAccess()` + إخفاء الحقول الحساسة في التدقيق + تقييد المستخدم العادي بنطاقه.
- **مصدر واحد للحقيقة للهوية**: جدول `settings` يقود المظهر بالكامل.
- **منع N+1**: استخدام `with()` في استعلامات التقارير والجداول.
- **التوطين**: كل النصوص عبر `resources/lang/ar.json` وثوابت عربية في الموارد.

---

## 7. أين تضيف ميزة جديدة؟

| تريد أن... | افعل |
|---|---|
| تضيف كيانًا جديدًا (CRUD) | `php artisan make:model X -m` ثم `make:filament-resource X` |
| تضيف صفحة غير CRUD | `php artisan make:filament-page X` (تظهر تلقائيًا عبر `discoverPages`) |
| تضيف ودجة للوحة | `make:filament-widget X` |
| تضيف منطقًا تلقائيًا عند حفظ نموذج | Observer جديد + سجّله في `AppServiceProvider::boot()` |
| تضيف نوع تقرير | أضف مفتاحًا في `ReportService::TYPES` ودالة بناء + فرع في `build()` |
| تضيف فلترًا لجدول | داخل `table()->filters([...])` في المورد |
| تضيف حقلًا لجدول موجود | ترحيل جديد `make:migration` + `$fillable` + النموذج + نموذج Filament |
| تغيّر الصلاحيات | عدّل Policy المعنية |
| تضيف إشعارًا | `NotificationService::send()/sendMany()/notifyManagers()` |

---

## 8. أين تعدّل الأكواد الحسّاسة؟

| للتعديل على... | الملف |
|---|---|
| هوية اللوحة/الألوان/الدخول | `app/Providers/Filament/AdminPanelProvider.php` |
| شعار العلامة | `resources/views/filament/brand.blade.php` |
| منطق الإشعارات | `app/Services/NotificationService.php` + `TaskObserver` |
| منطق التقارير والفلاتر | `app/Services/ReportService.php` + `app/Filament/Pages/Reports.php` |
| منطق التأخير/الحجب | خصائص `Task` المحسوبة |
| الترجمة العربية | `resources/lang/ar.json` |
| بذرة الإنتاج | `database/seeders/ProductionSeeder.php` |
| النشر | `railway-start.sh`, `nixpacks.toml`, `railway.toml` |

---

## 9. تشغيل بيئة التطوير والاختبار

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed          # أو --class=ProductionSeeder
php artisan storage:link
php artisan serve

# الجودة
./vendor/bin/pint --test            # فحص الأسلوب
php artisan test                    # الاختبارات (AdminSmokeTest, TaskLogicTest)
```

> ⚠️ لا تشغّل الاختبارات/الترحيلات على قاعدة الإنتاج. استخدم قاعدة منفصلة (`DB_DATABASE`) أو `RefreshDatabase`.

---

## 10. توصيات (Recommendations)

اكتُشفت أثناء التوثيق؛ لتحسين النظام مستقبلًا:

1. **Form Requests / التحقّق المركزي**: نقل قواعد التحقّق المعقّدة من نماذج Filament إلى طبقة قابلة لإعادة الاستخدام/الاختبار.
2. **طابور غير متزامن للإشعارات**: تحويل `QUEUE_CONNECTION` إلى `database`/`redis` وإرسال الإشعارات عبر Jobs عند نمو الحجم.
3. **مهام مجدولة**: تفعيل Scheduler لتنبيهات التأخير اليومية (مثلًا `notifyManagers` بالمهام المتأخرة).
4. **توحيد وسائط الأدوار**: `RoleMiddleware`/`AuthMiddleware` موجودة لكن الحماية الفعلية عبر Policies — يُفضّل توثيق أو إزالة غير المستخدَم لتقليل الالتباس.
5. **تغطية اختبارية أوسع**: إضافة اختبارات وحدة لـ `ReportService::applyTaskFilters` و`Task::is_delayed/is_blocked`.
6. **API رسمي**: عند الحاجة لتكامل خارجي، بناء طبقة API عبر Sanctum (مُثبّت أصلًا) — راجع [05-API](05-API-Documentation.md).
7. **حذف البذور الهدّامة من الإنتاج**: `RealUsersSeeder`/`QurtubaProjectSeeder` تقطع الجداول؛ الاعتماد على `ProductionSeeder` فقط في الإنتاج.
8. **فهرسة الأداء**: مراجعة فهارس الأعمدة كثيرة الفلترة (`tasks.status`, `tasks.department_id`, `tasks.due_date`).

---

<div align="center"><sub>© Quantum Dev Team — CODE BEYOND BOUNDARIES</sub></div>
