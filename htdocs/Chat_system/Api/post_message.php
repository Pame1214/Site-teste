<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Lib/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error'=>'Método não permitido']);
    exit;
}

try {
    $pdo = db();
    $channel = trim($_POST['channel'] ?? 'public');

    if ($channel === 'admin') {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error'=>'Login necessário para postar no canal admin']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT is_admin, blocked, nickname FROM users WHERE id = ?");
        $stmt->execute([ (int) $_SESSION['user_id'] ]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u || (int)$u['is_admin'] !== 1) {
            http_response_code(403);
            echo json_encode(['error'=>'Somente admins podem postar no canal admin']);
            exit;
        }
        if ((int)$u['blocked'] === 1) {
            http_response_code(403);
            echo json_encode(['error'=>'Conta bloqueada']);
            exit;
        }
        $nickname = $u['nickname'];
    } else {
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT nickname, blocked FROM users WHERE id = ?");
            $stmt->execute([ (int) $_SESSION['user_id'] ]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$u) {
                http_response_code(403);
                echo json_encode(['error'=>'Usuário não encontrado']);
                exit;
            }
            if ((int)$u['blocked'] === 1) {
                http_response_code(403);
                echo json_encode(['error'=>'Conta bloqueada']);
                exit;
            }
            $nickname = $u['nickname'];
        } else {
            $nickname = trim($_POST['nickname'] ?? 'Anon');
        }
    }

    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        http_response_code(400);
        echo json_encode(['error'=>'Mensagem vazia']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO messages (nickname, message, channel, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$nickname, $message, $channel]);

    echo json_encode(['success'=>true, 'id'=>$pdo->lastInsertId(), 'channel'=>$channel]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
