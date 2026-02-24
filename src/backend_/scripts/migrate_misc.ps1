$src_contracts = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\Contracts"
$src_evolution = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\Evolution"

$dest_contracts = "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\Contracts"
$dest_evolution = "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\EvolutionConstants"

Write-Host "Copying Contracts..."
if (-Not (Test-Path "$dest_contracts")) { New-Item -ItemType Directory -Force -Path "$dest_contracts" }
Copy-Item "$src_contracts\*" -Destination "$dest_contracts" -Recurse -Force

Write-Host "Copying EvolutionConstants..."
if (-Not (Test-Path "$dest_evolution")) { New-Item -ItemType Directory -Force -Path "$dest_evolution" }
Copy-Item "$src_evolution\*" -Destination "$dest_evolution" -Recurse -Force

Write-Host "Replacing Namespaces in copied files..."
$files = Get-ChildItem -Path $dest_contracts, $dest_evolution -Recurse -Filter *.php

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Replace App\Domains\Cosmology with WorldOS\Domains\Evolution
    $content = $content -replace "App\\Domains\\Cosmology\\Contracts", "WorldOS\Domains\Evolution\Contracts"
    $content = $content -replace "App\\Domains\\Cosmology\\Evolution", "WorldOS\Domains\Evolution\EvolutionConstants"
    $content = $content -replace "App\\Domains\\Cosmology\\Services", "WorldOS\Domains\Evolution\Services"
    $content = $content -replace "App\\Domains\\Cosmology\\ValueObjects", "WorldOS\Domains\Evolution\ValueObjects"
    $content = $content -replace "App\\Domains\\Cosmology\\Entities", "WorldOS\Domains\Evolution\ValueObjects" 
    $content = $content -replace "App\\Domains\\Cosmology\\Enums", "WorldOS\Domains\Evolution\Enums"
    $content = $content -replace "App\\Domains\\Cosmology\\Mathematics", "WorldOS\Domains\Evolution\Mathematics"
    
    # And generic replace
    $content = $content -replace "App\\Domains\\Cosmology", "WorldOS\Domains\Evolution"

    Set-Content -Path $file.FullName -Value $content -Encoding UTF8
}

# Update Services to point to EvolutionConstants instead of Evolution
$services = Get-ChildItem -Path "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\Services" -Recurse -Filter *.php
foreach ($file in $services) {
    $content = Get-Content $file.FullName -Raw
    $content = $content -replace "WorldOS\\Domains\\Evolution\\Evolution", "WorldOS\Domains\Evolution\EvolutionConstants"
    Set-Content -Path $file.FullName -Value $content -Encoding UTF8
}

Write-Host "Done migrating Contracts and Evolution Constants!"
