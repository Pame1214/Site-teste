<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Lib/config.php';

// Se o usuário já estiver logado, redireciona para a página de chat
if (isset($_SESSION['user_id'])) {
    header("Location: ../room.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Verificar se os campos não estão vazios
    if ($email && $password) {
        // Validar o formato do e-mail
        
            try {
                $pdo = db();
                $stmt = $pdo->prepare("SELECT id, password, nickname, blocked, is_admin FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    if ((int)$user['blocked'] === 1) {
                        $error = "Conta bloqueada. Contate o administrador.";
                    } elseif (password_verify($password, $user['password'])) {
                        // Inicia a sessão com o ID e outros dados do usuário
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['nickname'] = $user['nickname'];
                        $_SESSION['is_admin'] = (int)$user['is_admin'];

                        // Atualiza o campo last_active
                        $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$user['id']]);

                        // Redireciona para o chat
                        header("Location: ../room.php");
                        exit;
                    } else {
                        $error = "Credenciais inválidas.";
                    }
                } else {
                    $error = "Credenciais inválidas.";
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
  <title>Login - Chat</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }
    form {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 320px;
    }
    input {
      width: 100%;
      padding: 8px;
      margin: 6px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #007BFF;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    a {
      color: #007BFF;
      text-decoration: none;
    }
    .error {
      color: #c00;
      margin-bottom: 8px;
    }
  </style>
</head>
<body>
<form method="post" novalidate>
  <h2>Entrar</h2>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <label>Email</label>
  <input type="email" name="email" required />
  <label>Senha</label>
  <input type="password" name="password" required />
  <button type="submit">Entrar</button>
  <p style="margin-top:10px">Não tem conta? <a href="register.php">Registre-se</a></p>
</form>
</body>
</html>
