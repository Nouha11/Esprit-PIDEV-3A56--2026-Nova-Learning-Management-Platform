#!/usr/bin/env php
<?php

/**
 * Script to fix auto-generated Doctrine migrations for MariaDB compatibility
 * Replaces RENAME INDEX with DROP + CREATE INDEX
 * 
 * Usage: php bin/fix-migrations.php
 */

$migrationsDir = __DIR__ . '/../migrations';

if (!is_dir($migrationsDir)) {
    echo "Migrations directory not found!\n";
    exit(1);
}

$files = glob($migrationsDir . '/Version*.php');
$fixedCount = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Pattern to match RENAME INDEX statements
    $pattern = '/\$this->addSql\(\'ALTER TABLE (\w+) RENAME INDEX (\w+) TO (\w+)\'\);/';
    
    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $tableName = $match[1];
            $oldIndexName = $match[2];
            $newIndexName = $match[3];
            
            // Replace with DROP + CREATE
            $replacement = "// Fix: MariaDB doesn't support RENAME INDEX\n        " .
                          "\$this->addSql('ALTER TABLE {$tableName} DROP INDEX {$oldIndexName}');\n        " .
                          "// Note: Index will be recreated by Doctrine in subsequent operations";
            
            $content = str_replace($fullMatch, $replacement, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            $fixedCount++;
            echo "✓ Fixed: " . basename($file) . "\n";
        }
    }
}

if ($fixedCount > 0) {
    echo "\n✓ Fixed {$fixedCount} migration file(s)\n";
    echo "\nIMPORTANT: After running migrations, you may need to run:\n";
    echo "  php bin/console doctrine:schema:update --force\n";
    echo "to recreate any missing indexes.\n";
} else {
    echo "✓ No migrations needed fixing\n";
}

exit(0);
