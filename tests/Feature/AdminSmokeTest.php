<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    public function test_admin_panel_pages_load(): void
    {
        $user = User::where('email', 'admin@qurtuba.test')->first();
        $this->assertNotNull($user, 'Admin user not found in DB');

        $firstTaskId = \App\Models\Task::value('id');
        $firstProjectId = \App\Models\Project::value('id');

        $urls = [
            '/admin',
            '/admin/projects',
            '/admin/projects/create',
            "/admin/projects/{$firstProjectId}/edit",
            '/admin/tasks',
            '/admin/tasks/create',
            "/admin/tasks/{$firstTaskId}",
            "/admin/tasks/{$firstTaskId}/edit",
            '/admin/teams',
            '/admin/teams/create',
            '/admin/users',
            '/admin/users/create',
            '/admin/departments',
            '/admin/departments/create',
            '/admin/notifications',
            '/admin/manage-appearance',
            '/admin/kanban-board',
            '/admin/tasks-calendar',
            '/admin/reports',
            '/admin/audit-logs',
            '/admin/my-notifications',
            '/admin/dev-team',
            '/admin/my-tasks',
            '/admin/profile',
            '/reports/print?type=projects',
            '/reports/export?type=workload',
        ];

        foreach ($urls as $url) {
            $status = $this->actingAs($user)->get($url)->getStatusCode();
            fwrite(STDERR, str_pad($url, 28) . ' => ' . $status . "\n");
            $this->assertSame(200, $status, "Page {$url} did not return 200");
        }
    }
}
