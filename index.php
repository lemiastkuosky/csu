<?php
session_start();
define('ROOT_PATH', __DIR__);
require_once 'config/db.php';

$pagina = isset($_GET['p']) ? $_GET['p'] : 'mapa'; 
$paginas_publicas = ['login', 'cadastro', 'processa_login', 'processa_cadastro'];

// Lógica de Redirecionamento
if (!isset($_SESSION['usuario_id'])) {
    if (!in_array($pagina, $paginas_publicas)) { header("Location: index.php?p=login"); exit(); }
} else {
    // Se logado, impede acesso direto a login/cadastro
    if ($pagina == 'login' || $pagina == 'cadastro') { header("Location: index.php?p=mapa"); exit(); }
}
if ($pagina == 'login' && isset($_SESSION['usuario_id'])) $pagina = 'mapa';

$is_admin = 0;
if(isset($_SESSION['usuario_id'])) {
    $id_check = $_SESSION['usuario_id'];
    if(isset($conn)) {
        $res_adm = $conn->query("SELECT is_admin FROM usuarios WHERE id = $id_check");
        if($res_adm) { $row_adm = $res_adm->fetch_assoc(); $is_admin = $row_adm['is_admin'] ?? 0; }
    }
}

// Verifica se está na página de login ou cadastro (páginas públicas)
$is_public_page = in_array($pagina, $paginas_publicas);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Street Car Underground</title>
    
    <?php if (!$is_public_page): ?>
        <link rel="stylesheet" href="assets/css/style.css">
    <?php else: ?>
        <link rel="stylesheet" href="assets/css/login-style.css">
    <?php endif; ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* Estilos base (Mantidos) */
        body { background: #000; color: #fff; font-family: 'Arial', sans-serif; overflow: hidden; margin: 0; padding: 0; }

        /* Estilos de Debug (Mantidos) */
        .debug-modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); z-index: 20000;
            display: flex; justify-content: center; align-items: center;
            backdrop-filter: blur(5px);
        }
        .debug-content {
            background: #111; border: 2px solid #d32f2f; padding: 20px;
            border-radius: 10px; width: 350px; text-align: center;
            box-shadow: 0 0 50px rgba(211, 47, 47, 0.5);
        }
        .debug-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 15px 0; }
        .debug-sep { color: #777; font-size: 10px; text-transform: uppercase; border-bottom: 1px solid #333; margin: 15px 0 5px 0; }
        .d-btn { 
            background: #111; border: 1px solid #444; color: #aaa; padding: 10px; border-radius: 5px; 
            cursor: pointer; text-decoration: none; display: flex; flex-direction: column; 
            align-items: center; gap: 5px; font-size: 10px; font-weight: bold; 
        }
        .d-btn:hover { background: #222; color: white; border-color: #fff; }
        .btn-close-debug { background: transparent; border: 1px solid #555; color: #777; width: 100%; padding: 10px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .btn-close-debug:hover { background: #333; color: white; }
    </style>
</head>
<body>

    <?php if (isset($_SESSION['usuario_id']) && file_exists('includes/topbar.php')) include 'includes/topbar.php'; ?>

    <?php if (isset($_SESSION['usuario_id']) && file_exists('includes/right_chat.php')) include 'includes/right_chat.php'; ?>

    <?php if (isset($_SESSION['usuario_id']) && !in_array($pagina, $paginas_publicas)): ?>
        <div class="background-layer" style="position:fixed; top:0; left:0; width:100%; height:100%; z-index:1;">
            <?php if(file_exists('pages/mapa.php')) include 'pages/mapa.php'; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['usuario_id']) && $pagina != 'mapa' && !in_array($pagina, $paginas_publicas)): ?>
        <div class="global-modal-wrapper" style="display:flex !important; position:fixed; top:0; left:0; width:100%; height:100%; z-index:2000; justify-content:center; align-items:center; background:rgba(0,0,0,0.8); backdrop-filter:blur(3px);">
            <div class="modal-content" style="background:#111; padding:20px; border:1px solid #333; border-radius:10px; color:white; max-width:90%; max-height:90%; overflow-y:auto; position:relative; z-index:2001;">
                <?php
                    $arquivo = "pages/{$pagina}.php";
                    if (file_exists($arquivo)) include $arquivo;
                    else echo "<div style='padding:50px; text-align:center;'><h1>404</h1><a href='index.php?p=mapa' class='btn-red'>VOLTAR</a></div>";
                ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (in_array($pagina, $paginas_publicas)) { $arquivo = "pages/{$pagina}.php"; if (file_exists($arquivo)) include $arquivo; } ?>

    <?php if (isset($_SESSION['usuario_id']) && file_exists('includes/bottombar.php')) include 'includes/bottombar.php'; ?>

    <?php if($is_admin == 1): ?>
    <div id="debugModal" class="debug-modal" style="display:none !important;">
        <div class="debug-content">
            <div style="font-family:'Oswald',sans-serif; font-size:20px; color:#d32f2f; margin-bottom:10px;">GOD MODE</div>
            
            <div class="debug-sep">FERRAMENTAS</div>
            <div class="debug-grid" style="grid-template-columns: 1fr;">
                <button onclick="if(window.toggleMapEditor) { window.toggleMapEditor(); toggleDebug(); } else { alert('Vá para o Mapa primeiro!'); }" class="d-btn" style="border-color:#9b59b6; color:#9b59b6;">
                    <i class="fas fa-arrows-alt"></i> ATIVAR EDITOR DE MAPA
                </button>
            </div>

            <div class="debug-sep">CONTROLE DE HORA</div>
            <div class="debug-grid">
                <button onclick="window.forceTime('dia'); toggleDebug();" class="d-btn" style="border-color:#f1c40f; color:#f1c40f;"><i class="fas fa-sun"></i> DIA</button>
                <button onclick="window.forceTime('noite'); toggleDebug();" class="d-btn" style="border-color:#3498db; color:#3498db;"><i class="fas fa-moon"></i> NOITE</button>
            </div>
            
            <div class="debug-sep">CLIMA</div>
            <div class="debug-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                <button onclick="window.forceWeather('limpo'); toggleDebug();" class="d-btn" style="border-color:#f1c40f;"><i class="fas fa-sun"></i> SOL</button>
                <button onclick="window.forceWeather('chuva'); toggleDebug();" class="d-btn" style="border-color:#3498db;"><i class="fas fa-cloud-showers-heavy"></i> CHUVA</button>
                <button onclick="window.forceWeather('tempestade'); toggleDebug();" class="d-btn" style="border-color:#fff; color:#fff;"><i class="fas fa-bolt"></i> RAIO</button>
            </div>
            <div class="debug-grid">
                 <button onclick="window.forceWeather('neve'); toggleDebug();" class="d-btn" style="border-color:#fff; color:#fff;"><i class="fas fa-snowflake"></i> NEVE</button>
                 <button onclick="window.forceWeather('nublado'); toggleDebug();" class="d-btn" style="border-color:#aaa; color:#aaa;"><i class="fas fa-cloud"></i> NUBLADO</button>
            </div>

            <div class="debug-sep">JOGADOR</div>
            <div class="debug-grid">
                <a href="pages/processa_debug.php?acao=grana" class="d-btn" style="border-color:#2ecc71; color:#2ecc71;">+ R$ 5K</a>
                <a href="pages/processa_debug.php?acao=nivel" class="d-btn" style="border-color:#f1c40f; color:#f1c40f;">+1 NÍVEL</a>
                <a href="pages/processa_debug.php?acao=radio" class="d-btn" style="border-color:#e67e22; color:#e67e22;">RÁDIO</a>
                 <a href="pages/processa_debug.php?acao=reset_p" class="d-btn" style="border-color:#e74c3c; color:#e74c3c;">RESETAR</a>
            </div>

            <button onclick="toggleDebug()" class="btn-close-debug">FECHAR</button>
        </div>
    </div>

    <script>
        function toggleDebug() {
            const modal = document.getElementById('debugModal');
            if (modal) {
                if (modal.style.display === 'none' || modal.style.display === '') {
                    modal.style.display = 'flex';
                } else {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
    <?php endif; ?>

    <?php if (!$is_public_page): ?>
        <audio id="audio-bgm" loop><source src="assets/sounds/bgm.mp3" type="audio/mpeg"></audio>
        <audio id="audio-click"><source src="assets/sounds/click.mp3" type="audio/mpeg"></audio>
        <audio id="audio-hover"><source src="assets/sounds/hover.mp3" type="audio/mpeg"></audio>
        <script src="assets/js/audio.js"></script>
    <?php endif; ?>

</body>
</html>