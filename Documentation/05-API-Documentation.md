<div align="center">
<img src="assets/quantum-logo.png" alt="Quantum Dev Team" width="180" />

# 05 — توثيق واجهات البرمجة (API)
### Qurtuba Project Tracker · API / Endpoints
<sub>الإصدار 1.0.0 · 2026-07-05 · Quantum Dev Team</sub>
</div>

---

## 1. ملخّص الحالة

النظام **تطبيق لوحة تحكم (Server-rendered)** مبني على Filament + Livewire. **لا توجد حاليًا واجهة REST API عامة** موجّهة لمستهلكين خارجيين. التفاعل يتم عبر:

1. **لوحة Filament** على `/admin/*` — عبر جلسة مصادَقة (Livewire يتبادل الحالة داخليًا).
2. **مساري تقارير HTTP** عامّين (يتطلبان جلسة مسجّلة): الطباعة والتصدير.

- `routes/api.php`: لا مسارات مخصّصة (الملف الافتراضي).
- `laravel/sanctum` **مُثبّت** (يوفّر أساسًا جاهزًا لبناء API موثّق برموز عند الحاجة).

هذا المستند يوثّق **المسارات الفعلية المتاحة** بدقّة، ثم يشرح كيفية إضافة API رسمي.

---

## 2. مسارات الويب (routes/web.php)

| Method | URL | الاسم | المصادقة | الوصف |
|---|---|---|---|---|
| GET | `/` | — | لا | تحويل دائم إلى `/admin`. |
| GET | `/admin` وما تحته | (Filament) | جلسة | كامل لوحة التحكم. |
| GET | `/reports/print` | `reports.print` | جلسة | عرض تقرير قابل للطباعة (HTML). |
| GET | `/reports/export` | `reports.export` | جلسة | تنزيل تقرير Excel (`.xls`). |

---

## 3. Endpoint: طباعة تقرير

### `GET /reports/print`

يعرض تقريرًا بصيغة HTML مهيّأة للطباعة/التحويل إلى PDF.

**Headers**
| Header | القيمة |
|---|---|
| Cookie | جلسة Laravel مصادَقة (إلزامي) |

**Query Parameters**
| المعامل | النوع | إلزامي | القيم | الوصف |
|---|---|---|---|---|
| `type` | string | لا | `projects` (افتراضي) · `tasks` · `workload` · `delays` · `departments` | نوع التقرير |
| `scope` | string | لا | `all` · `mine` | النطاق (للمهام/التأخير؛ يُفرض `mine` للمستخدم العادي) |
| `department` | integer | لا | معرّف قسم | فلترة بالقسم (رئيسي أو معني) |
| `status` | string | لا | `pending`/`in_progress`/`completed`/`cancelled` | فلترة بالحالة (تقرير المهام) |

**Response**
- `200 OK` — `Content-Type: text/html; charset=UTF-8` (صفحة التقرير).

**Status Codes**
| الرمز | المعنى |
|---|---|
| 200 | نجاح |
| 302 | إعادة توجيه لصفحة الدخول (غير مصادَق) |
| 403 | ممنوع (فشل `guard()` — غير مسجّل) |

**مثال**
```
GET /reports/print?type=tasks&department=3&status=completed
Cookie: laravel_session=...
```

---

## 4. Endpoint: تصدير تقرير (Excel)

### `GET /reports/export`

يعيد ملف Excel (HTML بترويسة `application/vnd.ms-excel`).

**Query Parameters:** نفس معاملات `/reports/print` تمامًا.

**Response**
- `200 OK`
- `Content-Type: application/vnd.ms-excel; charset=UTF-8`
- `Content-Disposition: attachment; filename="<type>-<YmdHis>.xls"`

**مثال**
```
GET /reports/export?type=workload
Cookie: laravel_session=...

→ 200 OK  (تنزيل: workload-20260705_101500.xls)
```

**ملاحظة أمنية:** كلا المسارين يستدعيان `guard()` الذي يتطلب `auth()->check()`؛ والمستخدم العادي (`role=user`) يُقيَّد تلقائيًا بنوع `tasks` ونطاق `mine` بغضّ النظر عن المعاملات المرسلة.

---

## 5. المصادقة

- **آلية:** جلسة Laravel قائمة على ملفات تعريف الارتباط (Cookies) عبر تسجيل الدخول في Filament (`/admin/login`).
- لا توجد رموز API (Bearer/Token) مُفعّلة للمسارات الحالية.
- CSRF مطبّق على الطلبات غير الآمنة داخل اللوحة (Livewire يتكفّل بذلك).

---

## 6. كيفية إضافة API رسمي (دليل مستقبلي)

Sanctum مُثبّت، فالبنية جاهزة:

```php
// routes/api.php
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [Api\TaskController::class, 'index']);
    Route::get('/projects', [Api\ProjectController::class, 'index']);
    // ...
});
```

خطوات مقترحة:
1. `php artisan make:controller Api/TaskController --api`.
2. إنشاء **API Resources** (`php artisan make:resource TaskResource`) لتنسيق الخرج JSON.
3. إصدار الرموز عبر `$user->createToken('name')->plainTextToken`.
4. حماية المسارات بـ `auth:sanctum` + Policies الحالية.
5. توثيق كل Endpoint هنا وفق القالب أدناه.

**قالب توثيق Endpoint**
```
### <METHOD> <URL>
الوصف: ...
Headers: Authorization: Bearer <token>
Parameters / Body: ...
Response (200): { ... }
Status Codes: 200 / 401 / 403 / 404 / 422
مثال: ...
```

---

<div align="center"><sub>© Quantum Dev Team — CODE BEYOND BOUNDARIES</sub></div>
