<html xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        td, th { border: 1px solid #cbd5e1; padding: 6px 10px; font-family: 'Cairo', Tahoma, Arial; font-size: 12px; mso-number-format:"\@"; }
        th { background-color: #4f46e5; color: #ffffff; font-weight: bold; text-align: center; }
        .title { background-color: #eef2ff; color: #4338ca; font-weight: bold; font-size: 15px; text-align: center; }
        .meta { background-color: #f8fafc; color: #64748b; font-size: 11px; text-align: center; }
        td { text-align: right; }
    </style>
</head>
<body>
    @php $cols = max(count($report['headers']), 1); @endphp
    <table dir="rtl" border="1">
        <tr><td class="title" colspan="{{ $cols }}">نظام إدارة المشاريع — {{ $report['title'] }}</td></tr>
        <tr><td class="meta" colspan="{{ $cols }}">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }} — عدد السجلات: {{ count($report['rows']) }}</td></tr>
        <tr>
            @foreach ($report['headers'] as $h)
                <th>{{ $h }}</th>
            @endforeach
        </tr>
        @if (! empty($report['detailed']))
            {{-- تفصيلي: كل الحقول مع تلوين ذكي للحالة/الأولوية/التأخير --}}
            @foreach ($report['tasks'] as $t)
                <tr>
                    <td>{{ $t['title'] }}</td>
                    <td>{{ $t['project'] }}</td>
                    <td>{{ $t['department'] }}</td>
                    <td>{{ $t['depts'] }}</td>
                    <td style="background:{{ $t['status_color'] }};color:#ffffff;font-weight:bold;text-align:center">{{ $t['status'] }}</td>
                    <td style="background:{{ $t['priority_color'] }};color:#ffffff;font-weight:bold;text-align:center">{{ $t['priority'] }}</td>
                    <td style="text-align:center">{{ $t['progress'] }}%</td>
                    <td>{{ $t['assigned'] }}</td>
                    <td>{{ $t['creator'] }}</td>
                    <td style="text-align:center">{{ $t['start_date'] }}</td>
                    <td style="text-align:center">{{ $t['due_date'] }}</td>
                    <td style="text-align:center;{{ $t['is_delayed'] ? 'background:#fee2e2;color:#dc2626;font-weight:bold' : 'color:#16a34a' }}">{{ $t['is_delayed'] ? 'نعم' : 'لا' }}</td>
                    <td style="text-align:center">{{ $t['is_delayed'] ? $t['days_delayed'] : 0 }}</td>
                    <td style="text-align:center">{{ $t['estimated_hours'] }}</td>
                    <td style="text-align:center">{{ $t['actual_hours'] }}</td>
                    <td>{{ $t['delay_reason'] }}</td>
                    <td>{{ $t['needs'] }}</td>
                    <td>{{ $t['obstacles'] }}</td>
                    <td>{{ $t['risks'] }}</td>
                    <td>{{ $t['notes'] }}</td>
                    <td>{{ $t['description'] }}</td>
                    <td style="text-align:center">{{ $t['created_at'] }}</td>
                </tr>
            @endforeach
        @else
            @foreach ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endif
    </table>
</body>
</html>
