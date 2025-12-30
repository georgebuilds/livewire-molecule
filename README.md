# Livewire Molecule

A Laravel Livewire component for 3D molecular visualization powered by [3DMol.js](https://3dmol.csb.pitt.edu/).

## Features

- 🧪 Multiple input formats: SMILES, InChI, PDB ID, PubChem CID, or raw SDF data
- 🎨 Multiple visualization styles: stick, sphere, cartoon, line, ball-and-stick
- 🔄 Three display modes: interactive, rotating, static
- ⚡ Reactive updates with Livewire
- 🎯 Automatic 3D coordinate generation from SMILES/InChI

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12
- Livewire 3.x
- Alpine.js (included with Livewire)

## Installation

```bash
composer require georgebuilds/livewire-molecule
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=molecule-config
```

## Usage

### Basic Usage

```blade
{{-- From SMILES notation --}}
<livewire:molecule smiles="CCO" />

{{-- From PDB ID --}}
<livewire:molecule pdb="1CRN" />

{{-- From PubChem CID --}}
<livewire:molecule pubchem-cid="2244" />

{{-- From InChI --}}
<livewire:molecule inchi="InChI=1S/C2H6O/c1-2-3/h3H,2H2,1H3" />

{{-- From raw SDF data --}}
<livewire:molecule :sdf="$sdfContent" />
```

### Display Modes

```blade
{{-- Interactive (default) - user can rotate/zoom --}}
<livewire:molecule smiles="CCO" mode="interactive" />

{{-- Rotating - auto-rotates on Y axis --}}
<livewire:molecule smiles="CCO" mode="rotating" />

{{-- Static - no interaction --}}
<livewire:molecule smiles="CCO" mode="static" />
```

### Visualization Styles

```blade
<livewire:molecule smiles="CCO" style="stick" />
<livewire:molecule smiles="CCO" style="sphere" />
<livewire:molecule smiles="CCO" style="ball-and-stick" />
<livewire:molecule smiles="CCO" style="cartoon" /> {{-- Best for proteins --}}
<livewire:molecule smiles="CCO" style="line" />
```

### Customizing Appearance

```blade
<livewire:molecule
    smiles="c1ccccc1"
    mode="interactive"
    style="ball-and-stick"
    background-color="#1a1a2e"
    width="500px"
    height="400px"
/>
```

### Reactive Updates

The component reacts to property changes:

```blade
<div x-data="{ currentSmiles: 'CCO' }">
    <select x-model="currentSmiles">
        <option value="CCO">Ethanol</option>
        <option value="CC(=O)O">Acetic Acid</option>
        <option value="c1ccccc1">Benzene</option>
    </select>

    <livewire:molecule :smiles="$currentSmiles" />
</div>
```

## Configuration

```php
// config/molecule.php

return [
    // Default background color
    'default_background' => '#ffffff',

    // HTTP timeout for external APIs (seconds)
    'timeout' => 10,

    // Cache settings for resolved molecules
    'cache' => [
        'enabled' => true,
        'ttl' => 60 * 60 * 24, // 24 hours
        'prefix' => 'molecule_',
    ],
];
```

## Input Format Priority

When multiple identifiers are provided, the component uses this priority:

1. `sdf` (raw data, no API call needed)
2. `pdb` (fetches from RCSB PDB)
3. `pubchem-cid` (fetches from PubChem)
4. `smiles` (converts via NCI CACTUS)
5. `inchi` (converts via NCI CACTUS)

## External APIs Used

This package relies on these free public APIs for structure retrieval and conversion:

| API | Purpose | Rate Limits |
|-----|---------|-------------|
| [RCSB PDB](https://www.rcsb.org/) | Fetch protein structures | Generous |
| [PubChem](https://pubchem.ncbi.nlm.nih.gov/) | Fetch compound structures | 5 req/sec |
| [NCI CACTUS](https://cactus.nci.nih.gov/) | SMILES/InChI → 3D conversion | Best effort |

For production use with high traffic, consider implementing your own conversion service or caching aggressively.

## Troubleshooting

### "Failed to convert SMILES to 3D structure"

- Verify the SMILES string is valid
- The NCI CACTUS service may be temporarily unavailable
- Some complex molecules may fail to convert

### Molecule appears blank

- Check browser console for JavaScript errors
- Ensure 3DMol.js is loading (check Network tab)
- Verify the molecule data is being resolved (check `$moleculeData` property)

## Testing

```bash
composer test
```

## Acknowledgments

- [3DMol.js](https://3dmol.csb.pitt.edu/) - BSD-3-Clause licensed molecular viewer
- [NCI CACTUS](https://cactus.nci.nih.gov/) - Chemical structure conversion service
- [PubChem](https://pubchem.ncbi.nlm.nih.gov/) - Chemical compound database

## License

MIT License. See [LICENSE](LICENSE) for details.

This package includes 3DMol.js which is licensed under BSD-3-Clause.
