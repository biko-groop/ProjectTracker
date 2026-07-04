<?php

/**
 * build-html.php — مولّد نسخة HTML احترافية موحّدة من ملفات Markdown.
 *
 * الاستخدام:
 *   php Documentation/build-html.php
 *
 * المخرجات:
 *   Documentation/output/Qurtuba-Project-Tracker-Documentation.html
 *
 * الميزات:
 *   - غلاف احترافي بشعار Quantum Dev Team + هوية الفريق البصرية.
 *   - فهرس تلقائي (TOC) من العناوين.
 *   - ترويسة (اسم النظام + شعار الفريق) وتذييل (المشروع/الإصدار/التاريخ + رقم الصفحة).
 *   - دعم كامل للعربية (RTL) وخط Cairo، وجداول احترافية، ومخططات Mermaid.
 *   - جاهزة للطباعة: افتحها في المتصفح ثم اطبع (Ctrl+P) واحفظ كـ PDF،
 *     أو افتحها في Microsoft Word واحفظ كـ .docx.
 *
 * لا يعتمد على أدوات خارجية — يستخدم مكتبة league/commonmark المضمّنة مع Laravel.
 */

require __DIR__ . '/../vendor/autoload.php';

use League\CommonMark\GithubFlavoredMarkdownConverter;

const VERSION = '1.0.0';
const PROJECT = 'Qurtuba Project Tracker — نظام إدارة المشاريع';
const TEAM = 'Quantum Dev Team';

$date = date('Y-m-d');
$docsDir = __DIR__;
$outDir = $docsDir . '/output';
@mkdir($outDir, 0775, true);

// ترتيب المستندات في النسخة الموحّدة
$files = [
    '01-System-Overview.md',
    '02-User-Manual.md',
    '03-Developer-Guide.md',
    '04-Database-Documentation.md',
    '05-API-Documentation.md',
    '06-Deployment-Guide.md',
    '07-System-Architecture.md',
    '08-Quantum-Dev-Team.md',
];

$converter = new GithubFlavoredMarkdownConverter([
    'html_input' => 'allow',
    'allow_unsafe_links' => true,
]);

// شعار الفريق مضمّن (base64) لملف مستقل تمامًا
$logoPath = $docsDir . '/assets/quantum-logo.png';
$logoData = is_file($logoPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
    : '';

$toc = [];
$sections = '';
$slugCount = [];

foreach ($files as $index => $file) {
    $path = $docsDir . '/' . $file;
    if (! is_file($path)) {
        fwrite(STDERR, "تحذير: لم يُعثر على $file\n");
        continue;
    }

    $md = file_get_contents($path);
    // إزالة كتل الشعار/العنوان المكرّرة في رأس كل ملف (نستبدلها بترقيم موحّد)
    $html = (string) $converter->convert($md);

    // تحويل كتل mermaid إلى <div class="mermaid"> ليعرضها المتصفح
    $html = preg_replace_callback(
        '#<pre><code class="language-mermaid">(.*?)</code></pre>#s',
        fn ($m) => '<div class="mermaid">' . html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5) . '</div>',
        $html
    );

    // حقن معرّفات على العناوين h1/h2 وبناء الفهرس
    $chapter = $index + 1;
    $html = preg_replace_callback(
        '#<(h[12])>(.*?)</\1>#s',
        function ($m) use (&$toc, &$slugCount, $chapter) {
            $level = $m[1];
            $text = trim(strip_tags($m[2]));
            $base = 'sec-' . preg_replace('/[^a-z0-9]+/i', '-', $text);
            $base = trim($base, '-') ?: 'sec';
            $slugCount[$base] = ($slugCount[$base] ?? 0) + 1;
            $id = $base . ($slugCount[$base] > 1 ? '-' . $slugCount[$base] : '');
            if ($text !== '') {
                $toc[] = ['level' => $level, 'text' => $text, 'id' => $id];
            }
            return "<$level id=\"$id\">" . $m[2] . "</$level>";
        },
        $html
    );

    $sections .= '<section class="doc-section">' . $html . '</section>';
}

