<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Lib/config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'Método não permitido']);
    exit;
}
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error'=>'Acesso não autorizado']);
    exit;
}

$pdo = db();
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([ (int) $_SESSION['user_id'] ]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$r || (int)$r['is_admin'] !== 1) {
    http_response_code(403);
    echo json_encode(['error'=>'Somente admins podem executar essa ação']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$nickname = trim($_POST['nickname'] ?? '');

if ($user_id <= 0 && $nickname === '') {
    http_response_code(400);
    echo json_encode(['error'=>'Forneça user_id ou nickname']);
    exit;
}

try {
    if ($user_id > 0) {
        $where = 'id = ?';
        $param = $user_id;
    } else {
        $where = 'nickname = ?';
        $param = $nickname;
    }

    if ($action === 'block') {
        $stmt = $pdo->prepare("UPDATE users SET blocked = 1 WHERE $where");
        $stmt->execute([$param]);
        echo json_encode(['success'=>true, 'action'=>'blocked']);
    } elseif ($action === 'unblock') {
        $stmt = $pdo->prepare("UPDATE users SET blocked = 0 WHERE $where");
        $stmt->execute([$param]);
        echo json_encode(['success'=>true, 'action'=>'unblocked']);
    } else {
        http_response_code(400);
        echo json_encode(['error'=>'Ação inválida. Use action=block ou action=unblock']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}