$src_math = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\Mathematics"
$src_services = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\Services"
$dest_math = "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\Mathematics"
$dest_services = "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\Services"

Write-Host "Copying Mathematics..."
if (-Not (Test-Path "$dest_math")) { New-Item -ItemType Directory -Force -Path "$dest_math" }
Copy-Item "$src_math\*" -Destination "$dest_math" -Recurse -Force

Write-Host "Copying Services..."
if (-Not (Test-Path "$dest_services")) { New-Item -ItemType Directory -Force -Path "$dest_services" }
Copy-Item "$src_services\*" -Destination "$dest_services" -Recurse -Force

Write-Host "Replacing Namespaces in copied files..."
$files = Get-ChildItem -Path $dest_math, $dest_services -Recurse -Filter *.php

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Replace App\Domains\Cosmology with WorldOS\Domains\Evolution
    $content = $content -replace "App\\Domains\\Cosmology\\Mathematics", "WorldOS\Domains\Evolution\Mathematics"
    $content = $content -replace "App\\Domains\\Cosmology\\Services", "WorldOS\Domains\Evolution\Services"
    $content = $content -replace "App\\Domains\\Cosmology\\ValueObjects", "WorldOS\Domains\Evolution\ValueObjects"
    $content = $content -replace "App\\Domains\\Cosmology\\Entities", "WorldOS\Domains\Evolution\ValueObjects" # Because WorldStateVector is a VO now
    $content = $content -replace "App\\Domains\\Cosmology\\Enums", "WorldOS\Domains\Evolution\Enums"
    
    # And generic replace
    $content = $content -replace "App\\Domains\\Cosmology", "WorldOS\Domains\Evolution"

    Set-Content -Path $file.FullName -Value $content -Encoding UTF8
}

Write-Host "Done migrating Services and Mathematics!"
