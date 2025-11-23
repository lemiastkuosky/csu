<style>
    /* Botão Flutuante */
    .chat-toggle-btn {
        position: fixed; bottom: 20px; right: 20px;
        width: 50px; height: 50px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        border-radius: 50%; color: white;
        display: flex; justify-content: center; align-items: center;
        font-size: 24px; cursor: pointer; z-index: 10000;
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        border: 2px solid rgba(255,255,255,0.2);
        transition: transform 0.2s;
    }
    .chat-toggle-btn:hover { transform: scale(1.1); }

    /* Janela do Chat */
    .chat-window {
        position: fixed; bottom: 80px; right: 20px;
        width: 320px; height: 400px;
        background: rgba(15, 15, 15, 0.95); /* Mais escuro pra leitura */
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        display: none; flex-direction: column; z-index: 10000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.9);
        overflow: hidden;
    }
    .chat-window.open { display: flex; animation: slideUp 0.3s ease; }

    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* Header */
    .chat-header {
        padding: 10px 15px; background: rgba(255,255,255,0.08);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        font-family: 'Oswald', sans-serif; font-size: 14px; color: #fff;
        display: flex; justify-content: space-between; align-items: center;
    }

    /* Lista de Mensagens */
    .chat-messages {
        flex: 1; padding: 10px; overflow-y: auto;
        display: flex; flex-direction: column; gap: 6px;
        scroll-behavior: smooth;
    }
    /* Scrollbar bonita */
    .chat-messages::-webkit-scrollbar { width: 5px; }
    .chat-messages::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
    .chat-messages::-webkit-scrollbar-track { background: transparent; }

    /* Balões */
    .msg-line { 
        font-size: 13px; line-height: 1.4; color: #ddd; font-family: 'Arial', sans-serif; 
        padding: 4px 8px; border-radius: 4px; background: rgba(255,255,255,0.03);
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }

    .msg-user { font-weight: bold; color: #3498db; margin-right: 5px; font-size: 12px; }
    .msg-user.admin { color: #f1c40f; text-shadow: 0 0 5px rgba(241, 196, 15, 0.3); } 
    .msg-text { color: #fff; word-wrap: break-word; }
    .msg-time { font-size: 9px; color: #777; margin-left: 5px; float: right; margin-top: 3px; }

    /* Input */
    .chat-input-area {
        padding: 10px; background: rgba(0,0,0,0.6);
        border-top: 1px solid rgba(255,255,255,0.1);
        display: flex; gap: 5px;
    }
    .chat-input {
        flex: 1; background: rgba(255,255,255,0.1); border: 1px solid transparent;
        color: white; padding: 8px; border-radius: 4px; outline: none; transition: 0.3s;
    }
    .chat-input:focus { border-color: #3498db; background: rgba(255,255,255,0.15); }
    
    .chat-send-btn {
        background: #2980b9; border: none; color: white;
        padding: 0 15px; border-radius: 4px; cursor: pointer; transition: 0.2s;
    }
    .chat-send-btn:hover { background: #3498db; }
</style>

<div class="chat-toggle-btn" onclick="toggleChat()">
    <i class="fas fa-comments"></i>
</div>

<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <span>CHAT EM TEMPO REAL</span>
        <i class="fas fa-times" onclick="toggleChat()" style="cursor:pointer; color:#aaa;"></i>
    </div>
    
    <div class="chat-messages" id="chatList">
        </div>

    <div class="chat-input-area">
        <input type="text" id="chatInput" class="chat-input" placeholder="Digite..." maxlength="200" autocomplete="off">
        <button class="chat-send-btn" onclick="sendMsg()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<script>
    let chatOpen = false;
    let lastMsgId = 0; // O segredo do tempo real
    let pollingInterval = null;

    const chatWindow = document.getElementById('chatWindow');
    const chatList = document.getElementById('chatList');
    const chatInput = document.getElementById('chatInput');

    function toggleChat() {
        chatOpen = !chatOpen;
        if(chatOpen) {
            chatWindow.classList.add('open');
            loadMessages(); // Chama imediatamente
            
            // Inicia o loop rápido (1 segundo)
            if(!pollingInterval) pollingInterval = setInterval(loadMessages, 1000);
            
            // Foca no input
            setTimeout(() => chatInput.focus(), 100);
        } else {
            chatWindow.classList.remove('open');
            // Para o loop para economizar recurso quando fechado
            if(pollingInterval) { clearInterval(pollingInterval); pollingInterval = null; }
        }
    }

    function sendMsg() {
        const msg = chatInput.value;
        if(msg.trim() === '') return;

        const formData = new FormData();
        formData.append('msg', msg);

        // Envia e limpa o input imediatamente (Sensação instantânea)
        chatInput.value = '';
        
        fetch('api/chat_send.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            loadMessages(); // Força atualização imediata após envio
        });
    }

    chatInput.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendMsg();
        }
    });

    function loadMessages() {
        // Envia o ID da última mensagem que temos
        fetch('api/chat_get.php?last_id=' + lastMsgId)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                // Verifica se o usuário está lá embaixo no scroll antes de adicionar
                const isScrolledToBottom = chatList.scrollHeight - chatList.clientHeight <= chatList.scrollTop + 50;

                data.forEach(item => {
                    // Atualiza o último ID conhecido
                    if(parseInt(item.id) > lastMsgId) lastMsgId = parseInt(item.id);

                    const adminClass = item.is_admin == 1 ? 'admin' : '';
                    
                    // Cria o elemento HTML
                    const div = document.createElement('div');
                    div.className = 'msg-line';
                    div.innerHTML = `
                        <span class="msg-user ${adminClass}">${item.nome}:</span>
                        <span class="msg-text">${item.mensagem}</span>
                        <span class="msg-time">${item.hora_formatada}</span>
                    `;
                    
                    // Adiciona na lista (APPEND, não substitui)
                    chatList.appendChild(div);
                });

                // Se for a primeira carga OU se o usuário estava no final, rola a tela
                if (lastMsgId === data[data.length-1].id || isScrolledToBottom) {
                    scrollToBottom();
                }
            }
        })
        .catch(err => console.error(err));
    }

    function scrollToBottom() {
        chatList.scrollTop = chatList.scrollHeight;
    }
</script>