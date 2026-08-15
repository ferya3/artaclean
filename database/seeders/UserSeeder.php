<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'slug');

        $admin = User::updateOrCreate(['email' => 'admin@artaclean.ir'], [
            'name' => 'مدیر سیستم',
            'phone' => '09120000001',
            'password' => 'password',
            'is_active' => true,
            'locale' => 'fa',
        ]);
        $admin->roles()->sync([$roles[Role::ADMIN]]);

        $sales = User::updateOrCreate(['email' => 'sales@artaclean.ir'], [
            'name' => 'کارشناس فروش',
            'phone' => '09120000002',
            'password' => 'password',
            'is_active' => true,
            'locale' => 'fa',
        ]);
        $sales->roles()->sync([$roles[Role::SALES]]);

        $dealers = [
            ['tehran-center', 'نمایندگی تهران مرکز', 'تهران', 'تهران', 'gold', 'dealer.tehran@artaclean.ir'],
            ['isfahan', 'نمایندگی اصفهان', 'اصفهان', 'اصفهان', 'silver', 'dealer.isfahan@artaclean.ir'],
            ['mashhad', 'نمایندگی مشهد', 'خراسان رضوی', 'مشهد', 'silver', 'dealer.mashhad@artaclean.ir'],
        ];

        foreach ($dealers as $index => [$slug, $name, $province, $city, $tier, $email]) {
            $dealer = Dealer::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'company_name' => $name,
                'province' => $province,
                'city' => $city,
                'phone' => '021'.str_pad((string) (91000000 + $index), 8, '0', STR_PAD_LEFT),
                'email' => $email,
                'tier' => $tier,
                'commission_rate' => match ($tier) {
                    'gold' => 12.5,
                    'silver' => 9.0,
                    default => 6.0,
                },
                'is_active' => true,
            ]);

            $user = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => '0912000001'.$index,
                'password' => 'password',
                'is_active' => true,
                'dealer_id' => $dealer->id,
                'city' => $city,
                'locale' => 'fa',
            ]);

            $user->roles()->sync([$roles[Role::DEALER]]);

            $dealer->update(['user_id' => $user->id]);
        }
    }
}
