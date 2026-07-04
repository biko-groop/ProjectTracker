<div align="center">

<img src="assets/quantum-logo.png" alt="Quantum Dev Team" width="220" />

# 📚 توثيق نظام إدارة المشاريع
## Qurtuba Project Tracker — Documentation

**الإصدار:** 1.0.0 &nbsp;•&nbsp; **آخر تحديث:** 2026-07-05 &nbsp;•&nbsp; **الفريق:** Quantum Dev Team

</div>

---

## الغرض من هذا التوثيق

توثيق احترافي كامل يمكّن أي مستخدم أو مطوّر جديد من **فهم** النظام و**تشغيله** و**صيانته** و**تطويره** دون الرجوع إلى المطوّر الأصلي. جميع المحتويات مُستخرَجة من الكود الفعلي للمشروع وتعكس حالته الحقيقية.

---

## الفهرس

| # | المستند | الوصف | الجمهور |
|---|---------|-------|---------|
| 01 | [نظرة عامة على النظام](01-System-Overview.md) | الفكرة، الأهداف، الوحدات، الشاشات، سير العمل، الأدوار. | الجميع |
| 02 | [دليل المستخدم](02-User-Manual.md) | خطوة بخطوة: الدخول، المشاريع، المهام، البحث، الفلاتر، التقارير، الإعدادات، الإشعارات. | المستخدم النهائي |
| 03 | [دليل المطوّر](03-Developer-Guide.md) | هيكل المشروع، الطبقات، النماذج، المراقبات، الخدمات، أماكن التطوير. | المطوّرون |
| 04 | [توثيق قاعدة البيانات](04-Database-Documentation.md) | كل الجداول والأعمدة والمفاتيح والعلاقات + مخطط ERD. | المطوّرون/DBA |
| 05 | [توثيق واجهات البرمجة (API)](05-API-Documentation.md) | المسارات المتاحة وحالة واجهات REST. | المطوّرون |
| 06 | [دليل النشر](06-Deployment-Guide.md) | التشغيل من الصفر + النشر على Railway + متغيرات البيئة. | DevOps |
| 07 | [معمارية النظام](07-System-Architecture.md) | تدفق البيانات عبر الطبقات + المخططات. | المطوّرون/المعماريون |
| 08 | [فريق التطوير — Quantum Dev Team](08-Quantum-Dev-Team.md) | الرؤية، الرسالة، الحقوق، الإصدار. | الجميع |

### ملفات جذر المشروع ذات الصلة
- [`README.md`](../README.md) — مدخل المشروع.
- [`CHANGELOG.md`](../CHANGELOG.md) — سجل الإصدارات.
- [`INSTALLATION.md`](../INSTALLATION.md) — تثبيت مختصر.
- [`CONTRIBUTING.md`](../CONTRIBUTING.md) — دليل المساهمة.

---

## إخراج نسخ Word / PDF

لا يعتمد هذا التوثيق على أدوات خارجية. لإنتاج نسخة **PDF/Word احترافية** بهوية Quantum (غلاف + فهرس + ترويسة/تذييل):

```bash
php Documentation/build-html.php
```

يُنتج ملفًا واحدًا: `Documentation/output/Qurtuba-Project-Tracker-Documentation.html`.

- **PDF:** افتح الملف في المتصفح ← اطبع (Ctrl+P) ← «حفظ كـ PDF». التنسيق مُهيّأ للطباعة (غلاف، ترويسة، تذييل بأرقام الصفحات).
- **Word:** افتح الملف بـ Microsoft Word ← «حفظ باسم» ← `.docx`.
- **بديل احترافي (اختياري):** إن توفّر [Pandoc](https://pandoc.org)، شغّل `Documentation/build-docs.ps1` (Windows) أو `build-docs.sh` لإنتاج `.docx` و`.pdf` أصليين.

راجع [`build-docs-README.md`](build-docs-README.md) للتفاصيل.

---

<div align="center">
<sub>© Quantum Dev Team — CODE BEYOND BOUNDARIES</sub>
</div>
