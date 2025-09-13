<?php

date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Lib/config.php';
session_start();


try {
    $pdo = db();

    // 🔧 1. Limpeza automática
    // Oculta mensagens com mais de 1 minuto
    $pdo->query("
        UPDATE messages
        SET visible = 0
        WHERE created_at <= (NOW() - INTERVAL 1 MINUTE)
          AND visible = 1
    ");

    // Deleta mensagens com mais de 5 dias
    $pdo->query("
        DELETE FROM messages
        WHERE created_at <= (NOW() - INTERVAL 30 MINUTE)
    ");

    // 🔧 2. Captura o canal pelo GET
    $channel = trim($_GET['channel'] ?? 'public');

    if (($channel === 'admin' || $channel === 'all')) {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Login necessário para ver o canal admin']);
            exit;
        }
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([ (int)$_SESSION['user_id'] ]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r || (int)$r['is_admin'] !== 1) {
            http_response_code(403);
            echo json_encode(['error' => 'Apenas admins podem ver este canal']);
            exit;
        }
    }

    // 🔧 3. Busca mensagens visíveis
    if ($channel === 'all') {
        $stmt = $pdo->query("
            SELECT id, nickname, message,
                   DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                   channel
            FROM messages
            WHERE visible = 1
            ORDER BY created_at ASC
            LIMIT 500
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, nickname, message,
                   DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at,
                   channel
            FROM messages
            WHERE channel = :ch
              AND visible = 1
            ORDER BY created_at ASC
            LIMIT 500
        ");
        $stmt->execute([':ch' => $channel]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔧 4. Converte datas UTC → UTC+4
    $tz = new DateTimeZone('+04:00');
    foreach ($rows as &$msg) {
        $dt = new DateTime($msg['created_at'], new DateTimeZone('UTC'));
        $dt->setTimezone($tz);
        $msg['created_at'] = $dt->format('Y-m-d H:i:s');
    }

    // 🔧 5. Oculta a primeira mensagem se houver mais de 10
    if (count($rows) > 5) {
        $firstId = $rows[0]['id'];
        $pdo->prepare("UPDATE messages SET visible = 0 WHERE id = ?")
            ->execute([$firstId]);
        // Remove do array que será enviado
        array_shift($rows);
    }

    echo json_encode($rows);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}