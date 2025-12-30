<?php

namespace GeorgeBuilds\Molecule;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use GeorgeBuilds\Molecule\Components\Molecule;

class MoleculeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/molecule.php',
            'molecule'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'livewire-molecule');

        Livewire::component('molecule', Molecule::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/molecule.php' => config_path('molecule.php'),
            ], 'molecule-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/livewire-molecule'),
            ], 'molecule-views');
        }
    }
}
