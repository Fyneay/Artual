<?php

namespace App\Observers;

use App\Models\TypeSection;
use Illuminate\Support\Facades\Log;

class TypeSectionObserver
{
    public function created(TypeSection $typeSection){
        Log::info('Тип секции {name} создан', ['name' => $typeSection->name]);
    }

    public function updated(TypeSection $typeSection){
        Log::info('Тип секции {name} изменен', ['name' => $typeSection->name]);
    }

    public function deleted(TypeSection $typeSection){
        Log::info('Тип секции {name} удален', ['name' => $typeSection->name]);
    }
}
