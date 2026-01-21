<?php
// api/files.php
require_once __DIR__ . '/../db.php';
session_start();

$isAdmin = isset($_SESSION['admin_logged_in']);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $folder_id = isset($_GET['folder_id']) && is_numeric($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;

    if ($folder_id === null) {
        // Get Root
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE parent_id IS NULL LIMIT 1");
        $stmt->execute();
        $folder = $stmt->fetch();
        if (!$folder) {
            // Should exist from install
             echo json_encode(['error' => 'Root not found']); exit;
        }
        $folder_id = $folder['id'];
    } else {
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        $folder = $stmt->fetch();
        if (!$folder) { echo json_encode(['error' => 'Folder not found']); exit; }
    }

    // Get Subfolders
    $stmt = $pdo->prepare("SELECT * FROM folders WHERE parent_id = ? ORDER BY name ASC");
    $stmt->execute([$folder_id]);
    $subfolders = $stmt->fetchAll();

    // Get Files
    $stmt = $pdo->prepare("SELECT * FROM files WHERE folder_id = ? ORDER BY filename ASC");
    $stmt->execute([$folder_id]);
    $files = $stmt->fetchAll();

    // Add parent ID for navigation
    $parent = null;
    if ($folder['parent_id']) {
        $parent = $folder['parent_id'];
    }

    echo json_encode([
        'current_folder' => $folder,
        'parent_id' => $parent,
        'folders' => $subfolders,
        'files' => $files,
        'is_admin' => $isAdmin
    ]);

} elseif ($method === 'POST') {
    if (!$isAdmin) { http_response_code(401); exit; }

    $action = $_POST['action'] ?? '';

    if ($action === 'create_folder') {
        $name = $_POST['name'] ?? 'New Folder';
        $parent_id = $_POST['parent_id'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO folders (parent_id, name, description) VALUES (?, ?, '')");
        $stmt->execute([$parent_id, $name]);
        echo json_encode(['success' => true]);

    } elseif ($action === 'upload_file') {
        $folder_id = $_POST['folder_id'];
        if (!isset($_FILES['file'])) { echo json_encode(['error' => 'No file']); exit; }

        $file = $_FILES['file'];
        $filename = $file['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $uniq_name = uniqid() . '.' . $ext;
        $target = __DIR__ . '/../uploads/' . $uniq_name;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("INSERT INTO files (folder_id, filename, path, mime_type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$folder_id, $filename, $uniq_name, $file['type']]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Upload failed']);
        }

    } elseif ($action === 'update_folder') {
        $id = $_POST['id'];
        $description = $_POST['description']; // Markdown
        $name = $_POST['name'] ?? null;

        $sql = "UPDATE folders SET description = ?";
        $params = [$description];

        if ($name) {
            $sql .= ", name = ?";
            $params[] = $name;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true]);
    }
} elseif ($method === 'DELETE') {
    if (!$isAdmin) { http_response_code(401); exit; }

    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type']; // 'folder' or 'file'
    $id = $input['id'];

    if ($type === 'file') {
        $stmt = $pdo->prepare("SELECT path FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $path = $stmt->fetchColumn();
        if ($path) {
            @unlink(__DIR__ . '/../uploads/' . $path);
            $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$id]);
        }
    } elseif ($type === 'folder') {
        $stmt = $pdo->prepare("SELECT path FROM files WHERE folder_id = ?");
        $stmt->execute([$id]);
        while($path = $stmt->fetchColumn()) {
            @unlink(__DIR__ . '/../uploads/' . $path);
        }

        $pdo->prepare("DELETE FROM folders WHERE id = ?")->execute([$id]);
    }
    echo json_encode(['success' => true]);
}
