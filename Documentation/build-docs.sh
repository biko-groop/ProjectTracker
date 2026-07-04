#!/usr/bin/env bash
#
# build-docs.sh — إنتاج نسخ Word (.docx) و PDF أصلية من ملفات Markdown عبر Pandoc.
# (اختياري — يتطلب تثبيت Pandoc، وللـ PDF محرّك LaTeX مثل TeX Live/MiKTeX.)
#
# الاستخدام:
#   bash Documentation/build-docs.sh
#
# للنسخة التي لا تحتاج أدوات خارجية استخدم بدلًا منها:
#   php Documentation/build-html.php   (ثم اطبع كـ PDF أو افتح في Word)

set -e
cd "$(dirname "$0")"

OUT="output"
mkdir -p "$OUT"

FILES=(
  "00-Index.md"
  "01-System-Overview.md"
  "02-User-Manual.md"
  "03-Developer-Guide.md"
  "04-Database-Documentation.md"
  "05-API-Documentation.md"
  "06-Deployment-Guide.md"
  "07-System-Architecture.md"
  "08-Quantum-Dev-Team.md"
)

if ! command -v pandoc >/dev/null 2>&1; then
  echo "❌ Pandoc غير مثبّت. ثبّته من https://pandoc.org ثم أعد المحاولة."
  echo "   أو استخدم: php Documentation/build-html.php"
  exit 1
fi

echo "==> إنشاء Word موحّد..."
pandoc "${FILES[@]}" \
  --from gfm --to docx \
  --toc --toc-depth=2 \
  --metadata title="توثيق Qurtuba Project Tracker" \
  --resource-path=.:assets \
  -o "$OUT/Qurtuba-Project-Tracker-Documentation.docx"
echo "   ✅ $OUT/Qurtuba-Project-Tracker-Documentation.docx"

echo "==> محاولة إنشاء PDF (يتطلب محرّك LaTeX)..."
if pandoc "${FILES[@]}" \
  --from gfm --to pdf \
  --toc --toc-depth=2 \
  -V mainfont="Cairo" -V dir=rtl -V lang=ar \
  --pdf-engine=xelatex \
  --resource-path=.:assets \
  -o "$OUT/Qurtuba-Project-Tracker-Documentation.pdf" 2>/dev/null; then
  echo "   ✅ $OUT/Qurtuba-Project-Tracker-Documentation.pdf"
else
  echo "   ⚠️ تعذّر إنشاء PDF (محرّك LaTeX/الخط غير متوفّر)."
  echo "      البديل: افتح ملف build-html.php المُولّد واطبعه كـ PDF من المتصفح."
fi

echo "تم."
