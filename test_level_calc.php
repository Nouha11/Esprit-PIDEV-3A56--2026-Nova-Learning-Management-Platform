<?php
// Quick test of level calculation

$xp = 1220;

$thresholds = [
    6 => ['min' => 1000, 'max' => 1349, 'name' => 'Apprentice'],
    7 => ['min' => 1350, 'max' => 1749, 'name' => 'Apprentice'],
    8 => ['min' => 1750, 'max' => 2199, 'name' => 'Apprentice'],
];

echo "Testing XP: $xp\n\n";

foreach ($thresholds as $level => $threshold) {
    if ($xp >= $threshold['min'] && $xp <= $threshold['max']) {
        echo "✓ MATCH: Level $level ({$threshold['name']})\n";
        echo "  Range: {$threshold['min']} - {$threshold['max']} XP\n";
    } else {
        echo "✗ No match: Level $level\n";
    }
}

echo "\nConclusion: With 1220 XP, student should be Level 6\n";
