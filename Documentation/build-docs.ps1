# build-docs.ps1 — إنتاج نسخ Word (.docx) و PDF أصلية من Markdown عبر Pandoc (Windows).
# (اختياري — يتطلب Pandoc، وللـ PDF محرّك LaTeX مثل MiKTeX.)
#
# الاستخدام:
#   powershell -ExecutionPolicy Bypass -File Documentation\build-docs.ps1
#
# البديل بدون أدوات خارجية:
#   php Documentation\build-html.php

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

$out = "output"
New-Item -ItemType Directory -Force -Path $out | Out-Null

$files = @(
  "00-Index.md","01-System-Overview.md","02-User-Manual.md","03-Developer-Guide.md",
  "04-Database-Documentation.md","05-API-Documentation.md","06-Deployment-Guide.md",
  "07-System-Architecture.md","08-Quantum-Dev-Team.md"
)

if (-not (Get-Command pandoc -ErrorAction SilentlyContinue)) {
  Write-Host "❌ Pandoc غير مثبّت. ثبّته من https://pandoc.org ثم أعد المحاولة." -ForegroundColor Red
  Write-Host "   أو استخدم: php Documentation\build-html.php"
  exit 1
}

Write-Host "==> إنشاء Word موحّد..."
pandoc $files --from gfm --to docx --toc --toc-depth=2 `
  --metadata title="توثيق Qurtuba Project Tracker" `
  --resource-path=".;assets" `
  -o "$out\Qurtuba-Project-Tracker-Documentation.docx"
Write-Host "   ✅ $out\Qurtuba-Project-Tracker-Documentation.docx" -ForegroundColor Green

Write-Host "==> محاولة إنشاء PDF (يتطلب محرّك LaTeX)..."
try {
  pandoc $files --from gfm --to pdf --toc --toc-depth=2 `
    -V mainfont="Cairo" -V dir=rtl -V lang=ar --pdf-engine=xelatex `
    --resource-path=".;assets" `
    -o "$out\Qurtuba-Project-Tracker-Documentation.pdf"
  Write-Host "   ✅ $out\Qurtuba-Project-Tracker-Documentation.pdf" -ForegroundColor Green
} catch {
  Write-Host "   ⚠️ تعذّر إنشاء PDF (محرّك LaTeX/الخط غير متوفّر). استخدم build-html.php واطبع كـ PDF." -ForegroundColor Yellow
}

Write-Host "تم."
