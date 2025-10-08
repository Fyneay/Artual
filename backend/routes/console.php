<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

// @deprecated Данная функция не требуется с учетом конфига лога на тип daily
Schedule::exec('> /storage/logs/laravel.log')->daily();
