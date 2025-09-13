// Seleção de elementos

const publicForm = document.getElementById('publicForm');

const publicInput = document.getElementById('publicInput');

const publicMessages = document.getElementById('publicMessages');



const adminToggle = document.getElementById('adminToggle');

const adminCard = document.getElementById('adminCard');

const adminForm = document.getElementById('adminForm');

const adminInput = document.getElementById('adminInput');

const adminMessages = document.getElementById('adminMessages');



// Função para adicionar mensagens na tela

function addMessage(container, msg) {

    container.innerHTML += `<div><b>${msg.nickname}:</b> ${msg.message} <small>(${msg.created_at})</small></div>`;

    container.scrollTop = container.scrollHeight;

}



// Envio de mensagens

function sendMessage(inputEl, channel) {

    const text = inputEl.value.trim();

    if (!text) return;



    fetch('api/send_message.php', {

        method: 'POST',

        headers: {'Content-Type':'application/json'},

        body: JSON.stringify({message:text, channel})

    })

    .then(res => res.json())

    .then(data => {

        if (data.success) {

            addMessage(channel === 'admin' ? adminMessages : publicMessages, {nickname:'Você', message:text, created_at: new Date().toLocaleTimeString()});

            inputEl.value = '';

        } else {

            alert(data.error || 'Erro ao enviar mensagem');

        }

    });

}



// Eventos de envio

publicForm.addEventListener('submit', e => {

    e.preventDefault();

    sendMessage(publicInput, 'public');

});



if (adminForm) {

    adminForm.addEventListener('submit', e => {

        e.preventDefault();

        sendMessage(adminInput, 'admin');

    });

}



// Atualização automática

function fetchMessages() {

    fetch('api/get_messages.php?channel=public')

    .then(res => res.json())

    .then(data => {

        publicMessages.innerHTML = '';

        data.forEach(msg => addMessage(publicMessages, msg));

    });



    if (adminCard && adminToggle.checked) {

        fetch('api/get_messages.php?channel=admin')

        .then(res => res.json())

        .then(data => {

            adminMessages.innerHTML = '';

            data.forEach(msg => addMessage(adminMessages, msg));

        });

    }

}



// Atualiza usuários online

function fetchUsers() {

    fetch('api/get_users.php')

    .then(res => res.json())

    .then(data => {

        const userList = document.getElementById('userList');

        userList.innerHTML = '';

        data.forEach(u => {

            const status = u.blocked == 1 ? '🚫' : '✅';

            const admin = u.is_admin == 1 ? '👑' : '';

            userList.innerHTML += `<div>${status} ${admin} ${u.nickname}</div>`;

        });

    });

}



// Toggle admin

if (adminToggle) {

    adminToggle.addEventListener('change', () => {

        adminCard.style.display = adminToggle.checked ? 'block' : 'none';

        if (adminToggle.checked) fetchMessages();

    });

}



// Intervalos

setInterval(fetchMessages, 3000);

setInterval(fetchUsers, 5000);

fetchMessages();

fetchUsers();



// Admin tools

if (document.getElementById('deleteBtn')) {

    document.getElementById('deleteBtn').onclick = function() {

        const id = document.getElementById('deleteId').value;

        fetch('api/admin_delete_message.php', {

            method: 'POST',

            headers: {'Content-Type':'application/x-www-form-urlencoded'},

            body: 'id=' + encodeURIComponent(id)

        })

        .then(res => res.json())

        .then(data => {

            if (data.success) {

                document.getElementById('adminStatus').textContent = 'Mensagem deletada!';

                fetchMessages();

            } else {

                alert(data.error || 'Erro ao deletar mensagem');

            }

        });

    };

}

if (document.getElementById('blockBtn')) {

    document.getElementById('blockBtn').onclick = function() {

        const nick = document.getElementById('userNick').value;

        fetch('api/admin_block_user.php', {

            method: 'POST',

            headers: {'Content-Type':'application/x-www-form-urlencoded'},

            body: 'action=block&nickname=' + encodeURIComponent(nick)

        })

        .then(res => res.json())

        .then(data => {

            if (data.success) {

                document.getElementById('adminStatus').textContent = 'Usuário bloqueado!';

                fetchUsers();

            } else {

                alert(data.error || 'Erro ao bloquear');

            }

        });

    };

}

if (document.getElementById('unblockBtn')) {

    document.getElementById('unblockBtn').onclick = function() {

        const nick = document.getElementById('userNick').value;

        fetch('api/admin_block_user.php', {

            method: 'POST',

            headers: {'Content-Type':'application/x-www-form-urlencoded'},

            body: 'action=unblock&nickname=' + encodeURIComponent(nick)

        })

        .then(res => res.json())

        .then(data => {

            if (data.success) {

                document.getElementById('adminStatus').textContent = 'Usuário desbloqueado!';

                fetchUsers();

            } else {

                alert(data.error || 'Erro ao desbloquear');

            }

        });

    };


}