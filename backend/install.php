<?php
// install.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (file_exists('config.php')) {
    die("Installation already completed. If you want to reinstall, please delete config.php.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_pass = $_POST['admin_pass'] ?? '';

    if ($db_host && $db_name && $db_user && $admin_pass) {
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create Tables
            $queries = [
                "CREATE TABLE IF NOT EXISTS settings (
                    `key` VARCHAR(50) PRIMARY KEY,
                    `value` TEXT
                )",
                "CREATE TABLE IF NOT EXISTS folders (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    parent_id INT DEFAULT NULL,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS files (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    folder_id INT NOT NULL,
                    filename VARCHAR(255) NOT NULL,
                    path VARCHAR(255) NOT NULL,
                    mime_type VARCHAR(100),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS push_subscriptions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    endpoint TEXT NOT NULL,
                    p256dh TEXT NOT NULL,
                    auth TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ];

            foreach ($queries as $sql) {
                $pdo->exec($sql);
            }

            // Generate VAPID Keys
            // Require autoload if available to use WebPush
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                // Note: use statement inside function/block is syntax error in older PHP or strict configs?
                // Actually 'use' aliases must be global scope or we use FQCN.

                $vapid = \Minishlink\WebPush\VAPID::createVapidKeys();

                $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                $stmt->execute(['vapid_public_key', $vapid['publicKey'], $vapid['publicKey']]);
                $stmt->execute(['vapid_private_key', $vapid['privateKey'], $vapid['privateKey']]);
            } else {
                throw new Exception("vendor/autoload.php not found. Did you run composer install or upload the vendor folder?");
            }

            // Insert Settings
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");

            $pass_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt->execute(['admin_password', $pass_hash, $pass_hash]);

            // Create config file
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '$db_host');\n";
            $config_content .= "define('DB_NAME', '$db_name');\n";
            $config_content .= "define('DB_USER', '$db_user');\n";
            $config_content .= "define('DB_PASS', '$db_pass');\n";

            file_put_contents('config.php', $config_content);

            // Create uploads directory
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }

            // Create Root folder if not exists
            $stmt = $pdo->query("SELECT COUNT(*) FROM folders WHERE parent_id IS NULL");
            if ($stmt->fetchColumn() == 0) {
                $pdo->exec("INSERT INTO folders (name, description) VALUES ('Home', 'Welcome to the File Browser')");
            }

            $message = "Installation successful! <a href='/'>Go to App</a>";

        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
        label { display: block; margin-top: 1rem; }
        input { width: 100%; padding: 0.5rem; margin-top: 0.2rem; }
        button { margin-top: 1.5rem; padding: 0.7rem; width: 100%; background: #517c9f; color: white; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Installation</h1>
    <?php if($message): ?>
        <p class="<?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>"><?= $message ?></p>
    <?php endif; ?>

    <?php if(!file_exists('config.php')): ?>
    <form method="post">
        <label>Datenbank Host</label>
        <input type="text" name="db_host" value="localhost" required>

        <label>Datenbank Name</label>
        <input type="text" name="db_name" required>

        <label>Datenbank User</label>
        <input type="text" name="db_user" required>

        <label>Datenbank Passwort</label>
        <input type="password" name="db_pass">

        <label>Admin Passwort (für Login)</label>
        <input type="password" name="admin_pass" required>

        <button type="submit">Installieren</button>
    </form>
    <?php endif; ?>
</body>
</html>
