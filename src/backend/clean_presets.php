<?php
$filePath = __DIR__ . '/app/Domains/Saga/Services/GenesisPresetService.php';
$content = file_get_contents($filePath);

$patterns = [
    "/\s*'power_system'\s*=>.*?,\n/",
    "/\s*'power_ceiling'\s*=>.*?,\n/",
    "/\s*'tech_level'\s*=>.*?,\n/",
    "/\s*'environment'\s*=>.*?,\n/",
    "/\s*'social_structure'\s*=>.*?,\n/",
    "/\s*'starting_crisis'\s*=>.*?,\n/",
    "/\s*'power_ranking'\s*=>.*?,\n/"
];

foreach ($patterns as $pattern) {
    $content = preg_replace($pattern, "\n", $content);
}

// Remove the unused 'use' statements at the top
$usePatterns = [
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\PowerSystemType;\n/",
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\PowerCeiling;\n/",
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\TechLevel;\n/",
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\StartingEnvironment;\n/",
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\SocialStructure;\n/",
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\StartingCrisis;\n/",
    "/\s*use App\\\\Domains\\\\World\\\\Enums\\\\PowerRanking;\n/"
];

foreach ($usePatterns as $pattern) {
    $content = preg_replace($pattern, "\n", $content);
}


file_put_contents($filePath, $content);
echo "Cleaned GenesisPresetService.php\n";
