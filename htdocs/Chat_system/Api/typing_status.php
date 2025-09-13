<?php
/*
* Para este endpoint funcionar corretamente, é necessário adicionar a coluna is_typing na tabela users:
* ALTER TABLE users ADD COLUMN is_typing TINYINT(1) DEFAULT 0;
*/

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Lib/config.php';

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

try {
    $pdo = db();
    $user_id = (int)$_SESSION['user_id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Atualizar status de digitação do usuário atual
        $typing = isset($_POST['typing']) ? (int)$_POST['typing'] : 0;
        
        // Validar valor (deve ser 0 ou 1)
        if ($typing !== 0 && $typing !== 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Valor de typing deve ser 0 ou 1']);
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE users SET is_typing = ? WHERE id = ?");
        $stmt->execute([$typing, $user_id]);
        
        echo json_encode(['success' => true, 'typing' => $typing]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Retornar array de nicknames de usuários que estão digitando (exceto o atual)
        $stmt = $pdo->prepare("
            SELECT nickname 
            FROM users 
            WHERE is_typing = 1 
            AND id != ? 
            AND blocked = 0
            ORDER BY nickname ASC
        ");
        $stmt->execute([$user_id]);
        $typingUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode($typingUsers);
        
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
    error_log("Erro em typing_status.php: " . $e->getMessage());
}
?>