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
            try {
                $stmt = $pdo->query("SELECT id, created_at, user_agent, ip_address FROM push_subscriptions ORDER BY created_at DESC");
                $subs = $stmt->fetchAll();
            } catch (PDOException $e) {
                // Fallback for old schema (missing user_agent/ip columns)
                if ($e->getCode() === '42S22') {
                     $stmt = $pdo->query("SELECT id, created_at FROM push_subscriptions ORDER BY created_at DESC");
                     $subs = $stmt->fetchAll();
                } else {
                    throw $e;
                }
            }
            echo json_encode(['subscriptions' => $subs]);
        }
    }

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'subscribe') {
        try {
            if (!isset($input['subscription'])) {
                throw new Exception("Missing subscription data");
            }
            $subscription = $input['subscription'];
            $endpoint = $subscription['endpoint'] ?? '';
            $key = $subscription['keys']['p256dh'] ?? '';
            $token = $subscription['keys']['auth'] ?? '';

            if (!$endpoint || !$key || !$token) {
                throw new Exception("Invalid subscription data");
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            try {
                // Try new schema first
                $stmt = $pdo->prepare("INSERT INTO push_subscriptions (endpoint, p256dh, auth, user_agent, ip_address) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$endpoint, $key, $token, $userAgent, $ip]);
            } catch (PDOException $e) {
                // Fallback to old schema if columns missing (user didn't run update.php)
                // Error code 42S22 is "Column not found"
                if ($e->getCode() === '42S22') {
                     $stmt = $pdo->prepare("INSERT INTO push_subscriptions (endpoint, p256dh, auth) VALUES (?, ?, ?)");
                     $stmt->execute([$endpoint, $key, $token]);
                } else {
                    throw $e;
                }
            }
            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    } elseif ($action === 'send') {
        if (!isset($_SESSION['admin_logged_in'])) {
            http_response_code(401); exit;
        }

        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $csrf_token = $headers['x-csrf-token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
             http_response_code(403);
             echo json_encode(['error' => 'Invalid CSRF token']);
             exit;
        }

        try {
            $title = $input['title'];
            $body = $input['body'];
            $url = $input['url'] ?? '/';

            // Get Keys
            $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'vapid_public_key'");
            $publicKey = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'vapid_private_key'");
            $privateKey = $stmt->fetchColumn();

            if (!$publicKey || !$privateKey) {
                throw new Exception("VAPID keys not found in settings. Please check your configuration.");
            }

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
                try {
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
                } catch (Throwable $e) {
                    // Ignore invalid subscriptions to prevent stopping the whole batch
                    continue;
                }
            }

            foreach ($webPush->flush() as $report) {
                // Handle reporting if needed
            }

            echo json_encode(['success' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
