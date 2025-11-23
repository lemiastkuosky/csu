// assets/js/spa.js

// Função para abrir qualquer página em um modal
function openPage(page_name) {
    const modal = document.getElementById('globalModal');
    const content = document.getElementById('modalContent');
    const url = 'content_loader.php?p=' + page_name;
    
    // 1. Mostrar o modal
    modal.style.display = 'flex';
    content.innerHTML = '<div style="padding: 50px; text-align: center; color: #fff;"><i class="fas fa-circle-notch fa-spin"></i> A CARREGAR CONTEÚDO...</div>';
    
    // 2. Fazer o pedido AJAX
    fetch(url)
        .then(response => response.text())
        .then(html => {
            // 3. Injetar o conteúdo
            content.innerHTML = html;
            
            // 4. Se a página for a loja, atualiza os gatilhos (necessário para o AJAX)
            if(page_name === 'loja') {
                updateShopTriggers();
            }
        })
        .catch(error => {
            content.innerHTML = '<div style="color:red; padding:20px;">ERRO AO CARREGAR: ' + error + '</div>';
        });
}

function closeModal() {
    const modal = document.getElementById('globalModal');
    modal.style.display = 'none';
}

// Função placeholder para atualizar a loja (se necessário)
function updateShopTriggers() {
    // Se a página da loja tiver algum script específico para re-executar, faríamos aqui.
    // Ex: Adicionar o evento de clique nos botões "Comprar" da loja.
    console.log("Loja carregada no modal. Pronta para interagir.");
}