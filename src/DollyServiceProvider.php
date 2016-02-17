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
            return "<?php if (! Laracasts\Dolly\RussianCaching::setUp{$expression}) { ?>";
        });

        Blade::directive('endcache', function () {
            return "<?php } echo Laracasts\Dolly\RussianCaching::tearDown() ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
