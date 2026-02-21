<?php
$filePath = __DIR__ . '/resources/views/writer/genesis.blade.php';
$content = file_get_contents($filePath);

// 1. Remove data attributes from preset card
$content = preg_replace('/data-power-system="\{\{ \$preset\[\'power_system\'\] \}\}"/', '', $content);
$content = preg_replace('/data-power-ceiling="\{\{ \$preset\[\'power_ceiling\'\] \}\}"/', '', $content);
$content = preg_replace('/data-tech-level="\{\{ \$preset\[\'tech_level\'\] \}\}"/', '', $content);
$content = preg_replace('/data-environment="\{\{ \$preset\[\'environment\'\] \}\}"/', '', $content);
$content = preg_replace('/data-social-structure="\{\{ \$preset\[\'social_structure\'\] \}\}"/', '', $content);
$content = preg_replace('/data-starting-crisis="\{\{ \$preset\[\'starting_crisis\'\] \}\}"/', '', $content);
$content = preg_replace('/data-power-ranking="\{\{ \$preset\[\'power_ranking\'\] \}\}"/', '', $content);

// 2. Remove badges in the card
$badgesPattern = '/<span class="inline-flex items-center rounded-full bg-indigo.*?<\/span>\s*<span class="inline-flex items-center rounded-full bg-emerald.*?<\/span>/s';
$content = preg_replace($badgesPattern, '', $content);

// 3. Remove the entire Mixing Panel
$mixingPanelPattern = '/<!-- Mixing Panel -->\s*<div class="border border-gray-700.*?<\/div>\s*<\/div>\s*<\/div>/s';
// Wait, mixing panel ends at <div class="grid...">...</div></div>
// Let's just do a string replacement for the exact mixing panel lines if regex is tricky.
// Instead, I'll use a simpler regex
$mixingPanelStart = '<!-- Mixing Panel -->';
$mixingPanelEnd = '<!-- Submit -->';
$posStart = strpos($content, $mixingPanelStart);
$posEnd = strpos($content, $mixingPanelEnd);
if ($posStart !== false && $posEnd !== false) {
    $content = substr_replace($content, "\n                ", $posStart, $posEnd - $posStart);
}

// 4. Remove JS lines
$jsKeys = [
    "document.getElementById('field_power_system').value = card.dataset.powerSystem;",
    "document.getElementById('field_power_ceiling').value = card.dataset.powerCeiling;",
    "document.getElementById('field_tech_level').value = card.dataset.techLevel;",
    "document.getElementById('field_environment').value = card.dataset.environment;",
    "document.getElementById('field_social_structure').value = card.dataset.socialStructure;",
    "document.getElementById('field_starting_crisis').value = card.dataset.startingCrisis;",
    "document.getElementById('field_power_ranking').value = card.dataset.powerRanking;"
];
foreach ($jsKeys as $jsKey) {
    $content = str_replace($jsKey, '', $content);
}
// don't forget genre because we removed it from Mixing panel
$content = str_replace("document.getElementById('field_genre').value = card.dataset.genre;", '', $content);

file_put_contents($filePath, $content);
echo "Cleaned genesis.blade.php\n";
