# 📸 دليل لقطات الشاشة — Screenshots Guide

هذا المجلد يحتوي لقطات الشاشة المشار إليها في [دليل المستخدم](../02-User-Manual.md) و[نظرة عامة على النظام](../01-System-Overview.md).

> **لماذا ليست مُلتقطة تلقائيًا؟** التقاط اللقطات آليًا يتطلب تشغيل التطبيق مع أتمتة متصفح (Playwright/Puppeteer) وقاعدة بيانات مُهيّأة — وهي غير متوفّرة في بيئة توليد هذا التوثيق. أدناه دليل دقيق لالتقاطها يدويًا خلال دقائق، بأسماء ملفات مطابقة للمراجع في المستندات.

---

## طريقة الالتقاط

1. شغّل النظام محليًا: `php artisan serve` ثم افتح `http://127.0.0.1:8000/admin`.
2. سجّل الدخول بحساب **admin** (لرؤية كل الشاشات).
3. لكل شاشة أدناه: اذهب للمسار، والتقط الشاشة (Windows: `Win+Shift+S`)، واحفظها بالاسم المحدّد **في هذا المجلد** بصيغة PNG.
4. أعد توليد نسخة HTML: `php Documentation/build-html.php` لتضمين الصور.

---

## قائمة اللقطات المطلوبة

| اسم الملف | المسار | الوصف |
|---|---|---|
| `01-login.png` | `/admin/login` | صفحة تسجيل الدخول (الشعار الكبير). |
| `02-dashboard.png` | `/admin` | لوحة التحكم بالمؤشرات والرسوم. |
| `03-users-list.png` | `/admin/users` | قائمة المستخدمين. |
| `03b-user-form.png` | `/admin/users/create` | نموذج إضافة مستخدم. |
| `04-departments.png` | `/admin/departments` | قائمة الأقسام. |
| `05-projects-list.png` | `/admin/projects` | قائمة المشاريع. |
| `05b-project-form.png` | `/admin/projects/create` | نموذج مشروع. |
| `06-tasks-list.png` | `/admin/tasks` | قائمة المهام. |
| `06b-task-form.png` | `/admin/tasks/create` | نموذج مهمة (الأقسام المعنية). |
| `06c-task-view.png` | `/admin/tasks/1` | تفاصيل مهمة (Infolist + العلاقات). |
| `07-tasks-filters.png` | `/admin/tasks` | الفلاتر مفتوحة (الحالة/الأولوية/المشروع/القسم). |
| `08-kanban.png` | `/admin/kanban-board` | لوحة كانبان. |
| `08b-calendar.png` | `/admin/tasks-calendar` | تقويم المهام. |
| `09-reports.png` | `/admin/reports` | صفحة التقارير + الفلاتر. |
| `09b-report-print.png` | `/reports/print?type=tasks` | نسخة الطباعة. |
| `10-notifications.png` | `/admin/my-notifications` | الإشعارات. |
| `11-profile.png` | `/admin/profile` | الملف الشخصي. |
| `12-appearance.png` | `/admin/manage-appearance` | إعدادات المظهر والهوية. |
| `13-dev-team.png` | `/admin/dev-team` | صفحة فريق التطوير. |

---

## نصائح للجودة

- استخدم نافذة متصفح بعرض ≥ 1400px للّقطات الكاملة.
- التقط لقطة إضافية بعرض جوال (~390px) للشاشات المتوافقة مع الجوال (المهام، مهامي).
- تجنّب إظهار بيانات حسّاسة حقيقية إن كانت اللقطات ستُنشر خارجيًا.
