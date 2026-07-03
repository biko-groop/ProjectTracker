<x-filament-panels::page>
    @php
        $uid = auth()->id();
        $uname = auth()->user()->name;
        $tasks = $this->getMyTasks();
        $today = \Illuminate\Support\Carbon::today();

        $statusLabels = ['pending' => 'قيد الانتظار', 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتمل', 'cancelled' => 'ملغى'];
        $statusColors = ['pending' => '#f59e0b', 'in_progress' => '#3b82f6', 'completed' => '#22c55e', 'cancelled' => '#ef4444'];
        $prLabels = ['low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالٍ', 'urgent' => 'عاجل'];
        $prColors = ['low' => '#94a3b8', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444'];

        $total = $tasks->count();
        $inProgress = $tasks->where('status', 'in_progress')->count();
        $completed = $tasks->where('status', 'completed')->count();
        $overdue = $tasks->filter(fn ($t) => $t->due_date && $t->due_date->lt($today) && ! in_array($t->status, ['completed', 'cancelled']))->count();
    @endphp

    <style>
        .mt-hero { background:linear-gradient(135deg, rgb(var(--primary-500)) 0%, rgb(var(--primary-700)) 60%, rgb(var(--primary-900)) 100%);
                   color:#fff; border-radius:1rem; padding:1.5rem; box-shadow:0 10px 30px rgba(0,0,0,.18); }
        .mt-hero h1 { margin:0; font-size:1.5rem; font-weight:800; }
        .mt-hero p { margin:.3rem 0 0; opacity:.9; }
        .mt-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:.75rem; margin-top:1rem; }
        .mt-stat { background:var(--fi-color-white,#fff); border:1px solid rgba(0,0,0,.06); border-radius:.85rem; padding:1rem; box-shadow:0 2px 8px rgba(0,0,0,.04); }
        .dark .mt-stat { background:rgba(255,255,255,.03); border-color:rgba(255,255,255,.08); }
        .mt-stat .num { font-size:1.8rem; font-weight:800; line-height:1; }
        .mt-stat .lbl { color:#64748b; font-size:.82rem; margin-top:.3rem; }
        .mt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:1rem; margin-top:1.25rem; }
        .mt-card { display:block; text-decoration:none; color:inherit; background:var(--fi-color-white,#fff);
                   border:1px solid rgba(0,0,0,.07); border-inline-start:5px solid var(--pc,#94a3b8);
                   border-radius:.85rem; padding:1rem; box-shadow:0 2px 8px rgba(0,0,0,.05); transition:transform .15s, box-shadow .15s; }
        .dark .mt-card { background:rgba(255,255,255,.03); border-color:rgba(255,255,255,.08); }
        .mt-card:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,.12); }
        .mt-badge { display:inline-flex; align-items:center; gap:.3rem; font-size:.72rem; font-weight:700; padding:.15rem .55rem; border-radius:99px; }
        .mt-title { font-weight:800; font-size:1.02rem; margin-bottom:.5rem; }
        .mt-meta { display:flex; flex-wrap:wrap; gap:.4rem; align-items:center; margin-bottom:.6rem; }
        .mt-bar { height:.5rem; background:rgba(148,163,184,.25); border-radius:99px; overflow:hidden; }
        .mt-bar > i { display:block; height:100%; border-radius:99px; }
        .mt-foot { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.4rem; margin-top:.6rem; font-size:.78rem; color:#64748b; }
        .mt-empty { text-align:center; padding:3rem 1rem; color:#94a3b8; }
        @media (max-width:640px){ .mt-hero h1{ font-size:1.25rem; } }
    </style>

    {{-- ترويسة --}}
    <div class="mt-hero">
        <h1>👋 مرحباً {{ $uname }}</h1>
        <p>هذه المهام المرتبطة بك: المُسندة إليك، أو التي أنشأتها، أو التي تخص قسمك.</p>

        <div class="mt-stats">
            <div class="mt-stat"><div class="num" style="color:rgb(var(--primary-600))">{{ $total }}</div><div class="lbl">إجمالي مهامي</div></div>
            <div class="mt-stat"><div class="num" style="color:#3b82f6">{{ $inProgress }}</div><div class="lbl">قيد التنفيذ</div></div>
            <div class="mt-stat"><div class="num" style="color:#22c55e">{{ $completed }}</div><div class="lbl">مكتملة</div></div>
            <div class="mt-stat"><div class="num" style="color:{{ $overdue ? '#ef4444' : '#22c55e' }}">{{ $overdue }}</div><div class="lbl">متأخرة</div></div>
        </div>
    </div>

    {{-- بطاقات المهام --}}
    @if ($tasks->isEmpty())
        <div class="mt-empty">
            <x-heroicon-o-clipboard-document-check style="width:3rem;height:3rem;display:inline;" />
            <div style="margin-top:.5rem;">لا توجد مهام مرتبطة بك حالياً 🎉</div>
        </div>
    @else
        <div class="mt-grid">
            @foreach ($tasks as $t)
                @php
                    $isOverdue = $t->due_date && $t->due_date->lt($today) && ! in_array($t->status, ['completed', 'cancelled']);
                    $reason = $t->assigned_to == $uid ? ['مُسندة إليك', '#16a34a']
                        : ($t->created_by == $uid ? ['أنشأتها', '#2563eb'] : ['تخص قسمك', '#7c3aed']);
                    $pc = $prColors[$t->priority] ?? '#94a3b8';
                    $sc = $statusColors[$t->status] ?? '#94a3b8';
                    $prog = (int) ($t->progress ?? 0);
                @endphp
                <a class="mt-card" style="--pc:{{ $pc }}" href="{{ \App\Filament\Resources\TaskResource::getUrl('view', ['record' => $t->id]) }}">
                    <div class="mt-title">{{ $t->title }}</div>

                    <div class="mt-meta">
                        <span class="mt-badge" style="background:{{ $sc }}22;color:{{ $sc }}">{{ $statusLabels[$t->status] ?? $t->status }}</span>
                        <span class="mt-badge" style="background:{{ $pc }}22;color:{{ $pc }}">{{ $prLabels[$t->priority] ?? $t->priority }}</span>
                        <span class="mt-badge" style="background:{{ $reason[1] }}1f;color:{{ $reason[1] }}">{{ $reason[0] }}</span>
                    </div>

                    @if ($t->project)
                        <div style="font-size:.8rem;color:#64748b;margin-bottom:.5rem;">📁 {{ $t->project->name }}</div>
                    @endif

                    <div class="mt-bar"><i style="width:{{ $prog }}%;background:{{ $sc }}"></i></div>
                    <div style="font-size:.72rem;color:#94a3b8;margin-top:.2rem;">الإنجاز: {{ $prog }}%</div>

                    <div class="mt-foot">
                        <span>{{ $t->assignedUser?->name ? '👤 '.$t->assignedUser->name : '👤 غير مُسندة' }}</span>
                        @if ($t->due_date)
                            <span style="{{ $isOverdue ? 'color:#ef4444;font-weight:700;' : '' }}">
                                📅 {{ $t->due_date->format('Y-m-d') }}{{ $isOverdue ? ' • متأخرة' : '' }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
