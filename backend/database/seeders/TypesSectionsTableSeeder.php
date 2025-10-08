<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesSectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types_sections')->insert([
            'name' => 'Тип 1'
        ]);
        DB::table('types_sections')->insert([
            'name' => 'Тип 2'
        ]);
        DB::table('types_sections')->insert([
            'name' => 'Тип 3'
        ]);
        DB::table('types_sections')->insert([
            'name' => 'Тип 4'
        ]);
    }
}
