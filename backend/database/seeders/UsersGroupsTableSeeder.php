<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users_groups')->insert([
            'id' => 1,
            'name' => 'Системный администратор'
        ]);
        DB::table('users_groups')->insert([
            'name' => 'Директорат'
        ]);
        DB::table('users_groups')->insert([
            'name' => 'Начальник отдела'
        ]);
        DB::table('users_groups')->insert([
            'name' => 'Сотрудник'
        ]);
    }
}
