<?php

namespace App\Observers;

use App\Models\Section;
use Illuminate\Support\Facades\Log;

class SectionObserver
{
    public function created(Section $section)
    {
        Log:info("Раздел {name} создан", ['name'=>$section->name]);
    }

    public function updated(Section $section)
    {
        Log::info('Раздел {name} изменен', ['name'=>$section->name]);
    }

    public function deleted(Section $section)
    {
        Log::info('Раздел {name} удален', ['name'=>$section->name]);
    }
}
