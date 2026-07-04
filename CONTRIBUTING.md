# دليل المساهمة — CONTRIBUTING

مرحبًا بك في تطوير **Qurtuba Project Tracker**. هذا الدليل يوحّد أسلوب العمل حتى يبقى الكود نظيفًا ومتسقًا ومفهومًا لأي مطوّر جديد.

---

## 1) أسلوب كتابة الكود

- **PSR-12** هو المعيار. استخدم **Laravel Pint** (مثبّت في `require-dev`):
  ```bash
  ./vendor/bin/pint            # إصلاح تلقائي
  ./vendor/bin/pint --test     # فحص دون تعديل
  ```
- **Type hints** على المعاملات والمُرجَعات دائمًا (`function foo(int $id): bool`).
- التعليقات بالعربية مسموحة ومُشجَّعة (النظام عربي)، لكن أسماء الرموز (المتغيرات/الدوال/الأصناف) بالإنجليزية.
- تجنّب المنطق الثقيل داخل الـ Blade؛ ضعه في الصفحة (Livewire/Filament Page) أو في Service.
- لا تكرّر منطق الأعمال — استخرجه إلى `app/Services` أو Observer.

## 2) قواعد التسمية

| العنصر | النمط | مثال |
|---|---|---|
| الأصناف (Classes) | `PascalCase` | `ReportService` |
| الدوال/المتغيرات | `camelCase` | `getFilters()` |
| جداول قاعدة البيانات | `snake_case` جمع | `task_departments` |
| الأعمدة | `snake_case` | `assigned_to` |
| مفاتيح الترجمة | `snake_case` | `due_date` |
| ملفات Blade | `kebab-case` | `kanban-board.blade.php` |

## 3) سير عمل Git (Git Workflow)

- الفرع الرئيسي: **`main`** (قابل للنشر دائمًا).
- أنشئ فرعًا لكل مهمة، لا تعمل على `main` مباشرة:
  ```bash
  git switch -c feature/task-filters
  ```

### تسمية الفروع (Branch Naming)

```
feature/<وصف-مختصر>     ميزة جديدة
fix/<وصف-مختصر>         إصلاح خطأ
docs/<وصف-مختصر>        توثيق
refactor/<وصف-مختصر>    إعادة هيكلة
chore/<وصف-مختصر>       صيانة/أدوات
```

### رسائل الـ Commit (Commit Messages)

اتبع نمط **Conventional Commits**:

```
<type>: <ملخص بصيغة الأمر، ≤ 72 حرفًا>

<جسم اختياري يشرح السبب لا الكيفية>

Co-Authored-By: ...
```

الأنواع: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `perf`, `style`.

أمثلة من هذا المشروع:
- `Add department filter to Tasks table (main + related departments)`
- `Persist Tasks table filters/search/sort in session`
- `Fix login logo overlap: remove fixed 2.5rem height on login page`

## 4) طلبات الدمج (Pull Requests)

كل PR يجب أن:
1. يركّز على تغيير منطقي واحد.
2. يمرّ فيه `php artisan test` و`./vendor/bin/pint --test`.
3. يصف **ماذا** و**لماذا** (لا الكيفية فقط).
4. يشمل لقطات شاشة عند تغيير الواجهة.
5. يحدّث `CHANGELOG.md` عند تغيّر سلوك مرئي للمستخدم.

## 5) مراجعة الأكواد (Code Review)

المراجع يتحقق من:
- صحة المنطق وتغطيته للحالات الحدّية.
- عدم كسر الصلاحيات (Policies) أو تسريب بيانات بين الأدوار.
- الأداء (استعلامات N+1 — استخدم `with()`).
- الاتساق مع الأنماط القائمة (Filament Resources/Pages).
- عدم إدخال أسرار في الكود أو الـ Git.

## 6) الاختبار (Testing)

- الإطار: **PHPUnit 10** عبر `php artisan test`.
- الاختبارات في `tests/Feature` و`tests/Unit`.
- الاختبارات الحالية:
  - `AdminSmokeTest` — تفتح كل صفحات اللوحة وتتأكد من الاستجابة 200.
  - `TaskLogicTest` — منطق المهام (التأخير، الاعتماديات...).
- **أضف اختبارًا** لكل إصلاح خطأ (يمنع عودته) ولكل منطق أعمال جديد.
- لا تشغّل الاختبارات على قاعدة الإنتاج — استخدم قاعدة منفصلة أو `RefreshDatabase`.

## 7) قبل الدفع (Pre-push Checklist)

```bash
./vendor/bin/pint --test
php artisan test
php artisan optimize:clear
```

راجع أيضًا [`Documentation/03-Developer-Guide.md`](Documentation/03-Developer-Guide.md) لمعرفة أماكن إضافة الميزات الجديدة.
