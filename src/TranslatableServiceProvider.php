<?php

namespace DigitalUnity\Translatable;

use Illuminate\Support\ServiceProvider;

class TranslatableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/translatable.php' => config_path('translatable.php'),
            ], 'config');

        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton('translatable', function(){
            return new TranslatableClass();
        });


        $this->mergeConfigFrom(__DIR__.'/../config/translatable.php', 'translatable');
    }
}
