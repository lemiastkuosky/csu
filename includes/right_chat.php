<style>
    /* --- BOTÃO "C" DISCRETO --- */
    .chat-trigger {
        position: fixed; 
        bottom: 50px; /* AJUSTADO: Alto o suficiente para não bater na barra de baixo */
        right: 10px;  
        
        width: 35px; 
        height: 35px;
        
        border-radius: 4px; 
        background: rgba(0, 0, 0, 0.8); 
        border: 1px solid rgba(255, 255, 255, 0.8);
        color: #fff;
        
        font-family: 'Oswald', sans-serif;
        font-weight: bold;
        font-size: 20px;
        line-height: 1;
        
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.3);
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        
        display: flex; justify-content: center; align-items: center;
        cursor: pointer; 
        z-index: 9000;
        transition: all 0.2s ease;
    }
    
    .chat-trigger:hover { 
        background: #fff; color: #000;
        box-shadow: 0 0 15px rgba(255, 255, 255, 0.8);
        text-shadow: none; transform: scale(1.1);
    }
    
    .chat-notification {
        position: absolute; top: -3px; right: -3px;
        width: 8px; height: 8px;
        background: #ff0000; border: 1px solid #000;
        display: none;
    }
    .chat-trigger.has-new .chat-notification { display: block; }

    /* --- JANELA DO CHAT --- */
    .chat-sidebar {
        position: fixed; 
        bottom: 125px; /* Abre acima do botão (80 + 35 + 10) */
        right: 20px;
        width: 300px; 
        height: auto; max-height: 350px;
        
        background: rgba(5, 5, 5, 0.98);
        border: 1px solid #fff;
        border-radius: 2px;
        
        display: flex; flex-direction: column;
        z-index: 9000;
        
        transform: translateX(20px); opacity: 0; pointer-events: none;
        transition: all 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        box-shadow: 0 10px 50px rgba(0,0,0,1);
    }
    
    .chat-sidebar.active { transform: translateX(0); opacity: 1; pointer-events: auto; }

    /* Header */
    .chat-header {
        padding: 8px 12px; background: rgba(255,255,255,0.05);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        display: flex; justify-content: space-between; align-items: center;
        font-family: 'Oswald', sans-serif; font-size: 12px; letter-spacing: 1px; color: #fff;
    }

    /* Lista */
    .chat-messages {
        flex: 1; padding: 10px; overflow-y: auto;
        display: flex; flex-direction: column; gap: 6px;
        scroll-behavior: smooth;
        height: 200px; min-height: 200px;
    }
    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-thumb { background: #fff; border-radius: 0; }
    .chat-messages::-webkit-scrollbar-track { background: #222; }

    /* Mensagem */
    .msg-item {
        font-size: 11px; font-family: 'Arial', sans-serif; line-height: 1.4;
        color: #ccc; word-wrap: break-word;
        padding: 4px; border-bottom: 1px solid rgba(255,255,255,0.05);
        animation: fadeUp 0.3s ease forwards;
    }
    @keyframes fadeUp { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:translateY(0); } }

    .msg-meta { margin-bottom: 2px; display: flex; align-items: center; gap: 6px; }
    .user-badge { background: #fff; color: #000; font-size: 9px; padding: 0px 3px; border-radius: 0; font-weight: bold; }
    .user-name { font-weight: bold; color: #ccc; cursor: pointer; transition: 0.2s; }
    .user-name:hover { color: #fff; text-shadow: 0 0 5px #fff; }
    .user-name.admin { color: #f1c40f; text-shadow: none; }
    .msg-time { font-size: 9px; color: #666; margin-left: auto; }
    .msg-content { color: #ddd; }

    /* Input */
    .chat-input-box {
        padding: 8px; background: rgba(0,0,0,0.8);
        border-top: 1px solid rgba(255,255,255,0.1); display: flex; gap: 0;
    }
    .chat-input {
        flex: 1; background: rgba(255,255,255,0.1); border: 1px solid #333; border-right: none;
        color: #fff; padding: 6px; font-size: 11px; border-radius: 0; outline: none;
        transition: 0.2s;
    }
    .chat-input:focus { border-color: #fff; background: rgba(255,255,255,0.15); }
    .chat-send {
        background: #fff; border: 1px solid #fff; color: #000;
        width: 30px; border-radius: 0; cursor: pointer; transition: 0.2s; font-weight: bold; font-size: 12px;
    }
    .chat-send:hover { background: #ccc; border-color: #ccc; }
</style>

<div class="chat-trigger" onclick="toggleRightChat()" title="Chat Global">
    C
    <div class="chat-notification"></div>
</div>

<div class="chat-sidebar" id="rightChat">
    <div class="chat-header">
        <strong>SALA GLOBAL</strong>
        <i class="fas fa-times" onclick="toggleRightChat()" style="cursor:pointer;"></i>
    </div>

    <div class="chat-messages" id="chatList"></div>

    <div class="chat-input-box">
        <input type="text" id="chatMsg" class="chat-input" placeholder="..." autocomplete="off">
        <button class="chat-send" onclick="sendMessage()"><i class="fas fa-arrow-right"></i></button>
    </div>
</div>

<script>
    let chatLastId = 0;
    let isChatOpen = false;
    let isFetching = false; 

    const chatBox = document.getElementById('rightChat');
    const chatList = document.getElementById('chatList');
    const chatInput = document.getElementById('chatMsg');
    const chatBtn = document.querySelector('.chat-trigger');

    function toggleRightChat() {
        isChatOpen = !isChatOpen;
        if(isChatOpen) {
            chatBox.classList.add('active');
            chatBtn.classList.remove('has-new');
            setTimeout(() => chatInput.focus(), 100);
            scrollToBottom();
        } else {
            chatBox.classList.remove('active');
        }
    }

    function sendMessage() {
        const msg = chatInput.value.trim();
        if(!msg) return;

        const formData = new FormData();
        formData.append('msg', msg);
        chatInput.value = '';

        fetch('api/chat_send.php', { method: 'POST', body: formData })
            .then(() => fetchMessages(true));
    }

    chatInput.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') sendMessage();
    });

    function fetchMessages(forceScroll = false) {
        if (isFetching) return;
        isFetching = true;

        fetch('api/chat_get.php?last_id=' + chatLastId)
            .then(res => res.json())
            .then(data => {
                if(data.length > 0) {
                    if(!isChatOpen && chatLastId > 0) {
                        chatBtn.classList.add('has-new');
                    }

                    const userAtBottom = (chatList.scrollTop + chatList.clientHeight >= chatList.scrollHeight - 50);

                    data.forEach(item => {
                        if (document.getElementById('msg-' + item.id)) return;
                        if(parseInt(item.id) > chatLastId) chatLastId = parseInt(item.id);
                        
                        const adminClass = item.is_admin == 1 ? 'admin' : '';
                        
                        const html = `
                            <div class="msg-item" id="msg-${item.id}">
                                <div class="msg-meta">
                                    <span class="user-badge">${item.nivel}</span>
                                    <span class="user-name ${adminClass}">${item.nome}</span>
                                    <span class="msg-time">${item.hora}</span>
                                </div>
                                <div class="msg-content">${item.mensagem}</div>
                            </div>
                        `;
                        chatList.insertAdjacentHTML('beforeend', html);
                    });

                    if(userAtBottom || forceScroll || chatLastId === data[data.length-1].id) {
                        scrollToBottom();
                    }
                }
                isFetching = false;
            })
            .catch(err => {
                isFetching = false;
            });
    }

    function scrollToBottom() {
        chatList.scrollTop = chatList.scrollHeight;
    }

    setInterval(fetchMessages, 1000);
    fetchMessages(true); 
</script>