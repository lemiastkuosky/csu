<?php
// includes/bottombar.php

// LISTA DE NOTÍCIAS
$mensagens = [
    "<span style='color:#f1c40f'>[DICA]</span> O Clima influencia na aderência. Cuidado com a chuva!",
    "<span style='color:#e74c3c'>[ALERTA]</span> Blitz policial detectada na saída da Zona Norte.",
    "<span style='color:#3498db'>[MERCADO]</span> Peças de Turbo estão com 10% de desconto hoje.",
    "<span style='color:#2ecc71'>[ONLINE]</span> <strong>DK_Drift</strong> acabou de entrar no servidor.",
    "<span style='color:#9b59b6'>[EVENTO]</span> Bônus de XP na Agência de Empregos ativo!",
    "<span style='color:#fff'>[SISTEMA]</span> Bem-vindo ao Street Car Underground v1.0"
];

$texto_final = implode(" &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;&nbsp; ", $mensagens);
?>

<div id="newsBar" class="bottom-news-bar">
    
    <div class="news-label">
        <i class="fas fa-broadcast-tower" style="font-size: 10px; margin-right: 5px;"></i> LIVE
    </div>

    <div class="news-content">
        <div class="news-scroller">
            <?php echo $texto_final; ?> &nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $texto_final; ?>
        </div>
    </div>

    <div class="news-close" onclick="toggleNews(false)" title="Fechar Notícias">
        <i class="fas fa-times"></i>
    </div>

</div>

<div id="newsRestore" class="news-restore-btn" onclick="toggleNews(true)" title="Mostrar Notícias">
    <i class="fas fa-broadcast-tower"></i>
</div>

<style>
    .bottom-news-bar {
        position: fixed; bottom: 0; left: 0; width: 100%; height: 35px;
        background-color: #000; border-top: 1px solid #333;
        display: flex; align-items: center; z-index: 9990;
        font-family: 'Roboto', sans-serif; font-size: 12px; color: white;
        transition: transform 0.3s ease; /* Animação ao fechar */
    }

    .news-label {
        background-color: #c0392b; color: white; height: 100%; padding: 0 15px;
        display: flex; align-items: center; font-weight: bold;
        font-family: 'Oswald', sans-serif; letter-spacing: 1px; z-index: 20;
        box-shadow: 5px 0 10px rgba(0,0,0,0.5);
    }

    .news-content {
        flex: 1; overflow: hidden; position: relative; height: 100%;
        display: flex; align-items: center;
    }

    /* Animação Mais Lenta (85s) */
    .news-scroller {
        display: inline-block; white-space: nowrap; padding-left: 100%;
        animation: scroll-news 85s linear infinite; color: #ccc;
    }

    /* Botão Fechar na Barra */
    .news-close {
        height: 100%; width: 40px; background: #111; color: #555;
        display: flex; justify-content: center; align-items: center;
        cursor: pointer; border-left: 1px solid #333; z-index: 20;
    }
    .news-close:hover { background: #222; color: white; }

    /* Botão Restaurar (Aparece quando fecha a barra) */
    .news-restore-btn {
        position: fixed; bottom: 0; left: 0;
        width: 35px; height: 35px;
        background-color: #c0392b; color: white;
        display: none; /* Começa escondido */
        justify-content: center; align-items: center;
        cursor: pointer; z-index: 9980;
        border-top-right-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }
    .news-restore-btn:hover { background-color: #e74c3c; }

    @keyframes scroll-news {
        0% { transform: translateX(0); }
        100% { transform: translateX(-100%); }
    }
</style>

<script>
    function toggleNews(show) {
        const bar = document.getElementById('newsBar');
        const btn = document.getElementById('newsRestore');

        if (show) {
            // Mostrar Barra, Esconder Botão
            bar.style.display = 'flex';
            btn.style.display = 'none';
            // Salva preferência (opcional)
            localStorage.setItem('csu_news', 'open');
        } else {
            // Esconder Barra, Mostrar Botão
            bar.style.display = 'none';
            btn.style.display = 'flex';
            localStorage.setItem('csu_news', 'closed');
        }
    }

    // Lembrar estado ao carregar (Se o jogador fechou antes, mantém fechado)
    document.addEventListener("DOMContentLoaded", () => {
        const status = localStorage.getItem('csu_news');
        if (status === 'closed') {
            toggleNews(false);
        }
    });
</script>