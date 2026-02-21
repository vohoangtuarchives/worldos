<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$baseAppDir = realpath(__DIR__ . '/../app/Domains');
$baseTuzyDir = realpath(__DIR__ . '/../src/Tuzy');

if (!$baseAppDir) {
    die("Error: Could not find app/Domains directory.\n");
}
if (!$baseTuzyDir) {
    die("Error: Could not find src/Tuzy directory.\n");
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseAppDir));
$phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$mappingHeuristics = [
    // Domain
    'Contracts'    => 'Tuzy\Domain\{Domain}',
    'Interfaces'   => 'Tuzy\Domain\{Domain}',
    'Events'       => 'Tuzy\Domain\{Domain}',
    'Exceptions'   => 'Tuzy\Domain\{Domain}',
    'Enums'        => 'Tuzy\Domain\{Domain}',
    'ValueObjects' => 'Tuzy\Domain\{Domain}',
    'Entity'       => 'Tuzy\Domain\{Domain}',
    'Aggregates'   => 'Tuzy\Domain\{Domain}',
    'Models'       => 'Tuzy\Domain\{Domain}',
    'Policy'       => 'Tuzy\Domain\{Domain}',
    'Policies'     => 'Tuzy\Domain\{Domain}',
    'Factory'      => 'Tuzy\Domain\{Domain}',
    'Dictionaries' => 'Tuzy\Domain\{Domain}',

    // Infrastructure
    'Repositories' => 'Tuzy\Infrastructure\{Domain}',
    'Providers'    => 'Tuzy\Infrastructure\{Domain}',
    'Data'         => 'Tuzy\Infrastructure\{Domain}',

    // Presentation
    'Controllers'  => 'Tuzy\Presentation\{Domain}',
    'Requests'     => 'Tuzy\Presentation\{Domain}',
    'Resources'    => 'Tuzy\Presentation\{Domain}',
    'View'         => 'Tuzy\Presentation\{Domain}',

    // Application (default for many)
    'Services'     => 'Tuzy\Application\{Domain}',
    'Processor'    => 'Tuzy\Application\{Domain}',
    'Interaction'  => 'Tuzy\Application\{Domain}',
    'AI'           => 'Tuzy\Application\{Domain}',
    'Jobs'         => 'Tuzy\Application\{Domain}',
    'Commands'     => 'Tuzy\Application\{Domain}',
    'Console'      => 'Tuzy\Application\{Domain}',
    'Preset'       => 'Tuzy\Application\{Domain}',
    'Memory'       => 'Tuzy\Application\{Domain}',
    'Support'      => 'Tuzy\Application\{Domain}',
    'Observer'     => 'Tuzy\Application\{Domain}',
    'LLM'          => 'Tuzy\Application\{Domain}',
];

function determineDestination(string $subDir, string $domainName, array $mappingHeuristics): string
{
    // E.g. $subDir might be "Services", "Contracts", "LLM\Services", etc.
    $firstPart = explode('\\', $subDir)[0] ?? '';

    if (isset($mappingHeuristics[$firstPart])) {
        $layerNs = str_replace('{Domain}', $domainName, $mappingHeuristics[$firstPart]);
        return "{$layerNs}\\{$subDir}";
    }

    // Default to Application if we don't know
    return "Tuzy\\Application\\{$domainName}\\{$subDir}";
}

$migratedCount = 0;
$skippedCount = 0;

foreach ($phpFiles as $file) {
    $filePath = $file[0];
    
    // Skip if not an actual file (e.g., . or ..)
    if (!is_file($filePath)) {
        continue;
    }

    $relativePath = str_replace($baseAppDir . DIRECTORY_SEPARATOR, '', $filePath);
    $relativePath = str_replace('/', '\\', $relativePath);
    $parts = explode('\\', $relativePath);

    // If it's something right in the Domain root, e.g. App\Domains\World\WorldState.php
    if (count($parts) < 2) {
        continue;
    }

    if (count($parts) == 2) {
        $domainName = $parts[0];
        $className = str_replace('.php', '', $parts[1]);
        $subDir = ''; // Root of domain
        $destNs = "Tuzy\\Domain\\{$domainName}";
    } else {
        $domainName = array_shift($parts);
        $className = str_replace('.php', '', array_pop($parts));
        $subDir = implode('\\', $parts);
        $destNs = determineDestination($subDir, $domainName, $mappingHeuristics);
    }
    
    $srcNs = "App\\Domains\\{$domainName}" . ($subDir !== '' ? "\\{$subDir}" : "");
    $targetDir = realpath(__DIR__ . '/../src') . '\\' . str_replace('\\', DIRECTORY_SEPARATOR, $destNs);
    $targetFile = $targetDir . DIRECTORY_SEPARATOR . $className . '.php';

    // If target already exists, skip
    if (file_exists($targetFile)) {
        $skippedCount++;
        continue;
    }

    $content = file_get_contents($filePath);
    
    // Extract type (class, interface, trait, enum) to generate appropriate proxy
    $type = 'class';
    if (preg_match('/enum\s+' . preg_quote($className, '/') . '/i', $content)) {
        $type = 'enum';
    } elseif (preg_match('/interface\s+' . preg_quote($className, '/') . '/i', $content)) {
        $type = 'interface';
    } elseif (preg_match('/trait\s+' . preg_quote($className, '/') . '/i', $content)) {
        $type = 'trait';
    }
    
    // Replace namespace in the new file
    $newContent = preg_replace('/namespace\s+' . preg_quote($srcNs, '/') . '\s*;/i', "namespace {$destNs};", $content);

    // Create target dir
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    file_put_contents($targetFile, $newContent);

    // Generate proxy
    $proxyContent = "<?php\n\n";
    $proxyContent .= "declare(strict_types=1);\n\n";
    $proxyContent .= "namespace {$srcNs};\n\n";
    $proxyContent .= "/**\n * @deprecated Proxy class to maintain backward compatibility.\n * Use \\{$destNs}\\{$className} instead.\n */\n";

    if ($type === 'class') {
        // Can it be final? We might have issues if original was final and we extend.
        // Actually, if we just use class_alias, it works better for classes!
        // But for IDE hinting, extends is sometimes better.
        // Let's just use class_alias dynamically, or write a dummy class.
        // But if it's an interface, extending is normal.
        
        $proxyContent .= "if (false) {\n";
        $proxyContent .= "    class {$className} extends \\{$destNs}\\{$className}\n    {\n    }\n";
        $proxyContent .= "}\n";
        $proxyContent .= "\\class_alias(\\{$destNs}\\{$className}::class, {$className}::class);\n";
    } elseif ($type === 'interface') {
        $proxyContent .= "interface {$className} extends \\{$destNs}\\{$className}\n{\n}\n";
    } elseif ($type === 'trait') {
        $proxyContent .= "trait {$className}\n{\n    use \\{$destNs}\\{$className};\n}\n";
    } elseif ($type === 'enum') {
        // Enums cannot be extended. You must use the real one or class_alias.
        // PHP 8 `class_alias` does NOT work for enums in all contexts unfortunately.
        // But we can try to alias it.
        $proxyContent .= "if (false) {\n";
        $proxyContent .= "    enum {$className} {}\n";
        $proxyContent .= "}\n";
        $proxyContent .= "\\class_alias(\\{$destNs}\\{$className}::class, {$className}::class);\n";
    }

    file_put_contents($filePath, $proxyContent);
    $migratedCount++;
}

echo "Migrated: $migratedCount files.\n";
echo "Skipped: $skippedCount files (already exists in Tuzy).\n";

echo "Done.\n";
