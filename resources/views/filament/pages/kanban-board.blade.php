<x-filament-panels::page>
    <style>
        .kanban-grid { display:grid; grid-template-columns:repeat(4,minmax(220px,1fr)); gap:1rem; align-items:start; overflow-x:auto; }
        @media (max-width: 768px) {
            /* على الجوال: تكديس الأعمدة عمودياً بدل التمرير الأفقي */
            .kanban-grid { grid-template-columns:1fr !important; overflow-x:visible !important; }
        }
    </style>
    {{-- فلتر المشروع --}}
    <div style="max-width:24rem;margin-bottom:1rem;">
        <label style="display:block;font-weight:600;margin-bottom:.25rem;">المشروع</label>
        <select wire:model.live="projectId"
                style="width:100%;padding:.5rem .75rem;border:1px solid rgba(0,0,0,.15);border-radius:.5rem;background:var(--fi-color-white,#fff);">
            <option value="">كل المشاريع</option>
            @foreach ($this->projects as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    {{-- لوحة الأعمدة --}}
    <div wire:ignore.self class="kanban-grid">
        @foreach ($this->getBoard() as $key => $col)
            <div style="background:rgba(148,163,184,.12);border-radius:.75rem;padding:.75rem;min-height:200px;">
                <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;font-weight:700;">
                    <span style="width:.75rem;height:.75rem;border-radius:99px;background:{{ $col['color'] }};"></span>
                    {{ $col['label'] }}
                    <span style="margin-inline-start:auto;background:rgba(0,0,0,.08);border-radius:99px;padding:.05rem .5rem;font-size:.75rem;">
                        {{ $col['tasks']->count() }}
                    </span>
                </div>

                <div class="kanban-column" data-status="{{ $key }}"
                     x-data x-init="window.initKanban($el)"
                     style="display:flex;flex-direction:column;gap:.5rem;min-height:120px;">
                    @foreach ($col['tasks'] as $task)
                        <div class="kanban-card" data-id="{{ $task->id }}" wire:key="task-{{ $task->id }}"
                             style="background:var(--fi-color-white,#fff);border:1px solid rgba(0,0,0,.08);border-radius:.6rem;padding:.6rem .7rem;box-shadow:0 1px 2px rgba(0,0,0,.06);cursor:grab;">
                            <div style="font-weight:600;margin-bottom:.35rem;">{{ $task->title }}</div>
                            <div style="display:flex;flex-wrap:wrap;gap:.35rem;font-size:.72rem;color:#64748b;">
                                @if ($task->project)
                                    <span style="background:rgba(99,102,241,.12);color:#4f46e5;border-radius:99px;padding:.05rem .45rem;">{{ $task->project->name }}</span>
                                @endif
                                @php
                                    $pr = ['urgent'=>['عاجل','#ef4444'],'high'=>['عالٍ','#f59e0b'],'medium'=>['متوسط','#3b82f6'],'low'=>['منخفض','#94a3b8']][$task->priority] ?? ['—','#94a3b8'];
                                @endphp
                                <span style="background:{{ $pr[1] }}1f;color:{{ $pr[1] }};border-radius:99px;padding:.05rem .45rem;">{{ $pr[0] }}</span>
                                @if ($task->assignedUser)
                                    <span>👤 {{ $task->assignedUser->name }}</span>
                                @endif
                                @if ($task->due_date)
                                    <span @class(['']) style="{{ $task->is_delayed ? 'color:#ef4444;font-weight:600;' : '' }}">
                                        📅 {{ $task->due_date->format('Y-m-d') }}
                                    </span>
                                @endif
                            </div>

                            {{-- نقل سريع بديل للسحب --}}
                            <select onchange="@this.call('updateStatus', {{ $task->id }}, this.value)"
                                    style="margin-top:.45rem;width:100%;font-size:.72rem;padding:.2rem;border:1px solid rgba(0,0,0,.12);border-radius:.4rem;">
                                @foreach (\App\Filament\Pages\KanbanBoard::STATUSES as $sKey => $sMeta)
                                    <option value="{{ $sKey }}" @selected($sKey === $task->status)>{{ $sMeta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- SortableJS للسحب والإفلات --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        window.initKanban = function (el) {
            if (el.__sortable) return;
            el.__sortable = new Sortable(el, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function (evt) {
                    const id = evt.item.getAttribute('data-id');
                    const status = evt.to.getAttribute('data-status');
                    if (id && status) {
                        window.Livewire.find(
                            el.closest('[wire\\:id]').getAttribute('wire:id')
                        ).call('updateStatus', parseInt(id), status);
                    }
                },
            });
        };
    </script>
</x-filament-panels::page>
