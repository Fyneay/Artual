<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ListsPeriodsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lists_periods')->insert([
            'name' => 'Временное хранение (5 лет)',
            'retention_period' => 5,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Временное хранение (10 лет)',
            'retention_period' => 10,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Временное хранение (15 лет)',
            'retention_period' => 15,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Временное хранение (30 лет)',
            'retention_period' => 30,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Постоянное хранение (75 лет)',
            'retention_period' => 75,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Постоянное хранение',
            'retention_period' => 0, // 0 означает бессрочное хранение
        ]);
        
        // Дополнительные типы документов
        DB::table('lists_periods')->insert([
            'name' => 'Устав организации, изменения и дополнения к нему, положения о структурных подразделениях',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Документы по учреждению, реорганизации и ликвидации организации',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Протоколы (выписки) общих собраний участников (акционеров)',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Решения единственного участника (акционера)',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Приказы и распоряжения по основной деятельности (по вопросам, имеющим особую значимость)',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Годовые бухгалтерские отчёты (включая пояснительную записку)',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Годовые налоговые декларации и расчёты',
            'retention_period' => 0,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Документы по трудовым отношениям: трудовые договоры, соглашения, приложения',
            'retention_period' => 75,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Личные дела работников',
            'retention_period' => 75,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Книги учёта движения трудовых книжек и вкладышей',
            'retention_period' => 75,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Приказы по личному составу (о приёме, переводе, увольнении, поощрениях, взысканиях)',
            'retention_period' => 75,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Договоры (поставки, подряда, аренды и др.) и приложения к ним',
            'retention_period' => 5, // или срок действия + 5 лет
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Акты выполненных работ, приёма-передачи',
            'retention_period' => 5,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Платёжные поручения, выписки по расчётным счетам',
            'retention_period' => 5,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Переписка по основной деятельности (имеющая правовое значение)',
            'retention_period' => 10,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Отчёты о деятельности организации, аналитические обзоры',
            'retention_period' => 10,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Паспорта безопасности, инструкции по охране труда',
            'retention_period' => 45,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Журналы регистрации несчастных случаев на производстве',
            'retention_period' => 45,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Документы по лицензированию, сертификации, аккредитации',
            'retention_period' => 10,
        ]);
        
        DB::table('lists_periods')->insert([
            'name' => 'Документы по аттестации рабочих мест по условиям труда',
            'retention_period' => 45,
        ]);
    }
}
