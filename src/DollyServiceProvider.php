<?php

namespace Laracasts\Dolly;

use Blade;
use Illuminate\Support\ServiceProvider;

class DollyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('cache', function ($expression) {
            return "<?php if (! app('Laracasts\Dolly\BladeDirective')->setUp{$expression}) { ?>";
        });

        Blade::directive('endcache', function () {
            return "<?php } echo app('Laracasts\Dolly\BladeDirective')->tearDown() ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BladeDirective::class, function () {
            return new BladeDirective();   
        });
    }
}
