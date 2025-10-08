<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Article;
use App\Models\User;
use App\Models\TypeSection;
use App\Models\Section;
use App\Models\UserGroup;
use App\Observers\ArticleObserver;
use App\Observers\UserObserver;
use App\Observers\TypeSectionObserver;
use App\Observers\SectionObserver;
use App\Observers\UserGroupObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
        User::observe(UserObserver::class);
        TypeSection::observe(TypeSectionObserver::class);
        Section::observe(SectionObserver::class);
        UserGroup::observe(UserGroupObserver::class);
    }
}
