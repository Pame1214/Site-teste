<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Lib/config.php';


try {
    $pdo = db();
    
    // Busca usuários ativos nos últimos 5 minutos
    $stmt = $pdo->prepare("
        SELECT nickname, is_admin, blocked,
               CASE 
                   WHEN last_active > NOW() - INTERVAL 30 SECOND THEN 1 
                   ELSE 0 
               END as is_online
        FROM users 
        ORDER BY is_online DESC, nickname ASC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);



    
    echo json_encode($users);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}


try{

    $stmt = $pdo->prepare("
        DELETE FROM users
        WHERE last_active <= (NOW() - INTERVAL 3 DAY)
    ");


    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}