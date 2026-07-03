<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    // السماح بالدخول للوحة لكل مستخدم نشط (الصلاحيات التفصيلية على مستوى كل مورد)
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active;
    }

    // صورة المستخدم في قائمة اللوحة (وإلا يُستخدم الافتراضي)
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'job_title',
        'department_id',
        'manager_id',
        'avatar',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // المشاريع التي ينتمي إليها المستخدم
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // الفرق التي ينتمي إليها المستخدم
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    // المهام المعينة للمستخدم
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // المهام التي أنشأها المستخدم
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    // المشاريع التي أنشأها المستخدم
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    // الفرق التي أنشأها المستخدم
    public function createdTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'created_by');
    }

    // القسم
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // التسمية في قوائم الاختيار: الاسم — القسم · الصفة
    public function getSelectLabelAttribute(): string
    {
        $roleLabels = ['admin' => 'مدير النظام', 'manager' => 'مدير', 'user' => 'مستخدم'];

        $meta = array_filter([
            $this->department?->name,
            $this->job_title ?: ($roleLabels[$this->role] ?? $this->role),
        ]);

        return $this->name . (count($meta) ? ' — ' . implode(' · ', $meta) : '');
    }

    // المدير المباشر
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // المرؤوسون
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }
}