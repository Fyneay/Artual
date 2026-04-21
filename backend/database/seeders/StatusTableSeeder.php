<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'утерян',
            'выдан',
            'выделен к уничтожению',
            'уничтожен',
            'передан',
        ];

        foreach ($statuses as $statusName) {
            DB::table('status')->insertOrIgnore([
                'name' => $statusName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
