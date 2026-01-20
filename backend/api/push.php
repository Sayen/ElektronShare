<?php
// api/push.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

session_start();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'vapid_public_key') {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'vapid_public_key'");
        $stmt->execute();
        echo json_encode(['publicKey' => $stmt->fetchColumn()]);
        exit;
    }

    // Admin only for stats
    if (isset($_SESSION['admin_logged_in'])) {
        if ($action === 'list') {
            $stmt = $pdo->query("SELECT id, created_at FROM push_subscriptions ORDER BY created_at DESC");
            echo json_encode(['subscriptions' => $stmt->fetchAll()]);
        }
    }

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'subscribe') {
        $subscription = $input['subscription'];
        $endpoint = $subscription['endpoint'];
        $key = $subscription['keys']['p256dh'];
        $token = $subscription['keys']['auth'];

        $stmt = $pdo->prepare("INSERT INTO push_subscriptions (endpoint, p256dh, auth) VALUES (?, ?, ?)");
        $stmt->execute([$endpoint, $key, $token]);
        echo json_encode(['success' => true]);

    } elseif ($action === 'send') {
        if (!isset($_SESSION['admin_logged_in'])) {
            http_response_code(401); exit;
        }

        $title = $input['title'];
        $body = $input['body'];
        $url = $input['url'] ?? '/';

        // Get Keys
        $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'vapid_public_key'");
        $publicKey = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'vapid_private_key'");
        $privateKey = $stmt->fetchColumn();

        $auth = [
            'VAPID' => [
                'subject' => 'mailto:admin@example.com',
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ];

        $webPush = new WebPush($auth);

        $stmt = $pdo->query("SELECT * FROM push_subscriptions");
        while ($row = $stmt->fetch()) {
            $subscription = Subscription::create([
                'endpoint' => $row['endpoint'],
                'keys' => [
                    'p256dh' => $row['p256dh'],
                    'auth' => $row['auth']
                ],
            ]);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'icon' => '/AppIcon.png'
            ]);

            $webPush->queueNotification($subscription, $payload);
        }

        foreach ($webPush->flush() as $report) {
            // Handle reporting if needed
        }

        echo json_encode(['success' => true]);
    }
}
