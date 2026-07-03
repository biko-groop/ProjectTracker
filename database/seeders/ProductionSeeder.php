<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder إنتاجي آمن: يُنشئ مستخدم الأدمن إن لم يكن موجوداً (idempotent).
 * يقرأ البيانات من متغيّرات بيئة Railway:
 *   ADMIN_EMAIL, ADMIN_PASSWORD, ADMIN_NAME
 * يعمل قبل config:cache فتكون env() متاحة.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@qurtuba.test');
        $password = env('ADMIN_PASSWORD', 'password');
        $name = env('ADMIN_NAME', 'مدير النظام');

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
