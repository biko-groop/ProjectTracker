# 🖨️ إنتاج نسخ Word / PDF من التوثيق

مصدر التوثيق هو ملفات **Markdown** (`.md`) في هذا المجلد. أدناه ثلاث طرق لإخراج نسخ Word/PDF، مرتّبة من الأسهل (بلا أدوات) إلى الأكثر احترافية.

---

## الطريقة 1 — النسخة الموحّدة المُنسّقة (موصى بها · بلا أدوات خارجية)

تُنتج ملف HTML واحدًا بهوية Quantum (غلاف + فهرس تلقائي + ترويسة/تذييل + جداول ومخططات):

```bash
php Documentation/build-html.php
```

المخرج: `Documentation/output/Qurtuba-Project-Tracker-Documentation.html`

- **➜ PDF:** افتح الملف في المتصفح ← `Ctrl+P` ← الوجهة «حفظ كـ PDF». فعّل «الرسوم الخلفية» (Background graphics) لأفضل شكل، وعطّل ترويسة/تذييل المتصفح إن رغبت (لدينا ترويسة/تذييل خاصّان).
- **➜ Word:** افتح الملف بـ Microsoft Word ← «حفظ باسم» ← نوع `.docx`.

> يعتمد على مكتبة `league/commonmark` المضمّنة مع Laravel — لا تثبيت إضافي. تظهر مخططات Mermaid عند وجود اتصال إنترنت (تُحمّل مكتبة العرض من CDN).

---

## الطريقة 2 — Pandoc (نسخ .docx / .pdf أصلية)

إن رغبت بملفات Word/PDF أصلية (وليست عبر المتصفح):

1. ثبّت [Pandoc](https://pandoc.org/installing.html).
2. (للـ PDF فقط) ثبّت محرّك LaTeX: **MiKTeX** (Windows) أو **TeX Live**.
3. شغّل:
   - Windows: `powershell -ExecutionPolicy Bypass -File Documentation\build-docs.ps1`
   - Linux/macOS/Git-Bash: `bash Documentation/build-docs.sh`

المخرجات في `Documentation/output/`:
- `Qurtuba-Project-Tracker-Documentation.docx`
- `Qurtuba-Project-Tracker-Documentation.pdf` (إن توفّر محرّك LaTeX)

---

## الطريقة 3 — يدويًا لكل ملف

كل ملف `.md` قابل للفتح مباشرةً في:
- **VS Code** (معاينة Markdown + إضافات تصدير PDF).
- **Typora / Obsidian** (تصدير Word/PDF مدمج).
- **GitHub** (عرض منسّق مع مخططات Mermaid).

---

## ملاحظات التصميم

- الهوية البصرية (ألوان Indigo/Violet، خط Cairo، شعار Quantum) مطبّقة في `build-html.php`.
- لإضافة **لقطات الشاشة**: ضعها في `Documentation/screenshots/` بالأسماء الموضّحة في [`screenshots/README.md`](screenshots/README.md) ثم أعد تشغيل `build-html.php`.
- عند تحديث أي ملف `.md`، أعد توليد النسخة الموحّدة لتبقى متزامنة.
