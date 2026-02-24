<?php
$dir = new RecursiveDirectoryIterator('c:/Users/vohoa/worldos/src/backend/src/Domains/Evolution');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        if (substr($content, 0, 3) == "\xEF\xBB\xBF") {
            file_put_contents($path, substr($content, 3));
            echo "Stripped BOM: {$path}\n";
        }
    }
}
echo "Done stripping BOM.";
