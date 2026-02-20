$src_vo = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\ValueObjects"
$src_ent = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\Entities"
$src_enum = "c:\Users\vohoa\worldos\src\backend\app\Domains_Legacy\Cosmology\Enums"
$dest_vo = "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\ValueObjects"
$dest_enum = "c:\Users\vohoa\worldos\src\backend\src\Domains\Evolution\Enums"

Write-Host "Copying ValueObjects..."
if (-Not (Test-Path "$dest_vo")) { New-Item -ItemType Directory -Force -Path "$dest_vo" }
Copy-Item "$src_vo\*" -Destination "$dest_vo" -Recurse -Force

Write-Host "Copying Entities (WorldStateVector)..."
Copy-Item "$src_ent\WorldStateVector.php" -Destination "$dest_vo\WorldStateVector.php" -Force

Write-Host "Copying Enums..."
if (-Not (Test-Path "$dest_enum")) { New-Item -ItemType Directory -Force -Path "$dest_enum" }
Copy-Item "$src_enum\*" -Destination "$dest_enum" -Recurse -Force

Write-Host "Replacing Namespaces in copied files..."
$files = Get-ChildItem -Path $dest_vo, $dest_enum -Recurse -Filter *.php

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # 1. Base Domain Namespace Replacement
    $content = $content -replace "App\\Domains\\Cosmology\\ValueObjects", "WorldOS\Domains\Evolution\ValueObjects"
    $content = $content -replace "App\\Domains\\Cosmology\\Entities", "WorldOS\Domains\Evolution\ValueObjects"
    $content = $content -replace "App\\Domains\\Cosmology\\Enums", "WorldOS\Domains\Evolution\Enums"
    
    # 2. General Cosmology Domain Replacement (for return types etc)
    $content = $content -replace "App\\Domains\\Cosmology", "WorldOS\Domains\Evolution"

    Set-Content -Path $file.FullName -Value $content -Encoding UTF8
}

Write-Host "Done copying ValueObjects and Enums!"
