<?php

declare(strict_types=1);

$baseAppDir = realpath(__DIR__ . '/../app/Domains');
$baseTuzyDir = realpath(__DIR__ . '/../src/Tuzy');

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseAppDir));
$phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$replacements = [];

foreach ($phpFiles as $file) {
    $filePath = $file[0];
    if (!is_file($filePath)) continue;

    $content = file_get_contents($filePath);
    
    // Find the original namespace
    if (preg_match('/namespace\s+([^;]+);/i', $content, $matchNm)) {
        $oldNamespace = trim($matchNm[1]);
        $className = basename($filePath, '.php');
        $oldFullName = $oldNamespace . '\\' . $className;

        // Find the mapped new name from the file content
        // Either from "extends \Tuzy\..." or "class_alias(\Tuzy\..., ...)"
        
        $newFullName = null;
        if (preg_match('/extends\s+\\\\?(Tuzy\\\\[A-Za-z0-9_\\\\]+)/i', $content, $m)) {
            $newFullName = $m[1];
        } elseif (preg_match('/\\\\class_alias\(\\\\?(Tuzy\\\\[A-Za-z0-9_\\\\]+)::class/i', $content, $m)) {
            $newFullName = $m[1];
        } elseif (preg_match('/use\s+\\\\?(Tuzy\\\\[A-Za-z0-9_\\\\]+);/i', $content, $m)) {
            $newFullName = $m[1];
        }
        
        if ($newFullName) {
            $replacements[$oldFullName] = ltrim($newFullName, '\\');
        }
    }
}

echo "Found " . count($replacements) . " mapping entries.\n";

// Now iterate over the whole codebase and apply replacements
$searchDirs = [
    realpath(__DIR__ . '/../app'),
    realpath(__DIR__ . '/../src'),
    realpath(__DIR__ . '/../tests'),
    realpath(__DIR__ . '/../database'),
    realpath(__DIR__ . '/../routes'),
    realpath(__DIR__ . '/../bootstrap'),
];

$fileCount = 0;
$modifiedCount = 0;

foreach ($searchDirs as $dir) {
    if (!$dir || !is_dir($dir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($files as $file) {
        $filePath = $file[0];
        
        // Skip proxy files in app/Domains to not mess them up!
        if (strpos($filePath, $baseAppDir) === 0) {
            continue;
        }

        $content = file_get_contents($filePath);
        $newContent = $content;

        foreach ($replacements as $old => $new) {
            // Replace full class references (namespaces)
            $newContent = str_replace($old, $new, $newContent);
            // In case of double slashes App\\Domains\\
            $newContent = str_replace(addslashes($old), addslashes($new), $newContent);
        }

        if ($newContent !== $content) {
            file_put_contents($filePath, $newContent);
            $modifiedCount++;
        }
        $fileCount++;
    }
}

echo "Processed $fileCount files. Modified $modifiedCount files.\n";
echo "Done.\n";
