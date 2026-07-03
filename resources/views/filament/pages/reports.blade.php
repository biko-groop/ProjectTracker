<x-filament-panels::page>
    @php $report = $this->getReport(); @endphp

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
            <a href="{{ route('reports.print', ['type' => $type]) }}" target="_blank"
               style="padding:.45rem .9rem;border-radius:.5rem;border:1px solid rgba(0,0,0,.12);cursor:pointer;text-decoration:none;color:inherit;background:#fff;">
                🖨️ طباعة / PDF
            </a>
            <a href="{{ route('reports.export', ['type' => $type]) }}"
               style="padding:.45rem .9rem;border-radius:.5rem;border:0;cursor:pointer;text-decoration:none;color:#fff;background:#16a34a;">
                ⬇️ تصدير Excel
            </a>
        </div>
    </div>

    {{-- منطقة التقرير (قابلة للطباعة) --}}
    <div id="print-area" style="background:var(--fi-color-white,#fff);border-radius:.75rem;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <div style="text-align:center;margin-bottom:1rem;">
            <h2 style="font-weight:800;font-size:1.3rem;margin:0;">{{ $report['title'] }}</h2>
            <div style="color:#64748b;font-size:.8rem;margin-top:.25rem;">
                تاريخ التقرير: {{ now()->format('Y-m-d H:i') }} — عدد السجلات: {{ count($report['rows']) }}
            </div>
        </div>

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
    </div>

</x-filament-panels::page>
