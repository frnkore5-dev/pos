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
        // HestiaCP: Laravel lives in private/, web root is public_html/
        $publicHtml = dirname(base_path()) . DIRECTORY_SEPARATOR . 'public_html';

        if (is_dir($publicHtml) && basename(base_path()) === 'private') {
            $this->app->usePublicPath($publicHtml);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(!app()->isProduction());

        $this->ensurePublicStorageLink();
    }

    private function ensurePublicStorageLink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! is_dir($target)) {
            return;
        }

        if (file_exists($link) || is_link($link)) {
            return;
        }

        if (@symlink($target, $link)) {
            return;
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable) {
            //
        }
    }
}
