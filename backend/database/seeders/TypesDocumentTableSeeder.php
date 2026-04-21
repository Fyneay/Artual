<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypesDocumentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('types_document')->insert([
            'name' => 'оригинал',
        ]);
        
        DB::table('types_document')->insert([
            'name' => 'электронный',
        ]);
        
        DB::table('types_document')->insert([
            'name' => 'смешанный',
        ]);
    }
}
