<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(!app()->isProduction());

        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link) && is_dir($target)) {
            try {
                Artisan::call('storage:link');
            } catch (\Throwable) {
                //
            }
        }
    }
}
