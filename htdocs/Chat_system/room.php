<?php

date_default_timezone_set('America/Sao_Paulo');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header("Location: /Userspace/login.php");
    exit;
}


require_once __DIR__ . "/Lib/config.php";

$pdo = db();
$nickname = $_SESSION['nickname'] ?? 'Anon';

// Atualiza status online
try {
    $stmt = $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
} catch (Exception $e) {
    error_log("Erro ao atualizar status online: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT id, email, nickname, is_admin, blocked FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['nickname'] = $user['nickname'];
        $_SESSION['is_admin'] = (int)$user['is_admin'];
        $_SESSION['blocked'] = $user['blocked'];
        $nickname = $user['nickname'];
    }
} catch (Exception $e) {
    error_log("Erro ao buscar usuário: " . $e->getMessage());
}

$is_admin = isset($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Chat ao Vivo <?= $is_admin ? '— Admin Mode' : '' ?></title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="Lib/style.css">
</head>
<body>
<div class="container">
  <div class="chat-main">
    <div class="header">
      <div>
        <h1>💬 Chat ao Vivo</h1>
        <div class="user-info">
          Conectado como: <strong><?= htmlspecialchars($nickname) ?></strong>
          <?= $is_admin ? '<span class="admin-badge">👑 ADMIN</span>' : '' ?>
        </div>
      </div>
      <div class="header-controls">
        <?php if ($is_admin): ?>
        <label class="toggle">
          <input id="adminToggle" type="checkbox" />
          <span>Modo Admin</span>
        </label>
        <?php endif; ?>
        <a href="Userspace/logout.php" class="btn btn-outline">Sair</a>
      </div>
    </div>

    <div class="chat-card">
      <div class="chat-header">💭 Chat Público</div>
      <div id="publicMessages" class="chat-messages"><div class="loading">Carregando mensagens...</div></div>
      <div id="typingStatus" class="typing-status" style="display:none;"></div>
      <form id="publicForm" class="chat-form">
        <input id="publicInput" type="text" placeholder="Digite sua mensagem..." maxlength="500" required />
        <button type="submit" class="btn btn-primary">Enviar</button>
      </form>
    </div>

    <?php if ($is_admin): ?>
    <div class="chat-card" id="adminCard" style="display:none;">
      <div class="chat-header admin-header">🛡️ Canal Administrativo</div>
      <div id="adminMessages" class="chat-messages"><div class="loading">Ative o modo admin</div></div>
      <form id="adminForm" class="chat-form">
        <input id="adminInput" type="text" placeholder="Mensagem administrativa..." maxlength="500" required />
        <button type="submit" class="btn btn-primary">Enviar</button>
      </form>
      <div class="admin-tools">
        <h4>🔧 Ferramentas Administrativas</h4>
        <div class="tool-section">
          <label class="tool-label">Deletar Mensagem:</label>
          <div class="tool-row">
            <input id="deleteId" type="number" class="tool-input" placeholder="ID da mensagem" min="1" />
            <button id="deleteBtn" type="button" class="btn btn-danger">🗑️ Deletar</button>
          </div>
        </div>
        <div class="tool-section">
          <label class="tool-label">Gerenciar Usuário:</label>
          <div class="tool-row">
            <input id="userNick" type="text" class="tool-input" placeholder="Nickname" maxlength="50" />
            <button id="blockBtn" type="button" class="btn btn-danger">🚫 Bloquear</button>
            <button id="unblockBtn" type="button" class="btn btn-outline">✅ Desbloquear</button>
          </div>
        </div>
        <div id="adminStatus" class="status-message"></div>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <aside class="sidebar">
    <div class="chat-card">
      <div class="chat-header">👥 Usuários Online</div>
      <div id="userList" class="chat-messages"><div class="loading">Carregando usuários...</div></div>
    </div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos principais
    const publicForm = document.getElementById('publicForm');
    const publicInput = document.getElementById('publicInput');
    const publicMessages = document.getElementById('publicMessages');
    const typingStatus = document.getElementById('typingStatus');
    const adminToggle = document.getElementById('adminToggle');
    const adminCard = document.getElementById('adminCard');
    const adminForm = document.getElementById('adminForm');
    const adminInput = document.getElementById('adminInput');
    const adminMessages = document.getElementById('adminMessages');
    const userList = document.getElementById('userList');

    // Variáveis para gerenciar status de digitação
    let typingTimer = null;
    let isTyping = false;

    // Função para formatar links/GIFs
    function formatMessage(text) {
        const urlRegex = /(https?:\/\/[^\s<]+)/g;
        const gifDomains = ['tenor.com', 'media1.tenor.com', 'giphy.com', 'media.giphy.com'];
        return text.replace(urlRegex, function(url) {
            const isGif = gifDomains.some(domain => url.includes(domain)) &&
                         (url.includes('.gif') || url.includes('/gif'));
            if (isGif) {
                return `<img src="${url}" class="chat-gif" alt="GIF">`;
            }
            return `<a href="${url}" target="_blank" rel="noopener">${url}</a>`;
        });
    }

    // Adiciona mensagem no DOM
    function addMessage(container, msg) {
        const div = document.createElement('div');
        div.className = 'message';
        const formattedMessage = msg.message.includes('http')
            ? formatMessage(msg.message)
            : msg.message;
        div.innerHTML = `
            <div class="message-meta">
                <span class="message-author">${msg.nickname}</span>
                <span class="message-time">${msg.created_at}</span>
            </div>
            <div class="message-content">${formattedMessage}</div>
        `;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    // Envia mensagem via AJAX
    function sendMessage(inputEl, channel) {
        const text = inputEl.value.trim();
        if (!text) return;
        fetch('Api/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                message: text,
                channel: channel
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                addMessage(
                    channel === 'admin' ? adminMessages : publicMessages,
                    {
                        nickname: '<?= htmlspecialchars($nickname) ?>',
                        message: text,
                        created_at: new Date().toLocaleTimeString()
                    }
                );
                inputEl.value = '';
            } else {
                throw new Error(data.error || 'Erro ao enviar mensagem');
            }
        })
        .catch(err => {
            alert(err.message || 'Erro ao enviar mensagem');
        });
    }

    // Funções para gerenciar status de digitação
    function setTypingStatus(status) {
        fetch('Api/typing_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'typing=' + (status ? 1 : 0)
        }).catch(err => {
            console.error('Erro ao atualizar status de digitação:', err);
        });
    }

    function fetchTypingStatus() {
        fetch('Api/typing_status.php')
        .then(res => res.json())
        .then(users => {
            if (users.length > 0) {
                const userText = users.length === 1 
                    ? users[0] + ' está digitando...' 
                    : users.slice(0, -1).join(', ') + ' e ' + users[users.length - 1] + ' estão digitando...';
                typingStatus.innerHTML = '💬 ' + userText;
                typingStatus.style.display = 'block';
            } else {
                typingStatus.style.display = 'none';
            }
        })
        .catch(err => {
            console.error('Erro ao buscar status de digitação:', err);
        });
    }

    // Eventos de digitação no input público
    publicInput.addEventListener('input', function() {
        if (!isTyping) {
            isTyping = true;
            setTypingStatus(true);
        }
        
        // Limpar timer anterior
        clearTimeout(typingTimer);
        
        // Definir novo timer para parar digitação após 2s
        typingTimer = setTimeout(() => {
            isTyping = false;
            setTypingStatus(false);
        }, 2000);
    });

    // Eventos de envio
    publicForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Parar indicação de digitação ao enviar mensagem
        clearTimeout(typingTimer);
        isTyping = false;
        setTypingStatus(false);
        sendMessage(publicInput, 'public');
    });
    if (adminForm) {
        adminForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessage(adminInput, 'admin');
        });
    }

    // Busca e exibe mensagens do canal
    function fetchMessages() {
        fetch('Api/get_messages.php?channel=public')
        .then(res => res.json())
        .then(data => {
            publicMessages.innerHTML = '';
            if (data.length === 0) {
                publicMessages.innerHTML = '<div class="loading">Nenhuma mensagem encontrada.</div>';
            } else {
                data.forEach(msg => addMessage(publicMessages, msg));
            }
        })
        .catch(err => {
            publicMessages.innerHTML = '<div class="loading">Erro ao buscar mensagens!</div>';
        });

        // Admin
        if (adminCard && adminToggle && adminToggle.checked) {
            fetch('/Api/get_messages.php?channel=admin')
            .then(res => res.json())
            .then(data => {
                adminMessages.innerHTML = '';
                if (data.length === 0) {
                    adminMessages.innerHTML = '<div class="loading">Nenhuma mensagem (admin).</div>';
                } else {
                    data.forEach(msg => addMessage(adminMessages, msg));
                }
            })
            .catch(err => {
                adminMessages.innerHTML = '<div class="loading">Erro ao buscar mensagens admin!</div>';
            });
        }
    }

    // Busca e exibe usuários online
    function fetchUsers() {
        fetch('Api/get_users.php')
        .then(res => res.json())
        .then(data => {
            userList.innerHTML = '';
            data.forEach(u => {
                if (u.blocked == 1) return;
                const div = document.createElement('div');
                div.className = 'user-item';
                if (u.is_admin == 1) div.classList.add('admin');
                if (u.is_online == 1) div.classList.add('online');
                else div.classList.add('offline');
                const status = u.is_online == 1 ? '✅' : '⚪';
                const admin = u.is_admin == 1 ? '👑' : '';
                div.innerHTML = `<span class="user-status">${status}</span> ${admin} ${u.nickname}`;
                userList.appendChild(div);
            });
            if (userList.innerHTML.trim() === '') {
                userList.innerHTML = '<div class="loading">Nenhum usuário online.</div>';
            }
        })
        .catch(err => {
            userList.innerHTML = '<div class="loading">Erro ao buscar usuários!</div>';
        });
    }

    // Toggle admin
    if (adminToggle) {
        adminToggle.addEventListener('change', () => {
            adminCard.style.display = adminToggle.checked ? 'block' : 'none';
            if (adminToggle.checked) fetchMessages();
        });
    }

    // Admin tools
    if (document.getElementById('deleteBtn')) {
        document.getElementById('deleteBtn').onclick = function() {
            const id = document.getElementById('deleteId').value;
            if (!id) return alert('Informe o ID da mensagem');
            fetch('Api/admin_delete_message.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    adminStatus('Mensagem deletada!', true);
                    fetchMessages();
                } else {
                    throw new Error(data.error || 'Erro ao deletar mensagem');
                }
            })
            .catch(err => {
                adminStatus(err.message, false);
            });
        };
    }
    if (document.getElementById('blockBtn')) {
        document.getElementById('blockBtn').onclick = function() {
            const nick = document.getElementById('userNick').value;
            if (!nick) return alert('Informe o nickname');
            fetch('Api/admin_block_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=block&nickname=' + encodeURIComponent(nick)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    adminStatus('Usuário bloqueado!', true);
                    fetchUsers();
                } else {
                    throw new Error(data.error || 'Erro ao bloquear usuário');
                }
            })
            .catch(err => {
                adminStatus(err.message, false);
            });
        };
    }
    if (document.getElementById('unblockBtn')) {
        document.getElementById('unblockBtn').onclick = function() {
            const nick = document.getElementById('userNick').value;
            if (!nick) return alert('Informe o nickname');
            fetch('Api/admin_block_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=unblock&nickname=' + encodeURIComponent(nick)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    adminStatus('Usuário desbloqueado!', true);
                    fetchUsers();
                } else {
                    throw new Error(data.error || 'Erro ao desbloquear usuário');
                }
            })
            .catch(err => {
                adminStatus(err.message, false);
            });
        };
    }

    // Feedback admin
    function adminStatus(msg, success) {
        const el = document.getElementById('adminStatus');
        el.textContent = msg;
        el.className = 'status-message ' + (success ? 'status-success' : 'status-error');
        el.style.display = 'block';
    }

    // Atualização automática
    setInterval(fetchMessages, 1000);
    setInterval(fetchUsers, 500);
    setInterval(fetchTypingStatus, 1000);
    fetchMessages();
    fetchUsers();
    fetchTypingStatus();
});
</script>
</body>
</html>