<?php

namespace GeorgeBuilds\Molecule\Components;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Molecule extends Component
{
    // Input format - one of these is required
    public ?string $smiles = null;
    public ?string $inchi = null;
    public ?string $pdb = null;
    public ?string $sdf = null;
    public ?string $pubchemCid = null;

    // Display options
    #[Reactive]
    public string $mode = 'interactive';

    public string $style = 'stick';
    public ?string $backgroundColor = null;
    public string $width = '100%';
    public string $height = '400px';

    // Resolved molecule data
    public ?string $moleculeData = null;
    public string $moleculeFormat = 'sdf';
    public ?string $error = null;

    public function mount(): void
    {
        $this->backgroundColor ??= config('molecule.default_background', '#ffffff');
        $this->resolveMoleculeData();
    }

    public function resolveMoleculeData(): void
    {
        $this->error = null;
        $this->moleculeData = null;

        try {
            if ($this->sdf) {
                $this->moleculeData = $this->sdf;
                $this->moleculeFormat = 'sdf';
            } elseif ($this->pdb) {
                $this->moleculeData = $this->fetchFromPdb($this->pdb);
                $this->moleculeFormat = 'pdb';
            } elseif ($this->pubchemCid) {
                $this->moleculeData = $this->fetchFromPubChem($this->pubchemCid);
                $this->moleculeFormat = 'sdf';
            } elseif ($this->smiles) {
                $this->moleculeData = $this->convertSmilesToSdf($this->smiles);
                $this->moleculeFormat = 'sdf';
            } elseif ($this->inchi) {
                $this->moleculeData = $this->convertInchiToSdf($this->inchi);
                $this->moleculeFormat = 'sdf';
            } else {
                $this->error = 'No molecule identifier provided. Use smiles, inchi, pdb, sdf, or pubchem-cid.';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    protected function fetchFromPdb(string $pdbId): string
    {
        /** @var Response $response */
        $response = Http::timeout(config('molecule.timeout', 10))
            ->get("https://files.rcsb.org/download/{$pdbId}.pdb");

        if ($response->failed()) {
            throw new \Exception("Failed to fetch PDB structure: {$pdbId}");
        }

        return $response->body();
    }

    protected function fetchFromPubChem(string $cid): string
    {
        /** @var Response $response */
        $response = Http::timeout(config('molecule.timeout', 10))
            ->get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/SDF");

        if ($response->failed()) {
            throw new \Exception("Failed to fetch PubChem compound: {$cid}");
        }

        return $response->body();
    }

    protected function convertSmilesToSdf(string $smiles): string
    {
        $encoded = rawurlencode($smiles);
        /** @var Response $response */
        $response = Http::timeout(config('molecule.timeout', 10))
            ->get("https://cactus.nci.nih.gov/chemical/structure/{$encoded}/sdf");

        if ($response->failed()) {
            throw new \Exception("Failed to convert SMILES to 3D structure. The SMILES string may be invalid.");
        }

        return $response->body();
    }

    protected function convertInchiToSdf(string $inchi): string
    {
        $encoded = rawurlencode($inchi);
        /** @var Response $response */
        $response = Http::timeout(config('molecule.timeout', 10))
            ->get("https://cactus.nci.nih.gov/chemical/structure/{$encoded}/sdf");

        if ($response->failed()) {
            throw new \Exception("Failed to convert InChI to 3D structure. The InChI string may be invalid.");
        }

        return $response->body();
    }

    public function refresh(): void
    {
        $this->resolveMoleculeData();
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        /** @var view-string $viewName */
        $viewName = 'livewire-molecule::components.molecule';
        return view($viewName);
    }
}
