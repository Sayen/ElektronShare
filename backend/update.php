<?php
// update.php
require_once __DIR__ . '/db.php';

echo "Starting database update...<br>";

try {
    // 1. Add columns to push_subscriptions
    $table = 'push_subscriptions';
    $columns = [
        'user_agent' => 'TEXT',
        'ip_address' => 'VARCHAR(45)'
    ];

    foreach ($columns as $col => $type) {
        // Check if column exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME = :table AND COLUMN_NAME = :column");
        $stmt->execute(['dbname' => DB_NAME, 'table' => $table, 'column' => $col]);

        if ($stmt->fetchColumn() == 0) {
            echo "Adding column '$col' to '$table'...<br>";
            $pdo->exec("ALTER TABLE $table ADD COLUMN $col $type");
        } else {
            echo "Column '$col' already exists in '$table'.<br>";
        }
    }

    echo "Update completed successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
