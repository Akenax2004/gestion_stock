<?php

namespace App\Providers;

use Illuminate\Http\Request;
use App\Breadcrumbs\Breadcrumbs;
use Illuminate\Pagination\Paginator;
<<<<<<< HEAD
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
=======
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
>>>>>>> 58826724be3ba47de5bcd3dd0e3d1def8e583f42

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
<<<<<<< HEAD
=======
        // Fix pour les erreurs "clé trop longue" sous MySQL
        Schema::defaultStringLength(191);

>>>>>>> 58826724be3ba47de5bcd3dd0e3d1def8e583f42
        Paginator::useBootstrapFive();

        Request::macro('breadcrumbs', function (){
            return new Breadcrumbs($this);
        });
<<<<<<< HEAD

        Schema::defaultStringLength(191);
=======
>>>>>>> 58826724be3ba47de5bcd3dd0e3d1def8e583f42
    }
}
