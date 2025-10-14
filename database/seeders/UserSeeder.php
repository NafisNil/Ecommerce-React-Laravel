<?php

namespace Database\Seeders;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\RolesEnum;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com'
        ])->assignRole(RolesEnum::User->value);

        $user = User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@example.com'
        ]);
        $user->assignRole(RolesEnum::Vendor->value);
        $user->vendor()->create([
            'shop_name' => 'Vendor Shop',
            'status' => VendorStatusEnum::Approved,
            'shop_address' => '123 Vendor St, Vendor City, VC 12345',
            'user_id' => $user->id
        ]);


        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com'
        ])->assignRole(RolesEnum::Admin->value);

    }
}
