<?php
// Load Laravel environment
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Dropping zombie tables...\n";

Schema::disableForeignKeyConstraints();
Schema::dropIfExists('saga_entropy_ledgers');
Schema::enableForeignKeyConstraints();

echo "Dropped saga_entropy_ledgers successfully.\n";
