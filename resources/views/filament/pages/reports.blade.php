<x-filament-panels::page>
    @php $report = $this->getReport(); @endphp

    @php $linkParams = $this->getLinkParams(); @endphp

    {{-- أدوات التحكم (تُخفى عند الطباعة) --}}
    <div class="no-print" style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
            @foreach ($this->getTypes() as $key => $label)
                <button wire:click="$set('type', '{{ $key }}')"
                        style="padding:.4rem .8rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.12);font-size:.85rem;cursor:pointer;
                               {{ $type === $key ? 'background:rgb(var(--primary-600));color:#fff;border-color:transparent;' : 'background:transparent;' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
        <div style="display:flex;gap:.4rem;">
            <a href="{{ route('reports.print', $linkParams) }}" target="_blank"
               style="padding:.45rem .9rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.12);cursor:pointer;text-decoration:none;color:inherit;background:#fff;">
                🖨️ طباعة / PDF
            </a>
            <a href="{{ route('reports.export', $linkParams) }}"
               style="padding:.45rem .9rem;border-radius:.5rem;border:0;cursor:pointer;text-decoration:none;color:#fff;background:#16a34a;">
                ⬇️ تصدير Excel
            </a>
        </div>
    </div>

    {{-- الفلاتر الذكية (لتقارير المهام/التأخير) --}}
    @if ($this->supportsFilters())
        <div class="no-print" style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;margin-bottom:1rem;padding:.85rem 1rem;background:rgba(99,102,241,.05);border:1px solid rgba(99,102,241,.12);border-radius:.75rem;">
            @if ($this->isPrivileged())
                <label style="display:flex;flex-direction:column;gap:.25rem;font-size:.78rem;font-weight:700;color:#475569;">
                    النطاق
                    <select wire:model.live="scope"
                            style="padding:.4rem .6rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.15);font-size:.85rem;min-width:170px;background:#fff;">
                        <option value="all">كل المهام</option>
                        <option value="mine">مهامي والمرتبطة بي</option>
                    </select>
                </label>
            @else
                <span style="font-size:.8rem;font-weight:700;color:rgb(var(--primary-700));background:rgba(99,102,241,.12);padding:.35rem .7rem;border-radius:99px;">
                    📌 يعرض التقرير مهامك والمرتبطة بك فقط
                </span>
            @endif

            <label style="display:flex;flex-direction:column;gap:.25rem;font-size:.78rem;font-weight:700;color:#475569;">
                القسم
                <select wire:model.live="department"
                        style="padding:.4rem .6rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.15);font-size:.85rem;min-width:170px;background:#fff;">
                    <option value="">كل الأقسام</option>
                    @foreach ($this->getDepartments() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </label>

            @if (in_array($type, ['tasks', 'tasks_detailed']))
                <label style="display:flex;flex-direction:column;gap:.25rem;font-size:.78rem;font-weight:700;color:#475569;">
                    الحالة
                    <select wire:model.live="status"
                            style="padding:.4rem .6rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.15);font-size:.85rem;min-width:150px;background:#fff;">
                        <option value="">كل الحالات</option>
                        @foreach ($this->getStatuses() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            <button wire:click="clearFilters"
                    style="padding:.45rem .8rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.15);background:#fff;font-size:.8rem;cursor:pointer;color:#475569;">
                ↺ مسح الفلاتر
            </button>
        </div>
    @endif

    {{-- منطقة التقرير (قابلة للطباعة) --}}
    <div id="print-area" style="background:var(--fi-color-white,#fff);border-radius:.75rem;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <div style="text-align:center;margin-bottom:1rem;">
            <h2 style="font-weight:800;font-size:1.3rem;margin:0;">{{ $report['title'] }}</h2>
            <div style="color:#64748b;font-size:.8rem;margin-top:.25rem;">
                تاريخ التقرير: {{ now()->format('Y-m-d H:i') }} — عدد السجلات: {{ count($report['rows']) }}
            </div>
        </div>

        @if (! empty($report['detailed']))
            @include('reports._detailed_cards', ['report' => $report])
        @else
        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;min-width:520px;">
            <thead>
                <tr>
                    @foreach ($report['headers'] as $h)
                        <th style="border:1px solid #e2e8f0;padding:.5rem;background:#f1f5f9;text-align:start;font-weight:700;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td style="border:1px solid #e2e8f0;padding:.5rem;">{{ $cell }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report['headers']) }}" style="border:1px solid #e2e8f0;padding:1.5rem;text-align:center;color:#94a3b8;">
                            لا توجد بيانات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @endif
    </div>

</x-filament-panels::page>
