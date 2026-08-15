<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [Role::ADMIN, 'مدیر سیستم', 'دسترسی کامل به همه بخش‌ها'],
            [Role::SALES, 'کارشناس فروش', 'مدیریت سرنخ‌ها، استعلام‌ها و سفارش‌ها'],
            [Role::EDITOR, 'تولید محتوا', 'مدیریت محصولات، مقالات و فایل‌ها'],
            [Role::DEALER, 'نماینده فروش', 'دسترسی به پنل نمایندگی'],
            [Role::CUSTOMER, 'مشتری', 'حساب کاربری مشتری'],
        ];

        foreach ($roles as [$slug, $name, $description]) {
            Role::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'description' => $description,
            ]);
        }
    }
}
