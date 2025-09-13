<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Lib/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');

    // Validar o email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Por favor, insira um email válido.";
    } elseif ($email && $password && $nickname) {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email já cadastrado.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (email, password, nickname) VALUES (?, ?, ?)");
                $stmt->execute([$email, $hash, $nickname]);

                // Obter o ID do usuário recém-criado
                $userId = $pdo->lastInsertId();
                
                // Armazenar o ID do usuário na sessão
                $_SESSION['user_id'] = $userId;
                $_SESSION['nickname'] = $nickname;

                // Redirecionar para room.php
                header("Location: ../room.php");
                exit();
            }
        } catch (Exception $e) {
            $error = "Erro ao acessar o banco de dados.";
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Registrar - Chat</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f5f5;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    form{background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);width:320px}
    input{width:100%;padding:8px;margin:6px 0;border:1px solid #ccc;border-radius:4px}
    button{width:100%;padding:10px;background:#28a745;color:#fff;border:none;border-radius:4px;cursor:pointer}
    a{color:#007BFF;text-decoration:none}
    .error{color:#c00;margin-bottom:8px}
    .success{color:#080;margin-bottom:8px}
  </style>
</head>
<body>
<form method="post" novalidate>
  <h2>Registrar</h2>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
  <label>Email</label>
  <input type="email" name="email" required />
  <label>Apelido</label>
  <input type="text" name="nickname" required />
  <label>Senha</label>
  <input type="password" name="password" required />

  <button type="submit">Cadastrar</button>
  <p style="margin-top:10px">Já tem conta? <a href="login.php">Entrar</a></p>
</form>
</body>
</html>
