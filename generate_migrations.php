<?php

/**
 * Script to generate Laravel migrations from database dump
 * Run this script to convert the database.sql dump to Laravel migrations
 */

$dumpFile = 'source/database/dbdump/database.sql';
$migrationsDir = 'source/database/migrations';

if (!file_exists($dumpFile)) {
    die("Database dump file not found: $dumpFile\n");
}

if (!is_dir($migrationsDir)) {
    die("Migrations directory not found: $migrationsDir\n");
}

$content = file_get_contents($dumpFile);

// Extract all CREATE TABLE statements
preg_match_all('/CREATE TABLE `([^`]+)`\s*\(([^)]+)\)[^;]*;/s', $content, $matches, PREG_SET_ORDER);

$migrationNumber = 3; // Start after existing migrations

foreach ($matches as $match) {
    $tableName = $match[1];
    $tableDefinition = $match[2];
    
    // Skip tables that already have migrations
    $existingMigrations = [
        'users', 'password_resets', 'failed_jobs', 'coupon', 'cityadmin', 
        'delivery__boy__stores_', 'stores_cityadmin', 'mapbox_countries'
    ];
    
    if (in_array($tableName, $existingMigrations)) {
        continue;
    }
    
    $migrationName = date('Y_m_d_His', strtotime("+{$migrationNumber} minutes")) . "_create_{$tableName}_table.php";
    $migrationPath = $migrationsDir . '/' . $migrationName;
    
    $migrationContent = generateMigrationContent($tableName, $tableDefinition);
    
    file_put_contents($migrationPath, $migrationContent);
    echo "Created migration: $migrationName\n";
    
    $migrationNumber++;
}

echo "\nMigration generation complete!\n";

function generateMigrationContent($tableName, $definition) {
    $className = 'Create' . ucfirst(str_replace('_', '', $tableName)) . 'Table';
    
    $columns = parseColumns($definition);
    $columnDefinitions = generateColumnDefinitions($columns);
    
    return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

class {$className} extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
{$columnDefinitions}
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{$tableName}');
    }
}
";
}

function parseColumns($definition) {
    $lines = explode("\n", $definition);
    $columns = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        // Extract column definition
        if (preg_match('/`([^`]+)`\s+([^,]+)/', $line, $matches)) {
            $columnName = $matches[1];
            $columnType = trim($matches[2]);
            $columns[$columnName] = $columnType;
        }
    }
    
    return $columns;
}

function generateColumnDefinitions($columns) {
    $definitions = [];
    
    foreach ($columns as $name => $type) {
        $laravelType = convertToLaravelType($type);
        $definitions[] = "            \$table->{$laravelType}('{$name}');";
    }
    
    return implode("\n", $definitions);
}

function convertToLaravelType($mysqlType) {
    $type = strtolower($mysqlType);
    
    if (strpos($type, 'int(11)') !== false) {
        return 'integer';
    } elseif (strpos($type, 'varchar(255)') !== false) {
        return 'string';
    } elseif (strpos($type, 'varchar') !== false) {
        return 'string';
    } elseif (strpos($type, 'text') !== false) {
        return 'text';
    } elseif (strpos($type, 'longtext') !== false) {
        return 'longText';
    } elseif (strpos($type, 'float') !== false) {
        return 'float';
    } elseif (strpos($type, 'datetime') !== false) {
        return 'dateTime';
    } elseif (strpos($type, 'date') !== false) {
        return 'date';
    } elseif (strpos($type, 'timestamp') !== false) {
        return 'timestamp';
    } elseif (strpos($type, 'char') !== false) {
        return 'char';
    } else {
        return 'string'; // Default fallback
    }
} 