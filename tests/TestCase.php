<?php

namespace GeorgeBuilds\Molecule\Tests;

use GeorgeBuilds\Molecule\MoleculeServiceProvider;
use Illuminate\Support\Str;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            MoleculeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set application encryption key (required for Livewire views)
        $app['config']->set('app.key', 'base64:'.base64_encode(Str::random(32)));

        // Setup default config if needed
        $app['config']->set('molecule.timeout', 10);
        $app['config']->set('molecule.default_background', '#ffffff');
    }
}
