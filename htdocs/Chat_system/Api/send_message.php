<?php


date_default_timezone_set('America/Sao_Paulo');
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Lib/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não logado']);
    exit;
}

try {
    $pdo = db();
    $user_id = $_SESSION['user_id'];
    
    // Verifica se o usuário está bloqueado
    $stmt = $pdo->prepare("SELECT nickname, blocked, is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(403);
        echo json_encode(['error' => 'Usuário não encontrado']);
        exit;
    }
    
    if ((int)$user['blocked'] === 1) {
        http_response_code(403);
        echo json_encode(['error' => 'Conta bloqueada']);
        exit;
    }
    
    // Lê dados da requisição JSON
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos']);
        exit;
    }
    
    $message = trim($data['message'] ?? '');
    $channel = trim($data['channel'] ?? 'public');
    
    if ($message === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Mensagem vazia']);
        exit;
    }
    
    // Verifica permissão para canal admin
    if ($channel === 'admin') {
        if ((int)$user['is_admin'] !== 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Somente admins podem postar no canal admin']);
            exit;
        }
    }
    
    $nickname = $user['nickname'];
    
    // Atualiza status online
    $stmt = $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);
    
    // Insere mensagem
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, nickname, message, channel, created_at, visible) VALUES (?, ?, ?, ?, NOW(), 1)");
    $stmt->execute([$user_id, $nickname, $message, $channel]);
    
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}