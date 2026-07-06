{{-- بطاقات المهام التفصيلية (كل الحقول) — منسّقة بألوان احترافية. تُستخدم في العرض والطباعة/PDF. --}}
@php $tasks = $report['tasks'] ?? []; @endphp

<style>
    .dt-card { border:1px solid #e2e8f0; border-radius:12px; margin:0 0 16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); page-break-inside:avoid; background:#fff; }
    .dt-head { display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:12px 14px; border-bottom:1px solid #eef2f7; background:#f8fafc; }
    .dt-num { width:26px; height:26px; flex:none; border-radius:50%; background:#4f46e5; color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; }
    .dt-title { font-weight:800; font-size:15px; color:#1e293b; flex:1; min-width:180px; }
    .dt-badge { color:#fff; font-size:11px; font-weight:700; padding:3px 10px; border-radius:99px; white-space:nowrap; }
    .dt-badge.outline { background:#fff !important; border:1px solid #dc2626; color:#dc2626; }
    .dt-progress-wrap { display:flex; align-items:center; gap:6px; min-width:120px; }
    .dt-progress { flex:1; height:8px; background:#e2e8f0; border-radius:99px; overflow:hidden; }
    .dt-progress > span { display:block; height:100%; border-radius:99px; }
    .dt-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:0; }
    .dt-cell { padding:8px 14px; border-top:1px solid #f1f5f9; border-inline-start:1px solid #f1f5f9; font-size:12.5px; }
    .dt-cell .lbl { color:#64748b; font-size:11px; display:block; margin-bottom:2px; }
    .dt-cell .val { color:#1e293b; font-weight:600; }
    .dt-long { padding:8px 14px; border-top:1px solid #f1f5f9; font-size:12.5px; }
    .dt-long .lbl { color:#64748b; font-size:11px; display:block; margin-bottom:2px; }
    .dt-long .val { color:#334155; line-height:1.7; white-space:pre-wrap; }
    @media (max-width:640px){ .dt-grid { grid-template-columns:1fr 1fr; } }
    @media print { .dt-grid { grid-template-columns:repeat(3,1fr); } .dt-card { box-shadow:none; } }
</style>

@forelse ($tasks as $i => $t)
    @php
        $progColor = $t['progress'] >= 100 ? '#16a34a' : ($t['progress'] >= 50 ? '#2563eb' : ($t['progress'] > 0 ? '#d97706' : '#94a3b8'));
    @endphp
    <div class="dt-card">
        <div class="dt-head">
            <span class="dt-num">{{ $i + 1 }}</span>
            <span class="dt-title">{{ $t['title'] }}</span>
            <span class="dt-badge" style="background:{{ $t['status_color'] }}">{{ $t['status'] }}</span>
            <span class="dt-badge" style="background:{{ $t['priority_color'] }}">{{ $t['priority'] }}</span>
            @if ($t['is_delayed'])
                <span class="dt-badge outline">متأخرة {{ $t['days_delayed'] }} يوم</span>
            @endif
            <span class="dt-progress-wrap">
                <span class="dt-progress"><span style="width:{{ $t['progress'] }}%;background:{{ $progColor }}"></span></span>
                <b style="font-size:11px;color:{{ $progColor }}">{{ $t['progress'] }}%</b>
            </span>
        </div>

        <div class="dt-grid">
            <div class="dt-cell"><span class="lbl">المشروع</span><span class="val">{{ $t['project'] }}</span></div>
            <div class="dt-cell"><span class="lbl">القسم الرئيسي</span><span class="val">{{ $t['department'] }}</span></div>
            <div class="dt-cell"><span class="lbl">المسؤول</span><span class="val">{{ $t['assigned'] }}</span></div>
            <div class="dt-cell"><span class="lbl">المُنشئ</span><span class="val">{{ $t['creator'] }}</span></div>
            <div class="dt-cell"><span class="lbl">تاريخ البداية</span><span class="val">{{ $t['start_date'] }}</span></div>
            <div class="dt-cell"><span class="lbl">تاريخ الاستحقاق</span><span class="val">{{ $t['due_date'] }}</span></div>
            <div class="dt-cell"><span class="lbl">ساعات تقديرية</span><span class="val">{{ $t['estimated_hours'] }}</span></div>
            <div class="dt-cell"><span class="lbl">ساعات فعلية</span><span class="val">{{ $t['actual_hours'] }}</span></div>
            <div class="dt-cell"><span class="lbl">تاريخ الإنشاء</span><span class="val">{{ $t['created_at'] }}</span></div>
        </div>

        <div class="dt-long"><span class="lbl">الأقسام المعنية</span><span class="val">{{ $t['depts'] }}</span></div>
        @if ($t['description'] !== '—')<div class="dt-long"><span class="lbl">الوصف</span><span class="val">{{ $t['description'] }}</span></div>@endif
        @if ($t['delay_reason'] !== '—' || $t['needs'] !== '—')
            <div class="dt-long" style="background:#fef2f2">
                <span class="lbl">التأخير</span>
                <span class="val">السبب: {{ $t['delay_reason'] }} — الاحتياجات: {{ $t['needs'] }}</span>
            </div>
        @endif
        @if ($t['obstacles'] !== '—')<div class="dt-long"><span class="lbl">العوائق</span><span class="val">{{ $t['obstacles'] }}</span></div>@endif
        @if ($t['risks'] !== '—')<div class="dt-long"><span class="lbl">المخاطر المحتملة</span><span class="val">{{ $t['risks'] }}</span></div>@endif
        @if ($t['notes'] !== '—')<div class="dt-long"><span class="lbl">ملاحظات</span><span class="val">{{ $t['notes'] }}</span></div>@endif
    </div>
@empty
    <div style="text-align:center;padding:30px;color:#94a3b8;">لا توجد مهام مطابقة</div>
@endforelse
