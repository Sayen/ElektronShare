<?php
// api/auth.php
require_once __DIR__ . '/../db.php';

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'login') {
        $password = $input['password'] ?? '';

        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'admin_password'");
        $stmt->execute();
        $hash = $stmt->fetchColumn();

        if (password_verify($password, $hash)) {
            $_SESSION['admin_logged_in'] = true;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid password']);
        }
    } elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
    } elseif ($action === 'change_password') {
        if (!isset($_SESSION['admin_logged_in'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $new_password = $input['new_password'] ?? '';
        if (strlen($new_password) < 5) {
            echo json_encode(['success' => false, 'error' => 'Password too short']);
            exit;
        }

        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'admin_password'");
        $stmt->execute([$hash]);
        echo json_encode(['success' => true]);
    }
} elseif ($method === 'GET') {
    echo json_encode([
        'logged_in' => isset($_SESSION['admin_logged_in']),
        'csrf_token' => $_SESSION['csrf_token']
    ]);
}
