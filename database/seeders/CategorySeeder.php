<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $categories = [
           [
            'name' => 'Mobile Phones',
            'department_id' => 1,
            'parent_id' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
           ],
           [
               'name' => 'Laptops',
               'department_id' => 1,
               'parent_id' => null,
               'active' => true,
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Lenovo',
               'department_id' => 1,
               'parent_id' => 1,
               'active' => true,
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Clothing',
               'department_id' => 2,
               'parent_id' => null,
               'active' => true,
               'created_at' => now(),
               'updated_at' => now(),
           ],
            [
               'name' => 'T-shirt',
               'department_id' => 2,
               'parent_id' => 2,
               'active' => true,
               'created_at' => now(),
               'updated_at' => now(),
           ],

           [
               'name' => 'Home Appliances',
               'department_id' => 3,
               'parent_id' => null,
               'active' => true,
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Books',
               'department_id' => 4,
               'parent_id' => null,
               'active' => true,
               'created_at' => now(),
               'updated_at' => now(),
           ],
       ];

        DB::table('categories')->insert($categories);
    }
}