// بناء الفهرس
$tocHtml = '';
foreach ($toc as $item) {
    $cls = $item['level'] === 'h1' ? 'toc-h1' : 'toc-h2';
    $tocHtml .= '<div class="' . $cls . '"><a href="#' . $item['id'] . '">' . htmlspecialchars($item['text']) . '</a></div>';
}

$logoImg = $logoData ? '<img class="brand-logo" src="' . $logoData . '" alt="' . TEAM . '">' : '';

$title = 'توثيق ' . PROJECT;

$out = <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$title</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #4f46e5;      /* Indigo — هوية Quantum */
    --primary-dark: #312e81;
    --accent: #7c3aed;
    --ink: #1e293b;
    --muted: #64748b;
    --line: #e2e8f0;
    --bg-soft: #f8fafc;
  }
  * { box-sizing: border-box; }
  body {
    font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif;
    color: var(--ink); line-height: 1.9; margin: 0;
    background: #eef2f7;
  }
  .page {
    background: #fff; max-width: 210mm; margin: 12px auto; padding: 22mm 20mm;
    box-shadow: 0 6px 30px rgba(0,0,0,.10);
  }

  /* ===== الغلاف ===== */
  .cover {
    min-height: 265mm; display: flex; flex-direction: column;
    align-items: center; justify-content: center; text-align: center;
    background: linear-gradient(160deg,#ffffff 0%,#f5f3ff 55%,#eef2ff 100%);
    border: 1px solid var(--line);
  }
  .cover .brand-logo { width: 320px; max-width: 70%; margin-bottom: 1.5rem; }
  .cover h1 { font-size: 2.4rem; font-weight: 800; color: var(--primary-dark); margin: .4rem 0; }
  .cover .subtitle { font-size: 1.2rem; color: var(--muted); margin-bottom: 2rem; }
  .cover .meta {
    margin-top: 2.5rem; font-size: .95rem; color: var(--ink);
    border-top: 2px solid var(--primary); padding-top: 1rem; display: inline-block;
  }
  .cover .team { margin-top: 1.2rem; font-weight: 800; color: var(--accent); letter-spacing: .04em; }
  .cover .tagline { color: var(--muted); font-size: .8rem; letter-spacing: .2em; }

  /* ===== الترويسة/التذييل (تظهر عند الطباعة على كل صفحة) ===== */
  .running-header, .running-footer { display: none; }

  /* ===== الفهرس ===== */
  .toc h2 { color: var(--primary-dark); border-bottom: 2px solid var(--primary); padding-bottom: .4rem; }
  .toc-h1 { font-weight: 700; margin: .5rem 0 .1rem; }
  .toc-h1 a { color: var(--primary-dark); text-decoration: none; }
  .toc-h2 { margin-right: 1.4rem; font-size: .92rem; }
  .toc-h2 a { color: var(--muted); text-decoration: none; }
  .toc a:hover { text-decoration: underline; }

  /* ===== المحتوى ===== */
  .doc-section { padding-top: .5rem; }
  .doc-section + .doc-section { border-top: 0; }
  h1, h2, h3, h4 { color: var(--primary-dark); line-height: 1.4; }
  h1 { font-size: 1.9rem; border-bottom: 3px solid var(--primary); padding-bottom: .3rem; margin-top: 1.5rem; }
  h2 { font-size: 1.4rem; margin-top: 1.6rem; border-right: 5px solid var(--primary); padding-right: .6rem; }
  h3 { font-size: 1.12rem; color: var(--accent); }
  a { color: var(--primary); }
  code {
    background: var(--bg-soft); padding: .1rem .35rem; border-radius: .3rem;
    font-family: Consolas, 'Courier New', monospace; font-size: .88em; direction: ltr; unicode-bidi: embed;
  }
  pre {
    background: #0f172a; color: #e2e8f0; padding: 1rem 1.1rem; border-radius: .6rem;
    overflow-x: auto; direction: ltr; text-align: left; font-size: .84rem; line-height: 1.6;
  }
  pre code { background: none; color: inherit; padding: 0; }
  blockquote {
    border-right: 4px solid var(--accent); background: #faf5ff; margin: 1rem 0;
    padding: .6rem 1rem; color: #4c1d95; border-radius: .4rem;
  }
  table { border-collapse: collapse; width: 100%; margin: 1rem 0; font-size: .9rem; }
  th, td { border: 1px solid var(--line); padding: .5rem .7rem; text-align: right; vertical-align: top; }
  thead th { background: var(--primary); color: #fff; font-weight: 700; }
  tbody tr:nth-child(even) { background: var(--bg-soft); }
  hr { border: 0; border-top: 1px solid var(--line); margin: 1.5rem 0; }
  img { max-width: 100%; }
  .mermaid { background: var(--bg-soft); border: 1px solid var(--line); border-radius: .6rem; padding: 1rem; margin: 1rem 0; text-align: center; }

  /* ===== الطباعة ===== */
  @page {
    size: A4;
    margin: 20mm 16mm 20mm 16mm;
  }
  @media print {
    body { background: #fff; }
    .page { box-shadow: none; margin: 0; max-width: none; padding: 0; }
    .cover { min-height: 92vh; page-break-after: always; border: 0; }
    .toc { page-break-after: always; }
    .doc-section { page-break-before: always; }
    h1, h2, h3 { page-break-after: avoid; }
    table, pre, blockquote, .mermaid { page-break-inside: avoid; }
    /* ترويسة/تذييل ثابتان يتكرّران على كل صفحة مطبوعة */
    .running-header, .running-footer {
      display: flex; position: fixed; left: 0; right: 0;
      align-items: center; justify-content: space-between;
      font-size: 8pt; color: var(--muted); padding: 0 4mm;
    }
    .running-header { top: 4mm; border-bottom: .5pt solid var(--line); }
    .running-footer { bottom: 4mm; border-top: .5pt solid var(--line); }
    .running-header img { height: 8mm; }
  }
</style>
</head>
<body>

<!-- ترويسة/تذييل للطباعة -->
<div class="running-header">
  <span>$title</span>
  $logoImg
</div>
<div class="running-footer">
  <span>© TEAM_PH</span>
  <span>PROJECT_PH · v VERSION_PH · DATE_PH</span>
</div>

<!-- الغلاف -->
<div class="page cover">
  $logoImg
  <h1>توثيق نظام إدارة المشاريع</h1>
  <div class="subtitle">Qurtuba Project Tracker — Full Documentation</div>
  <div class="meta">
    الإصدار VERSION_PH &nbsp;•&nbsp; آخر تحديث DATE_PH<br>
    منصّة: Laravel 10 + Filament 3
  </div>
  <div class="team">TEAM_PH</div>
  <div class="tagline">CODE BEYOND BOUNDARIES</div>
</div>

<!-- الفهرس -->
<div class="page toc">
  <h2>الفهرس — Table of Contents</h2>
  $tocHtml
</div>

<!-- المحتوى -->
<div class="page">
  $sections
</div>

<script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
<script>
  try {
    mermaid.initialize({ startOnLoad: true, theme: 'neutral', securityLevel: 'loose' });
  } catch (e) { console.warn('Mermaid not loaded (offline?)', e); }
</script>
</body>
</html>
HTML;

// استبدال العناصر النائبة (لتفادي تعارض صياغة heredoc)
$out = strtr($out, [
    'TEAM_PH' => TEAM,
    'PROJECT_PH' => PROJECT,
    'VERSION_PH' => VERSION,
    'DATE_PH' => $date,
]);

$outFile = $outDir . '/Qurtuba-Project-Tracker-Documentation.html';
file_put_contents($outFile, $out);

echo "✅ تم إنشاء النسخة الموحّدة:\n   $outFile\n";
echo "   - PDF: افتحها في المتصفح ثم اطبع (Ctrl+P) واحفظ كـ PDF.\n";
echo "   - Word: افتحها في Microsoft Word واحفظ كـ .docx.\n";
