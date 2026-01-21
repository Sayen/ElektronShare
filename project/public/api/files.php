<?php
// api/files.php
session_start();
header('Content-Type: application/json');

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Helper to check auth
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

// GET: List files and folders
if ($method === 'GET') {
    $folder_id = isset($_GET['folder_id']) && is_numeric($_GET['folder_id']) ? $_GET['folder_id'] : null;

    // Get Current Folder Info
    $current_folder = null;
    $breadcrumbs = [];

    if ($folder_id) {
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        $current_folder = $stmt->fetch();

        // Build Breadcrumbs (recursive up)
        $parent = $current_folder;
        while($parent) {
            array_unshift($breadcrumbs, ['id' => $parent['id'], 'name' => $parent['name']]);
            if ($parent['parent_id']) {
                $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
                $stmt->execute([$parent['parent_id']]);
                $parent = $stmt->fetch();
            } else {
                $parent = null;
            }
        }
        array_unshift($breadcrumbs, ['id' => null, 'name' => 'Home']);
    } else {
        $breadcrumbs[] = ['id' => null, 'name' => 'Home'];
    }

    // Get Folders
    $sql_folders = "SELECT * FROM folders WHERE " . ($folder_id ? "parent_id = ?" : "parent_id IS NULL");
    $stmt = $pdo->prepare($sql_folders);
    $stmt->execute($folder_id ? [$folder_id] : []);
    $folders = $stmt->fetchAll();

    // Get Files
    $sql_files = "SELECT * FROM files WHERE " . ($folder_id ? "folder_id = ?" : "folder_id IS NULL");
    $stmt = $pdo->prepare($sql_files);
    $stmt->execute($folder_id ? [$folder_id] : []);
    $files = $stmt->fetchAll();

    // Map files to include URL
    foreach ($files as &$file) {
        $file['url'] = '/uploads/' . $file['disk_name'];
    }

    echo json_encode([
        'current_folder' => $current_folder,
        'folders' => $folders,
        'files' => $files,
        'breadcrumbs' => $breadcrumbs
    ]);
    exit;
}

// POST Actions
if ($method === 'POST') {
    requireAuth();

    if ($action === 'create_folder') {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? 'Neuer Ordner';
        $parent_id = $data['parent_id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parent_id]);
        echo json_encode(['success' => true]);
    }

    if ($action === 'delete_folder') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];

        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }

    if ($action === 'delete_file') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];

        $stmt = $pdo->prepare("SELECT disk_name FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if ($file) {
            $path = '../uploads/' . $file['disk_name'];
            if (file_exists($path)) unlink($path);

            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo json_encode(['success' => true]);
    }

    if ($action === 'update_description') {
        $data = json_decode(file_get_contents('php://input'), true);
        $folder_id = $data['folder_id'];
        $description = $data['description'];

        $stmt = $pdo->prepare("UPDATE folders SET description = ? WHERE id = ?");
        $stmt->execute([$description, $folder_id]);
        echo json_encode(['success' => true]);
    }

    if ($action === 'upload') {
        $folder_id = $_POST['folder_id'] ?? null;
        if ($folder_id === 'null') $folder_id = null;

        if (!empty($_FILES['files']['name'][0])) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            foreach ($_FILES['files']['name'] as $i => $name) {
                $tmpName = $_FILES['files']['tmp_name'][$i];
                $extension = pathinfo($name, PATHINFO_EXTENSION);

                $blocked = ['php', 'phtml', 'exe', 'sh'];
                if (in_array(strtolower($extension), $blocked)) continue;

                $diskName = uniqid() . '.' . $extension;
                if (move_uploaded_file($tmpName, $uploadDir . $diskName)) {
                    $stmt = $pdo->prepare("INSERT INTO files (folder_id, name, disk_name, size) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$folder_id, $name, $diskName, $_FILES['files']['size'][$i]]);
                }
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No files']);
        }
    }
}
?>
