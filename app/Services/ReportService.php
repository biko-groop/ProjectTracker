<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * يبني بيانات التقارير (رؤوس + صفوف) جاهزة للعرض/الطباعة/التصدير.
 */
class ReportService
{
    public const TYPES = [
        'projects' => 'تقرير المشاريع',
        'tasks' => 'تقرير المهام',
        'workload' => 'تقرير أداء الموظفين',
        'delays' => 'تقرير التأخير',
        'departments' => 'تقرير الأقسام',
    ];

    private array $status = [
        'pending' => 'قيد الانتظار', 'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل', 'cancelled' => 'ملغى',
    ];

    private array $priority = [
        'low' => 'منخفض', 'medium' => 'متوسط', 'high' => 'عالٍ', 'urgent' => 'عاجل',
    ];

    public function build(string $type, array $filters = []): array
    {
        return match ($type) {
            'tasks' => $this->tasks($filters),
            'workload' => $this->workload(),
            'delays' => $this->delays($filters),
            'departments' => $this->departments(),
            default => $this->projects(),
        };
    }

    /**
     * يطبّق فلاتر النطاق (مهامي)، القسم (رئيسي أو معني)، والحالة على استعلام المهام.
     */
    private function applyTaskFilters($query, array $filters)
    {
        // النطاق: مهامي والمرتبطة بي
        if (($filters['scope'] ?? 'all') === 'mine' && ! empty($filters['user_id'])) {
            $uid = $filters['user_id'];
            $deptId = $filters['user_department_id'] ?? null;

            $query->where(function ($q) use ($uid, $deptId) {
                $q->where('assigned_to', $uid)->orWhere('created_by', $uid);
                if ($deptId) {
                    $q->orWhere('department_id', $deptId)
                        ->orWhereHas('departmentLinks', fn ($dl) => $dl->where('department_id', $deptId));
                }
            });
        }

        // القسم: رئيسي أو معني
        if (! empty($filters['department'])) {
            $did = $filters['department'];
            $query->where(function ($q) use ($did) {
                $q->where('department_id', $did)
                    ->orWhereHas('departmentLinks', fn ($dl) => $dl->where('department_id', $did));
            });
        }

        // الحالة
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * لاحقة توضّح الفلاتر المطبّقة في عنوان التقرير.
     */
    private function filterSuffix(array $filters): string
    {
        $parts = [];

        if (($filters['scope'] ?? 'all') === 'mine') {
            $parts[] = 'مهامي والمرتبطة بي';
        }
        if (! empty($filters['department_label'])) {
            $parts[] = 'القسم: ' . $filters['department_label'];
        }
        if (! empty($filters['status'])) {
            $parts[] = 'الحالة: ' . ($this->status[$filters['status']] ?? $filters['status']);
        }

        return $parts ? ' — ' . implode(' · ', $parts) : '';
    }

    private function projects(): array
    {
        $rows = Project::with('manager')->orderByDesc('created_at')->get()->map(fn (Project $p) => [
            $p->name,
            $this->status[$p->status] ?? $p->status,
            $this->priority[$p->priority] ?? $p->priority,
            ($p->progress ?? 0) . '%',
            optional($p->manager)->name ?? '—',
            optional($p->end_date)->format('Y-m-d') ?? '—',
            $p->is_delayed ? 'نعم' : 'لا',
        ])->toArray();

        return [
            'title' => 'تقرير المشاريع',
            'headers' => ['المشروع', 'الحالة', 'الأولوية', 'الإنجاز', 'مدير المشروع', 'تاريخ النهاية', 'متأخر؟'],
            'rows' => $rows,
        ];
    }

    private function tasks(array $filters = []): array
    {
        $query = Task::with(['project', 'assignedUser', 'department']);
        $this->applyTaskFilters($query, $filters);

        $rows = $query->orderByDesc('created_at')->get()->map(fn (Task $t) => [
            $t->title,
            optional($t->project)->name ?? '—',
            optional($t->department)->name ?? '—',
            $this->status[$t->status] ?? $t->status,
            $this->priority[$t->priority] ?? $t->priority,
            optional($t->assignedUser)->name ?? '—',
            ($t->progress ?? 0) . '%',
            optional($t->due_date)->format('Y-m-d') ?? '—',
            $t->is_delayed ? 'نعم' : 'لا',
        ])->toArray();

        return [
            'title' => 'تقرير المهام' . $this->filterSuffix($filters),
            'headers' => ['المهمة', 'المشروع', 'القسم', 'الحالة', 'الأولوية', 'المسؤول', 'الإنجاز', 'تاريخ النهاية', 'متأخر؟'],
            'rows' => $rows,
        ];
    }

    private function workload(): array
    {
        $today = Carbon::today();

        $rows = User::with('department')->get()->map(function (User $u) use ($today) {
            $base = Task::where('assigned_to', $u->id);

            $total = (clone $base)->count();
            $inProgress = (clone $base)->where('status', 'in_progress')->count();
            $completed = (clone $base)->where('status', 'completed')->count();
            $delayed = (clone $base)->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('due_date', '<', $today)->count();

            return [
                $u->name,
                optional($u->department)->name ?? '—',
                $u->job_title ?? '—',
                $total,
                $inProgress,
                $completed,
                $delayed,
            ];
        })->toArray();

        return [
            'title' => 'تقرير أداء الموظفين (عبء العمل)',
            'headers' => ['الموظف', 'القسم', 'المسمى الوظيفي', 'إجمالي المهام', 'قيد التنفيذ', 'مكتملة', 'متأخرة'],
            'rows' => $rows,
        ];
    }

    private function delays(array $filters = []): array
    {
        $today = Carbon::today();

        $query = Task::with(['project', 'assignedUser'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('due_date', '<', $today);

        // النطاق والقسم فقط (الحالة ثابتة: متأخرة)
        $this->applyTaskFilters($query, ['scope' => $filters['scope'] ?? 'all', 'user_id' => $filters['user_id'] ?? null, 'user_department_id' => $filters['user_department_id'] ?? null, 'department' => $filters['department'] ?? null]);

        $rows = $query->orderBy('due_date')
            ->get()
            ->map(fn (Task $t) => [
                $t->title,
                optional($t->project)->name ?? '—',
                optional($t->assignedUser)->name ?? '—',
                optional($t->due_date)->format('Y-m-d') ?? '—',
                $t->days_delayed,
                $t->delay_reason ?: 'لم يُسجّل',
            ])->toArray();

        return [
            'title' => 'تقرير التأخير' . $this->filterSuffix(['scope' => $filters['scope'] ?? 'all', 'department_label' => $filters['department_label'] ?? null]),
            'headers' => ['المهمة', 'المشروع', 'المسؤول', 'تاريخ النهاية', 'أيام التأخير', 'سبب التأخير'],
            'rows' => $rows,
        ];
    }

    private function departments(): array
    {
        $today = Carbon::today();

        $rows = Department::withCount('users')->get()->map(function (Department $d) use ($today) {
            $base = Task::where('department_id', $d->id);
            $total = (clone $base)->count();
            $completed = (clone $base)->where('status', 'completed')->count();
            $delayed = (clone $base)->whereNotIn('status', ['completed', 'cancelled'])
                ->whereDate('due_date', '<', $today)->count();

            return [
                $d->name,
                $d->users_count,
                $total,
                $completed,
                $delayed,
            ];
        })->toArray();

        return [
            'title' => 'تقرير الأقسام',
            'headers' => ['القسم', 'عدد الموظفين', 'إجمالي المهام', 'مكتملة', 'متأخرة'],
            'rows' => $rows,
        ];
    }
}
