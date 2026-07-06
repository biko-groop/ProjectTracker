<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Services\ReportService;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'الإدارة والإعدادات';

    protected static ?string $navigationLabel = 'التقارير';

    protected static ?string $title = 'التقارير';

    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.reports';

    public string $type = 'projects';

    public string $scope = 'all';

    public ?string $department = null;

    public ?string $status = null;

    // متاحة لكل مستخدم مسجّل؛ المستخدم العادي يرى مهامه فقط
    public static function canAccess(): bool
    {
        return (bool) auth()->check();
    }

    public function mount(): void
    {
        // المستخدم العادي: تقرير المهام فقط ومقصور على مهامه
        if (! $this->isPrivileged()) {
            $this->type = 'tasks';
            $this->scope = 'mine';
        }
    }

    public function isPrivileged(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'manager']);
    }

    public function getTypes(): array
    {
        if (! $this->isPrivileged()) {
            return [
                'tasks' => ReportService::TYPES['tasks'],
                'tasks_detailed' => ReportService::TYPES['tasks_detailed'],
            ];
        }

        return ReportService::TYPES;
    }

    public function getDepartments(): array
    {
        return Department::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getStatuses(): array
    {
        return [
            'pending' => 'قيد الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
        ];
    }

    /** هل يدعم نوع التقرير الحالي الفلاتر؟ */
    public function supportsFilters(): bool
    {
        return in_array($this->type, ['tasks', 'tasks_detailed', 'delays']);
    }

    /** الفلاتر المطبّقة (تُمرّر للخدمة وروابط الطباعة/التصدير) */
    public function getFilters(): array
    {
        $user = auth()->user();
        $deptLabel = $this->department ? ($this->getDepartments()[$this->department] ?? null) : null;

        return [
            'scope' => $this->isPrivileged() ? $this->scope : 'mine',
            'user_id' => $user?->id,
            'user_department_id' => $user?->department_id,
            'department' => $this->department ?: null,
            'department_label' => $deptLabel,
            'status' => $this->status ?: null,
        ];
    }

    /** معاملات الروابط (طباعة/تصدير) */
    public function getLinkParams(): array
    {
        return array_filter([
            'type' => $this->type,
            'scope' => $this->isPrivileged() ? $this->scope : null,
            'department' => $this->department ?: null,
            'status' => $this->status ?: null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    public function clearFilters(): void
    {
        $this->department = null;
        $this->status = null;
        if ($this->isPrivileged()) {
            $this->scope = 'all';
        }
    }

    public function getReport(): array
    {
        return app(ReportService::class)->build($this->type, $this->getFilters());
    }
}
