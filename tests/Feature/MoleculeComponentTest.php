<?php

use GeorgeBuilds\Molecule\Components\Molecule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
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

it('prefers sdf over other identifiers', function () {
    Http::fake([
        'https://files.rcsb.org/download/*' => Http::response('pdb data'),
    ]);

    Livewire::test(Molecule::class, [
        'sdf' => 'primary sdf',
        'pdb' => '1CRN',
        'smiles' => 'CCO',
    ])
        ->assertSet('moleculeData', 'primary sdf')
        ->assertSet('moleculeFormat', 'sdf');

    Http::assertNothingSent();
});

it('prefers pdb over pubchem and conversions', function () {
    Http::fake([
        'https://files.rcsb.org/download/*' => Http::response('pdb data'),
    ]);

    Livewire::test(Molecule::class, [
        'pdb' => '1CRN',
        'pubchemCid' => '2244',
        'smiles' => 'CCO',
    ])
        ->assertSet('moleculeData', 'pdb data')
        ->assertSet('moleculeFormat', 'pdb');
});

it('prefers pubchem over smiles and inchi', function () {
    Http::fake([
        'https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/*' => Http::response('pubchem data'),
    ]);

    Livewire::test(Molecule::class, [
        'pubchemCid' => '2244',
        'smiles' => 'CCO',
        'inchi' => 'InChI=1S/C2H6O/c1-2-3/h3H,2H2,1H3',
    ])
        ->assertSet('moleculeData', 'pubchem data')
        ->assertSet('moleculeFormat', 'sdf');
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

it('uses default background color from config', function () {
    config()->set('livewire-molecule.default_background', '#123456');

    Livewire::test(Molecule::class, [
        'sdf' => 'mock data',
    ])
        ->assertSet('backgroundColor', '#123456');
});

it('merges viewer options with config defaults', function () {
    config()->set('livewire-molecule.viewer_options', [
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
    config()->set('livewire-molecule.cache.enabled', true);

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

it('does not cache external lookups when disabled', function () {
    Cache::flush();
    config()->set('livewire-molecule.cache.enabled', false);

    Http::fake([
        'https://files.rcsb.org/download/*' => Http::response('pdb data'),
    ]);

    Livewire::test(Molecule::class, ['pdb' => '1CRN'])
        ->assertSet('moleculeData', 'pdb data');

    Livewire::test(Molecule::class, ['pdb' => '1CRN'])
        ->assertSet('moleculeData', 'pdb data');

    Http::assertSentCount(2);
});

it('surfaces http errors from pdb', function () {
    Http::fake([
        'https://files.rcsb.org/download/*' => Http::response('nope', 500),
    ]);

    Livewire::test(Molecule::class, ['pdb' => '1CRN'])
        ->assertSet('moleculeData', null)
        ->assertSet('error', 'Failed to fetch PDB structure: 1CRN (HTTP 500)');
});

it('surfaces empty response errors from pubchem', function () {
    Http::fake([
        'https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/*' => Http::response('', 200),
    ]);

    Livewire::test(Molecule::class, ['pubchemCid' => '2244'])
        ->assertSet('moleculeData', null)
        ->assertSet('error', 'PubChem API returned empty data for CID: 2244');
});

it('surfaces connection errors from cactus', function () {
    Http::fake(function (): void {
        throw new ConnectionException('connection failed');
    });

    Livewire::test(Molecule::class, ['smiles' => 'CCO'])
        ->assertSet('moleculeData', null)
        ->assertSet('error', 'Cannot connect to NCI CACTUS API. Your server may block outbound HTTP requests. Error: connection failed');
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
