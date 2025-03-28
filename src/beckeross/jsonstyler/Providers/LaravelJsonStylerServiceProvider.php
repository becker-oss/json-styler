<?php

namespace beckeross\jsonstyler\Providers;

use beckeross\jsonstyler\View\Components\JsonStyler;
use Illuminate\Support\ServiceProvider;
use beckeross\jsonstyler\Repositories\JsonStylerRepository;
use Illuminate\Support\Facades\Blade;

class LaravelJsonStylerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Blade-Komponente registrieren (Korrekt)
        Blade::component(JsonStyler::class, 'json-styler');

        // Pfad zu den Blade-Views registrieren
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'jsonstyler');

        // Config veröffentlichen
        $this->publishes([
            __DIR__ . '/../config/jsonstyler.php' => config_path('jsonstyler.php'),
        ], 'laravelJsonStyler-config');
    }

    public function register()
    {
        // Standard-Config mit veröffentlichter Config zusammenführen
        $this->mergeConfigFrom(__DIR__ . '/../config/jsonstyler.php', 'jsonstyler');

        // Repository als Singleton registrieren
        $this->app->singleton(JsonStylerRepository::class, function ($app) {
            $repo = new JsonStylerRepository();
            $repo->loadThemes(); // Lade hier direkt die Config
            return $repo;
        });
    }
}
