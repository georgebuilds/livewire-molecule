<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Molecule Sandbox</title>
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #0f1117;
            color: #e2e8f0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            min-height: 100vh;
            padding: 2rem;
        }

        header {
            max-width: 1200px;
            margin: 0 auto 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #2d3748;
        }

        header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f7fafc;
            letter-spacing: -0.02em;
        }

        header p {
            margin-top: 0.4rem;
            font-size: 0.8rem;
            color: #718096;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: #1a1f2e;
            border: 1px solid #2d3748;
            border-radius: 10px;
            overflow: hidden;
        }

        .card-label {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #2d3748;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .card-label strong {
            font-size: 0.8rem;
            color: #e2e8f0;
        }

        .card-label code {
            font-size: 0.7rem;
            color: #68d391;
            background: #2d3748;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            display: inline-block;
            word-break: break-all;
        }

        .card-body {
            padding: 0.75rem;
        }
    </style>
</head>
<body>

<header>
    <h1>🧬 Molecule Sandbox</h1>
    <p>Local dev preview &mdash; georgebuilds/livewire-molecule</p>
</header>

@php
$aspirinSdf = <<<'SDF'

     RDKit          3D

 21 21  0  0  0  0  0  0  0  0999 V2000
    1.2333    0.5540    0.0000 O   0  0  0  0  0  0  0  0  0  0  0  0
   -0.6952   -0.6015    0.0000 O   0  0  0  0  0  0  0  0  0  0  0  0
    1.0722   -0.8583    0.0000 O   0  0  0  0  0  0  0  0  0  0  0  0
   -1.4018    0.5529    0.0000 O   0  0  0  0  0  0  0  0  0  0  0  0
    2.3356    0.0996    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    0.3553   -0.0681    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
   -0.6836    0.7539    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    2.5509   -1.3201    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
   -1.7534   -0.6823    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    3.4396    0.9997    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
   -0.3995    2.1155    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    4.6777    0.5529    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    0.9473    2.5651    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    4.8952   -0.8063    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    2.0406    2.3566    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    3.7764   -1.7710    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
    2.2559    1.4655    0.0000 C   0  0  0  0  0  0  0  0  0  0  0  0
   -2.5000    0.3000    0.0000 H   0  0  0  0  0  0  0  0  0  0  0  0
   -1.8000   -1.7600    0.0000 H   0  0  0  0  0  0  0  0  0  0  0  0
    5.5600    1.1800    0.0000 H   0  0  0  0  0  0  0  0  0  0  0  0
    5.8700   -1.1600    0.0000 H   0  0  0  0  0  0  0  0  0  0  0  0
  1  5  1  0
  1  6  1  0
  2  6  1  0
  2  9  1  0
  3  6  2  0
  4  7  2  0
  5  8  1  0
  5 10  1  0
  6  7  1  0
  7 11  1  0
  8 16  1  0
  9 18  1  0
  9 19  1  0
 10 12  2  0
 10 17  1  0
 11 13  2  0
 11  7  1  0
 12 14  1  0
 13 15  1  0
 14 16  2  0
 15 17  2  0
 16 17  1  0
M  END
$$$$
SDF;
@endphp

<div class="grid">

    <div class="card">
        <div class="card-label">
            <strong>Aspirin — inline SDF</strong>
            <code>:sdf="$aspirinSdf"</code>
        </div>
        <div class="card-body">
            <livewire:molecule :sdf="$aspirinSdf" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Aspirin — PubChem CID</strong>
            <code>pubchem-cid="2244"</code>
        </div>
        <div class="card-body">
            <livewire:molecule pubchem-cid="2244" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Ethanol — SMILES</strong>
            <code>smiles="CCO"</code>
        </div>
        <div class="card-body">
            <livewire:molecule smiles="CCO" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Crambin — PDB</strong>
            <code>pdb="1CRN" style="cartoon"</code>
        </div>
        <div class="card-body">
            <livewire:molecule pdb="1CRN" style="cartoon" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Benzene — sphere style</strong>
            <code>smiles="c1ccccc1" style="sphere"</code>
        </div>
        <div class="card-body">
            <livewire:molecule smiles="c1ccccc1" style="sphere" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Caffeine — rotating mode</strong>
            <code>smiles="Cn1cnc2c1c(=O)n(c(=O)n2C)C" mode="rotating"</code>
        </div>
        <div class="card-body">
            <livewire:molecule smiles="Cn1cnc2c1c(=O)n(c(=O)n2C)C" mode="rotating" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Ibuprofen — ball-and-stick</strong>
            <code>smiles="CC(C)Cc1ccc(cc1)C(C)C(=O)O" style="ball-and-stick"</code>
        </div>
        <div class="card-body">
            <livewire:molecule smiles="CC(C)Cc1ccc(cc1)C(C)C(=O)O" style="ball-and-stick" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

    <div class="card">
        <div class="card-label">
            <strong>Error state</strong>
            <code>smiles="NOT_A_SMILES"</code>
        </div>
        <div class="card-body">
            <livewire:molecule smiles="NOT_A_SMILES" height="300px" background-color="#1a1f2e" />
        </div>
    </div>

</div>

@livewireScripts
</body>
</html>
