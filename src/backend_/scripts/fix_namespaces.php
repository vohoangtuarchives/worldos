<?php
$files = glob("c:/Users/vohoa/worldos/src/backend/src/Domains/Evolution/Services/*.php");
$classes = [
    'CivilizationSnapshot' => 'WorldOS\Domains\Evolution\ValueObjects\CivilizationSnapshot',
    'WorldSnapshot' => 'WorldOS\Domains\Evolution\ValueObjects\WorldSnapshot',
    'CosmicState' => 'WorldOS\Domains\Evolution\ValueObjects\CosmicState',
    'EnvironmentState' => 'WorldOS\Domains\Evolution\ValueObjects\EnvironmentState'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    $changed = false;
    foreach ($classes as $short => $full) {
        // Mẫu regex để tìm \bShortClass\b
        if (preg_match("/\b$short\b/", $content)) {
            // Kiểm tra xem đã có use statement chưa
            if (strpos($content, "use $full;") === false && strpos($content, "namespace WorldOS\Domains\Evolution\ValueObjects;") === false) {
                if (preg_match('/namespace [^;]+;/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $pos = $matches[0][1] + strlen($matches[0][0]);
                    $content = substr_replace($content, "\nuse $full;", $pos, 0);
                    $changed = true;
                }
            }
        }
    }
    if ($changed) {
        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}
echo "Done.";
