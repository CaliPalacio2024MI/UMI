<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Models\Cursos\Course;
use App\Policies\CoursePolicy;
use App\Models\Users\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale('es');

        Route::bind('clase', function ($value) {
            return \App\Models\AdmonCont\HorarioClase::findOrFail($value);
        });
        Gate::policy(Course::class, CoursePolicy::class);
        User::observe(UserObserver::class);
    }
}
