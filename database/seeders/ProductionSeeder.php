<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDepartment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder إنتاجي آمن (idempotent):
 *  - يُنشئ الأدمن من متغيّرات البيئة (ADMIN_EMAIL / ADMIN_PASSWORD / ADMIN_NAME).
 *  - يُنشئ الأقسام والموظفين السبعة ومشروع «تشغيل مباني قرطبة» ومهامه إن لم تكن موجودة.
 *  - لا يمسح ولا يعدّل أي بيانات قائمة، فتُحفظ تعديلات المستخدمين عبر عمليات النشر.
 * يعمل قبل config:cache فتكون env() متاحة.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1) الأدمن (من البيئة)
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@qurtuba.test')],
            [
                'name' => env('ADMIN_NAME', 'مدير النظام'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2) الأقسام (idempotent)
        $deptNames = [
            'it' => 'تقنية المعلومات',
            'fin' => 'المالية',
            'hr' => 'الموارد البشرية',
            'pmo' => 'المشاريع',
            'edu' => 'الشؤون التعليمية',
            'fac' => 'المرافق والخدمات العامة',
            'sec' => 'الأمن والسلامة',
            'trans' => 'النقل والحركة',
        ];
        $dept = [];
        foreach ($deptNames as $key => $name) {
            $dept[$key] = Department::firstOrCreate(['name' => $name])->id;
        }

        // 3) الموظفون السبعة (idempotent بالبريد) — كلمة المرور 123456
        $users = [
            ['name' => 'إبراهيم الرشيد', 'email' => 'i.alrasheed@gheras.sch.sa', 'job_title' => 'مدير المشاريع', 'role' => 'manager', 'dept' => 'pmo'],
            ['name' => 'مشاري الدخيل', 'email' => 'gss.office@gheras.sch.sa', 'job_title' => 'المشرف العام', 'role' => 'manager', 'dept' => null],
            ['name' => 'سعيد قطب', 'email' => 'HRM@gheras.sch.sa', 'job_title' => 'المدير المالي', 'role' => 'manager', 'dept' => 'fin'],
            ['name' => 'وليد القحطاني', 'email' => 'CFO@gheras.sch.sa', 'job_title' => 'مدير الموارد البشرية', 'role' => 'manager', 'dept' => 'hr'],
            ['name' => 'طارق حامد', 'email' => 'tarek.h@gheras.sch.sa', 'job_title' => 'مدير الخدمات المساندة', 'role' => 'manager', 'dept' => null],
            ['name' => 'ابوبكر حسن', 'email' => 'ITM@gheras.sch.sa', 'job_title' => 'مسؤول تقنية المعلومات', 'role' => 'manager', 'dept' => 'it'],
            ['name' => 'ياسر السيد', 'email' => 'y.alsaeed@gheras.sch.sa', 'job_title' => 'مهندس الكهرباء', 'role' => 'user', 'dept' => 'it'],
        ];
        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('123456'),
                    'role' => $u['role'],
                    'job_title' => $u['job_title'],
                    'department_id' => $u['dept'] ? $dept[$u['dept']] : null,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 4) المشروع (idempotent بالاسم)
        $pm = User::where('email', 'i.alrasheed@gheras.sch.sa')->first();
        $project = Project::firstOrCreate(
            ['name' => 'تشغيل مباني قرطبة'],
            [
                'description' => 'مشروع تشغيل وتجهيز مباني مدارس قرطبة (بيانات تشغيلية حقيقية).',
                'status' => 'in_progress',
                'priority' => 'high',
                'progress' => 0,
                'created_by' => $admin->id,
                'manager_id' => $pm?->id,
            ]
        );

        // 5) المهام — تُزرع مرة واحدة فقط (إن لم يكن للمشروع مهام) حفاظاً على التعديلات
        if ($project->tasks()->count() === 0) {
            foreach ($this->tasks() as [$name, $deptKey, $progress]) {
                $status = $progress >= 100 ? 'completed' : ($progress > 0 ? 'in_progress' : 'pending');
                $task = Task::create([
                    'title' => $name,
                    'status' => $status,
                    'priority' => 'medium',
                    'progress' => $progress,
                    'project_id' => $project->id,
                    'department_id' => $dept[$deptKey],
                    'created_by' => $admin->id,
                ]);
                TaskDepartment::firstOrCreate(
                    ['task_id' => $task->id, 'department_id' => $dept[$deptKey]],
                    ['responsibility' => 'execution']
                );
            }
        }
    }

    /** قائمة المهام: [الاسم, مفتاح القسم, نسبة الإنجاز] */
    private function tasks(): array
    {
        return [
            ['تأثيث المبنى التعليمي', 'edu', 40],
            ['الشاشات التفاعلية', 'it', 20],
            ['السبورة المنزلية', 'edu', 10],
            ['اللوحات الورقية', 'edu', 10],
            ['أجهزة الكمبيوتر المتكامل', 'it', 60],
            ['شراء الكتب المعتمدة', 'edu', 15],
            ['طباعة المواد الداخلية', 'edu', 0],
            ['تأثيث غرف الروبوتات', 'it', 15],
            ['نظارات الواقع الافتراضي', 'it', 15],
            ['تغذية المعامل الرياضية', 'edu', 0],
            ['تغذية معامل العلوم', 'edu', 15],
            ['تغذية فصول التمهيدي', 'edu', 15],
            ['المنصة التعليمية', 'it', 20],
            ['ربط المدرسة (الفايبر والنقل)', 'it', 10],
            ['الخطوط الهاتفية', 'it', 80],
            ['أجهزة الهاتف', 'it', 10],
            ['أعمال الشبكات', 'it', 10],
            ['الطابعات المكتبية', 'it', 20],
            ['آلة التصوير المكتبية', 'it', 100],
            ['مركز التصوير', 'it', 70],
            ['نظام التحكم بالدخول (Access Control)', 'it', 100],
            ['نظام الإذاعة (Public Address)', 'it', 90],
            ['نظام إدارة المواقف (Parking System)', 'it', 95],
            ['أجهزة الكمبيوتر للموظفين', 'it', 60],
            ['الطابعات', 'it', 20],
            ['معدات العيادة (عدد 2)', 'fac', 10],
            ['التصاميم والديكورات', 'pmo', 30],
            ['اللوحات الخارجية', 'pmo', 20],
            ['لوحات التشجير الداخلي', 'fac', 20],
            ['فرش النادي', 'fac', 10],
            ['أجهزة النادي', 'fac', 10],
            ['مستودعات الألعاب', 'fac', 20],
            ['أدوات الملاعب', 'fac', 0],
            ['أدوات المسبح', 'fac', 20],
            ['أدوات النظافة', 'fac', 25],
            ['تجهيز دورات المياه', 'fac', 0],
            ['تشغيل المقصف', 'fac', 0],
            ['فرش المصليات', 'fac', 0],
            ['تجهيز ورشة السيارات', 'fac', 0],
            ['فصل كاميرات البنات', 'sec', 10],
            ['تركيب كاميرات في فصول البنين', 'sec', 20],
            ['الحراسات الأمنية', 'sec', 20],
            ['فصل البنين عن البنات', 'sec', 20],
            ['الحواجز الفاصلة', 'sec', 0],
            ['الباصات', 'trans', 15],
            ['استئجار الباصات', 'trans', 10],
            ['الزي للطلاب والطالبات', 'hr', 50],
            ['سكن العمال والعاملات', 'hr', 20],
            ['سكن ضيافة المعلمين', 'hr', 20],
            ['زي العاملين والعمال', 'hr', 20],
            ['حقوق مكاتب الأساتذة المعلمين', 'fin', 20],
            ['حقوق مكاتب الأساتذة للعمال والعاملات', 'fin', 20],
            ['ضيافة التسجيل', 'hr', 0],
            ['ترميز المباني', 'pmo', 0],
            ['ميزانية البرامج والأنشطة', 'fin', 0],
            ['برنامج الأسبوع الأول', 'edu', 0],
            ['هدايا الأسبوع الأول', 'hr', 0],
            ['تجهيز المكاتب الإدارية (مكاتب الموظفين وإدارة المشروع)', 'pmo', 0],
        ];
    }
}
