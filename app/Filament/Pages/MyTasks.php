<?php

namespace App\Filament\Pages;

use App\Models\Task;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MyTasks extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'مهامي';

    protected static ?string $title = 'مهامي';

    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.my-tasks';

    public function getHeading(): string
    {
        return '';
    }

    /** المهام المرتبطة بالمستخدم: مُسندة إليه، أو أنشأها، أو تخص قسمه */
    public function getMyTasks(): Collection
    {
        $user = auth()->user();
        $deptId = $user->department_id;

        return Task::query()
            ->with(['project', 'department', 'assignedUser'])
            ->where(function ($q) use ($user, $deptId) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);

                if ($deptId) {
                    $q->orWhere('department_id', $deptId)
                        ->orWhereHas('departmentLinks', fn ($q2) => $q2->where('department_id', $deptId));
                }
            })
            ->orderByRaw("FIELD(status,'in_progress','pending','completed','cancelled')")
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->get();
    }
}
