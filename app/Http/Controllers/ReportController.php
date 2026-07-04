<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function guard(): void
    {
        abort_unless(auth()->check(), 403);
    }

    private function isPrivileged(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'manager']);
    }

    /** نوع التقرير: المستخدم العادي مقصور على المهام */
    private function type(Request $request): string
    {
        return $this->isPrivileged() ? $request->query('type', 'projects') : 'tasks';
    }

    /** الفلاتر من الطلب مع فرض النطاق الشخصي للمستخدم العادي */
    private function filters(Request $request): array
    {
        $user = auth()->user();
        $deptId = $request->query('department') ?: null;
        $deptLabel = $deptId ? optional(Department::find($deptId))->name : null;

        return [
            'scope' => $this->isPrivileged() ? $request->query('scope', 'all') : 'mine',
            'user_id' => $user->id,
            'user_department_id' => $user->department_id,
            'department' => $deptId,
            'department_label' => $deptLabel,
            'status' => $request->query('status') ?: null,
        ];
    }

    public function print(Request $request, ReportService $service)
    {
        $this->guard();
        $report = $service->build($this->type($request), $this->filters($request));

        return view('reports.print', compact('report'));
    }

    public function export(Request $request, ReportService $service)
    {
        $this->guard();
        $type = $this->type($request);
        $report = $service->build($type, $this->filters($request));

        $html = view('reports.excel', compact('report'))->render();
        $filename = $type . '-' . now()->format('Ymd_His') . '.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
