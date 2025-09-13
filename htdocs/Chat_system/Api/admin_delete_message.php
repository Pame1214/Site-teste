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

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error'=>'ID inválido']);
    exit;
}

try {
    $del = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $del->execute([$id]);
    echo json_encode(['success'=>true, 'deleted_id'=>$id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}