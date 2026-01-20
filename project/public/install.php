<?php
// install.php - Installer for Elektron

if (file_exists('config.php')) {
    die("Installation bereits abgeschlossen. Bitte löschen Sie config.php, um neu zu installieren.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_user = $_POST['admin_user'] ?? 'admin';
    $admin_pass = $_POST['admin_pass'] ?? '';

    if (empty($db_name) || empty($db_user) || empty($admin_pass)) {
        $message = "Bitte alle Felder ausfüllen.";
    } else {
        try {
            $dsn = "mysql:host=$db_host;charset=utf8mb4";
            $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // Create Database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
            $pdo->exec("USE `$db_name`");

            // Tables
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS folders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                parent_id INT DEFAULT NULL,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                folder_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                disk_name VARCHAR(255) NOT NULL,
                mime_type VARCHAR(100),
                size INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                endpoint TEXT NOT NULL,
                keys_json TEXT NOT NULL,
                user_agent VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Create Admin
            $hash = password_hash($admin_pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->execute([$admin_user, $hash]);

            // VAPID Keys Generation
            $vapid_public = '';
            $vapid_private = '';

            if (file_exists('vendor/autoload.php')) {
                require 'vendor/autoload.php';
                try {
                    $vapid = Minishlink\WebPush\VAPID::createVapidKeys();
                    $vapid_public = $vapid['publicKey'];
                    $vapid_private = $vapid['privateKey'];
                } catch (Exception $e) {
                    $message = "Warnung: VAPID Keys konnten nicht generiert werden (Composer missing?).";
                }
            } else {
                 $message = "Warnung: vendor/autoload.php fehlt. Bitte 'composer install' ausführen.";
            }

            // Write Config
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '$db_host');\n";
            $config_content .= "define('DB_NAME', '$db_name');\n";
            $config_content .= "define('DB_USER', '$db_user');\n";
            $config_content .= "define('DB_PASS', '$db_pass');\n";
            $config_content .= "define('VAPID_PUBLIC_KEY', '$vapid_public');\n";
            $config_content .= "define('VAPID_PRIVATE_KEY', '$vapid_private');\n";
            $config_content .= "define('VAPID_SUBJECT', 'mailto:admin@example.com');\n";

            file_put_contents('config.php', $config_content);

            $message = "Installation erfolgreich! <a href='/'>Zum Start</a>. Bitte löschen Sie install.php.";

        } catch (PDOException $e) {
            $message = "DB Fehler: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Elektron Installation</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 400px; }
        input { width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #5681a5; color: white; border: none; border-radius: 4px; cursor: pointer; }
        h1 { color: #5681a5; text-align: center; }
        .alert { padding: 10px; background: #fee2e2; color: #b91c1c; border-radius: 4px; margin-bottom: 1rem; }
        .success { background: #dcfce7; color: #15803d; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Elektron Setup</h1>
        <?php if($message): ?>
            <div class="alert <?php echo strpos($message, 'erfolgreich') !== false ? 'success' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if(!file_exists('config.php')): ?>
        <form method="post">
            <h3>Datenbank</h3>
            <input type="text" name="db_host" placeholder="Host (localhost)" value="localhost">
            <input type="text" name="db_name" placeholder="DB Name" required>
            <input type="text" name="db_user" placeholder="DB User" required>
            <input type="password" name="db_pass" placeholder="DB Password">

            <h3>Admin Account</h3>
            <input type="text" name="admin_user" placeholder="Username" value="admin">
            <input type="password" name="admin_pass" placeholder="Password" required>

            <button type="submit">Installieren</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
