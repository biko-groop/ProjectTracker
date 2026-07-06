<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report['title'] }}</title>
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Cairo', 'Tahoma', sans-serif;
            margin: 0; padding: 24px; color: #1f2937; background: #fff;
        }
        .report-header {
            text-align: center; margin-bottom: 20px;
            border-bottom: 3px solid #4f46e5; padding-bottom: 14px;
        }
        .report-header h1 { margin: 0 0 6px; font-size: 22px; color: #4f46e5; }
        .report-header .org { font-size: 14px; color: #6b7280; font-weight: 600; }
        .report-meta {
            display: flex; justify-content: space-between;
            font-size: 12px; color: #6b7280; margin-bottom: 14px;
        }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        thead th {
            background: #4f46e5; color: #fff; padding: 9px 8px;
            text-align: right; font-weight: 700; border: 1px solid #4338ca;
        }
        tbody td { padding: 8px; border: 1px solid #e5e7eb; text-align: right; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .empty { text-align: center; padding: 30px; color: #9ca3af; }
        .footer { margin-top: 24px; text-align: center; font-size: 11px; color: #9ca3af; }
        .print-btn {
            display: inline-block; margin: 0 auto 16px; padding: 8px 18px;
            background: #4f46e5; color: #fff; border: 0; border-radius: 8px;
            font-family: inherit; font-size: 14px; cursor: pointer;
        }
        @media print {
            .print-btn { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div style="text-align:center;">
        <button class="print-btn" onclick="window.print()">🖨️ طباعة</button>
    </div>

    <div class="report-header">
        <div class="org">نظام إدارة المشاريع</div>
        <h1>{{ $report['title'] }}</h1>
    </div>

    <div class="report-meta">
        <span>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</span>
        <span>عدد السجلات: {{ count($report['rows']) }}</span>
    </div>

    @if (! empty($report['detailed']))
        @include('reports._detailed_cards', ['report' => $report])
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($report['headers'] as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="empty" colspan="{{ count($report['headers']) }}">لا توجد بيانات</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">© {{ date('Y') }} نظام إدارة المشاريع — تقرير مُولَّد آلياً</div>

    <script>
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
    </script>
</body>
</html>
