<?php

namespace GeorgeBuilds\Molecule\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
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

    /**
     * @var array<string, mixed> Viewer options forwarded to 3Dmol.js.
     */
    public array $viewerOptions = [];

    /**
     * @var array<string, mixed> Model options forwarded to 3Dmol.js.
     */
    public array $modelOptions = [];

    /**
     * @var array<string, mixed> Style overrides forwarded to 3Dmol.js.
     */
    public array $styleOptions = [];

    // Resolved molecule data
    public ?string $moleculeData = null;

    public string $moleculeFormat = 'sdf';

    public ?string $error = null;

    public function mount(): void
    {
        /** @var string $defaultBg */
        $defaultBg = config('livewire-molecule.default_background', '#ffffff');
        $this->backgroundColor ??= $defaultBg;
        $this->viewerOptions = $this->mergeDefaultOptions('viewer_options', $this->viewerOptions);
        $this->modelOptions = $this->mergeDefaultOptions('model_options', $this->modelOptions);
        $this->styleOptions = $this->mergeDefaultOptions('style_options', $this->styleOptions);
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
                $this->moleculeData = $this->getCachedOrFetch('pdb', $this->pdb, fn () => $this->fetchFromPdb($this->pdb));
                $this->moleculeFormat = 'pdb';
            } elseif ($this->pubchemCid) {
                $this->moleculeData = $this->getCachedOrFetch('pubchem', $this->pubchemCid, fn () => $this->fetchFromPubChem($this->pubchemCid));
                $this->moleculeFormat = 'sdf';
            } elseif ($this->smiles) {
                $this->moleculeData = $this->getCachedOrFetch('smiles', $this->smiles, fn () => $this->convertSmilesToSdf($this->smiles));
                $this->moleculeFormat = 'sdf';
            } elseif ($this->inchi) {
                $this->moleculeData = $this->getCachedOrFetch('inchi', $this->inchi, fn () => $this->convertInchiToSdf($this->inchi));
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
        try {
            /** @var Response $response */
            $response = Http::timeout($this->getTimeout())
                ->get("https://files.rcsb.org/download/{$pdbId}.pdb");

            if ($response->failed()) {
                throw new \Exception("Failed to fetch PDB structure: {$pdbId} (HTTP {$response->status()})");
            }

            $body = $response->body();

            if (empty($body)) {
                throw new \Exception("PDB API returned empty data for: {$pdbId}");
            }

            return $body;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Cannot connect to PDB API. Your server may block outbound HTTP requests. Error: '.$e->getMessage());
        }
    }

    protected function fetchFromPubChem(string $cid): string
    {
        try {
            /** @var Response $response */
            $response = Http::timeout($this->getTimeout())
                ->get("https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/SDF");

            if ($response->failed()) {
                throw new \Exception("Failed to fetch PubChem compound: {$cid} (HTTP {$response->status()})");
            }

            $body = $response->body();

            if (empty($body)) {
                throw new \Exception("PubChem API returned empty data for CID: {$cid}");
            }

            return $body;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Cannot connect to PubChem API. Your server may block outbound HTTP requests. Error: '.$e->getMessage());
        }
    }

    protected function convertSmilesToSdf(string $smiles): string
    {
        try {
            $encoded = rawurlencode($smiles);
            /** @var Response $response */
            $response = Http::timeout($this->getTimeout())
                ->get("https://cactus.nci.nih.gov/chemical/structure/{$encoded}/sdf");

            if ($response->failed()) {
                throw new \Exception("Failed to convert SMILES to 3D structure (HTTP {$response->status()}). The SMILES string may be invalid.");
            }

            $body = $response->body();

            if (empty($body)) {
                throw new \Exception("NCI CACTUS API returned empty data for SMILES: {$smiles}");
            }

            return $body;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Cannot connect to NCI CACTUS API. Your server may block outbound HTTP requests. Error: '.$e->getMessage());
        }
    }

    protected function convertInchiToSdf(string $inchi): string
    {
        try {
            $encoded = rawurlencode($inchi);
            /** @var Response $response */
            $response = Http::timeout($this->getTimeout())
                ->get("https://cactus.nci.nih.gov/chemical/structure/{$encoded}/sdf");

            if ($response->failed()) {
                throw new \Exception("Failed to convert InChI to 3D structure (HTTP {$response->status()}). The InChI string may be invalid.");
            }

            $body = $response->body();

            if (empty($body)) {
                throw new \Exception("NCI CACTUS API returned empty data for InChI: {$inchi}");
            }

            return $body;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \Exception('Cannot connect to NCI CACTUS API. Your server may block outbound HTTP requests. Error: '.$e->getMessage());
        }
    }

    public function refresh(): void
    {
        $this->resolveMoleculeData();
    }

    /**
     * Get the configured timeout value for HTTP requests.
     */
    private function getTimeout(): int
    {
        /** @var int $timeout */
        $timeout = config('livewire-molecule.timeout', 10);

        return $timeout;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function mergeDefaultOptions(string $configKey, array $overrides): array
    {
        /** @var array<string, mixed> $defaults */
        $defaults = config("livewire-molecule.{$configKey}", []);

        /** @var array<string, mixed> $merged */
        $merged = array_replace($defaults, $overrides);

        return $merged;
    }

    /**
     * @param  Closure(): string  $fetcher
     */
    private function getCachedOrFetch(string $type, string $value, Closure $fetcher): string
    {
        if (! $this->isCacheEnabled()) {
            return $fetcher();
        }

        $cacheKey = $this->getCacheKey($type, $value);
        /** @var string|null $cached */
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $fresh = $fetcher();
        Cache::put($cacheKey, $fresh, $this->getCacheTtl());

        return $fresh;
    }

    private function isCacheEnabled(): bool
    {
        /** @var bool $enabled */
        $enabled = config('livewire-molecule.cache.enabled', true);

        return $enabled;
    }

    private function getCacheTtl(): int
    {
        /** @var int $ttl */
        $ttl = config('livewire-molecule.cache.ttl', 60 * 60 * 24);

        return $ttl;
    }

    private function getCacheKey(string $type, string $value): string
    {
        /** @var string $prefix */
        $prefix = config('livewire-molecule.cache.prefix', 'molecule_');

        return $prefix.$type.':'.sha1($value);
    }

    public function render(): View
    {
        /** @var view-string $viewName */
        $viewName = 'livewire-molecule::components.molecule';

        return view($viewName);
    }
}
