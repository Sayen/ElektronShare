<?php
// api/push.php
session_start();
header('Content-Type: application/json');

require_once 'db.php';

// Helper to check auth
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

$action = $_GET['action'] ?? '';

if ($action === 'vapid_public_key') {
    // Return Public Key for Frontend
    echo json_encode(['publicKey' => VAPID_PUBLIC_KEY]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'subscribe') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
             echo json_encode(['error' => 'Invalid data']); exit;
        }

        $endpoint = $data['endpoint'];
        $keys = json_encode($data['keys']);
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        // Check if exists
        $stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE endpoint = ?");
        $stmt->execute([$endpoint]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO subscriptions (endpoint, keys_json, user_agent) VALUES (?, ?, ?)");
            $stmt->execute([$endpoint, $keys, $userAgent]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'broadcast') {
        requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'];
        $body = $data['body'];
        $url = $data['url'] ?? '/';

        // Check for WebPush library
        if (!class_exists('Minishlink\WebPush\WebPush')) {
             if (file_exists('../vendor/autoload.php')) {
                 require_once '../vendor/autoload.php';
             } else {
                 echo json_encode(['success' => false, 'error' => 'WebPush library not found. Run composer install.']);
                 exit;
             }
        }

        // Use Minishlink\WebPush
        try {
            $auth = [
                'VAPID' => [
                    'subject' => VAPID_SUBJECT,
                    'publicKey' => VAPID_PUBLIC_KEY,
                    'privateKey' => VAPID_PRIVATE_KEY,
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            $stmt = $pdo->query("SELECT * FROM subscriptions");
            $subs = $stmt->fetchAll();
            $sentCount = 0;

            foreach ($subs as $sub) {
                $keys = json_decode($sub['keys_json'], true);
                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $sub['endpoint'],
                    'publicKey' => $keys['p256dh'],
                    'authToken' => $keys['auth'],
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'url' => $url,
                    'icon' => '/assets/AppIcon.png'
                ]);

                $webPush->queueNotification($subscription, $payload);
                $sentCount++;
            }

            foreach ($webPush->flush() as $report) {
                // Handle invalid tokens (delete from DB)
                if (!$report->isSuccess()) {
                    if ($report->isSubscriptionExpired()) {
                         $endpoint = $report->getRequest()->getUri()->__toString();
                         $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE endpoint = ?");
                         $stmt->execute([$endpoint]);
                    }
                }
            }

            echo json_encode(['success' => true, 'sent_count' => $sentCount]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

if ($action === 'stats') {
    requireAuth();
    $stmt = $pdo->query("SELECT count(*) as c FROM subscriptions");
    $count = $stmt->fetch()['c'];
    echo json_encode(['subscribers' => $count]);
}
?>
