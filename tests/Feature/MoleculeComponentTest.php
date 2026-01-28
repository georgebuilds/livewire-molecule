<?php

use GeorgeBuilds\Molecule\Components\Molecule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('renders without errors', function () {
    Livewire::test(Molecule::class)
        ->assertStatus(200);
});

it('shows error when no molecule identifier provided', function () {
    Livewire::test(Molecule::class)
        ->assertSet('error', 'No molecule identifier provided. Use smiles, inchi, pdb, sdf, or pubchem-cid.');
});

it('accepts sdf data directly', function () {
    $sdfData = 'mock sdf content';

    Livewire::test(Molecule::class, ['sdf' => $sdfData])
        ->assertSet('moleculeData', $sdfData)
        ->assertSet('moleculeFormat', 'sdf')
        ->assertSet('error', null);
});

it('sets default mode to interactive', function () {
    Livewire::test(Molecule::class)
        ->assertSet('mode', 'interactive');
});

it('accepts custom display options', function () {
    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
        'mode' => 'rotating',
        'style' => 'sphere',
        'backgroundColor' => '#000000',
    ])
        ->assertSet('mode', 'rotating')
        ->assertSet('style', 'sphere')
        ->assertSet('backgroundColor', '#000000');
});

it('merges viewer options with config defaults', function () {
    config()->set('molecule.viewer_options', [
        'disableFog' => true,
        'backgroundAlpha' => 0.5,
    ]);

    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
        'viewerOptions' => ['backgroundAlpha' => 0.2],
    ])
        ->assertSet('viewerOptions', [
            'disableFog' => true,
            'backgroundAlpha' => 0.2,
        ]);
});

it('accepts model and style options', function () {
    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
        'modelOptions' => ['keepH' => true],
        'styleOptions' => ['stick' => ['radius' => 0.2]],
    ])
        ->assertSet('modelOptions', ['keepH' => true])
        ->assertSet('styleOptions', ['stick' => ['radius' => 0.2]]);
});

it('caches external lookups when enabled', function () {
    Cache::flush();
    config()->set('molecule.cache.enabled', true);

    Http::fake([
        'https://files.rcsb.org/download/*' => Http::response('pdb data'),
    ]);

    Livewire::test(Molecule::class, ['pdb' => '1CRN'])
        ->assertSet('moleculeData', 'pdb data');

    Http::assertSentCount(1);

    Livewire::test(Molecule::class, ['pdb' => '1CRN'])
        ->assertSet('moleculeData', 'pdb data');

    Http::assertSentCount(1);
});

it('accepts rotating mode', function () {
    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
        'mode' => 'rotating',
    ])
        ->assertSet('mode', 'rotating');
});

it('accepts static mode', function () {
    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
        'mode' => 'static',
    ])
        ->assertSet('mode', 'static');
});

it('renders interactive mode by default', function () {
    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
    ])
        ->assertSet('mode', 'interactive');
});
