<?php
// pages/admin.php
if(!isset($_SESSION['usuario_id'])) exit;

// Verifica Admin
$id = $_SESSION['usuario_id'];
$sql = "SELECT is_admin FROM usuarios WHERE id = $id";
$res = $conn->query($sql);
$u = $res->fetch_assoc();

if ($u['is_admin'] != 1) {
    echo "<script>window.location.href='index.php?p=mapa';</script>";
    exit;
}

// Pega o estado atual do jogo para mostrar no painel
$res_conf = $conn->query("SELECT * FROM config_jogo WHERE id = 1");
$config = $res_conf->fetch_assoc();
?>

<style>
    .admin-wrapper {
        padding: 20px; background: #111; min-height: 100vh; color: white;
        padding-bottom: 100px;
    }
    .admin-header {
        border-bottom: 1px solid #333; padding-bottom: 20px; margin-bottom: 30px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .admin-title { font-family: 'Oswald', sans-serif; font-size: 30px; color: var(--red-neon); }
    
    /* Grid do Painel */
    .control-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px; margin-bottom: 40px;
    }

    /* Botões de Debug */
    .debug-btn {
        background: #1c1c1e; border: 1px solid #333; padding: 20px;
        border-radius: 8px; text-align: center; cursor: pointer;
        transition: 0.2s; text-decoration: none; color: white;
        display: flex; flex-direction: column; align-items: center; gap: 10px;
    }
    .debug-btn:hover { transform: scale(1.05); }
    .debug-btn i { font-size: 24px; }
    .debug-btn span { font-size: 12px; font-weight: bold; text-transform: uppercase; }

    /* Cores Específicas */
    .btn-sol { border-color: #f1c40f; color: #f1c40f; }
    .btn-sol:hover { background: #f1c40f; color: black; }

    .btn-nublado { border-color: #95a5a6; color: #95a5a6; }
    .btn-nublado:hover { background: #95a5a6; color: black; }

    .btn-chuva { border-color: #3498db; color: #3498db; }
    .btn-chuva:hover { background: #3498db; color: white; }

    .btn-raio { border-color: #8e44ad; color: #8e44ad; }
    .btn-raio:hover { background: #8e44ad; color: white; }

    .btn-neve { border-color: #fff; color: #fff; }
    .btn-neve:hover { background: #fff; color: black; }

    /* Status Atual */
    .status-bar {
        background: #222; padding: 15px; border-radius: 8px; margin-bottom: 20px;
        border-left: 5px solid var(--red-neon);
    }
</style>

<div class="admin-wrapper">
    
    <div class="admin-header">
        <div class="admin-title"><i class="fas fa-user-shield"></i> GOD MODE</div>
        <a href="index.php?p=mapa" class="btn-red">VOLTAR AO JOGO</a>
    </div>

    <div class="status-bar">
        <h3>ESTADO ATUAL DO MUNDO</h3>
        <p>Clima: <strong><?php echo strtoupper($config['clima_atual']); ?></strong></p>
        <p>Temperatura: <strong><?php echo $config['temperatura']; ?>°C</strong></p>
        <p>Próxima Mudança Automática: <?php echo date('H:i', strtotime($config['proxima_mudanca'])); ?></p>
    </div>

    <h3 style="font-family:'Oswald', sans-serif; margin-bottom:15px;">FORÇAR CLIMA (INSTANTÂNEO)</h3>
    
    <div class="control-grid">
        
        <a href="pages/processa_debug.php?acao=sol" class="debug-btn btn-sol">
            <i class="fas fa-sun"></i>
            <span>SOL / CALOR</span>
        </a>

        <a href="pages/processa_debug.php?acao=nublado" class="debug-btn btn-nublado">
            <i class="fas fa-cloud"></i>
            <span>NUBLADO</span>
        </a>

        <a href="pages/processa_debug.php?acao=chuva" class="debug-btn btn-chuva">
            <i class="fas fa-cloud-showers-heavy"></i>
            <span>CHUVA</span>
        </a>

        <a href="pages/processa_debug.php?acao=tempestade" class="debug-btn btn-raio">
            <i class="fas fa-bolt"></i>
            <span>TEMPESTADE</span>
        </a>

        <a href="pages/processa_debug.php?acao=neve" class="debug-btn btn-neve">
            <i class="fas fa-snowflake"></i>
            <span>NEVE / GELO</span>
        </a>

    </div>

    <h3 style="font-family:'Oswald', sans-serif; margin-bottom:15px;">OUTRAS FERRAMENTAS</h3>
    
    <div class="control-grid">
        <a href="#" onclick="alert('Em breve')" class="debug-btn" style="border-color: #2ecc71; color: #2ecc71;">
            <i class="fas fa-money-bill-wave"></i>
            <span>DAR DINHEIRO</span>
        </a>

        <a href="#" onclick="alert('Em breve')" class="debug-btn" style="border-color: #e74c3c; color: #e74c3c;">
            <i class="fas fa-car"></i>
            <span>CRIAR CARRO</span>
        </a>
    </div>

</div>