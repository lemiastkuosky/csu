<?php
// includes/topbar.php
if(!isset($_SESSION['usuario_id'])) exit;

$id_user = $_SESSION['usuario_id'];

// 1. BUSCA DADOS + CARRO ATIVO
$sql_top = "
    SELECT 
        u.dinheiro, u.nivel, u.xp, u.cidade, u.is_admin,
        g.id as carro_id, g.tanque_atual, g.nivel_oleo, g.danificado, g.placa,
        m.nome as nome_carro
    FROM usuarios u
    LEFT JOIN garagem_jogador g ON (g.usuario_id = u.id AND g.equipado = 1)
    LEFT JOIN carros_modelos m ON g.modelo_id = m.id
    WHERE u.id = $id_user
";

if(isset($conn)) {
    $res_top = $conn->query($sql_top);
    $dado_top = $res_top->fetch_assoc();
}

// Dados Jogador
$dinheiro_form = number_format($dado_top['dinheiro'] ?? 0, 0, ',', '.');
$nivel = $dado_top['nivel'] ?? 1;
$cidade = strtoupper($dado_top['cidade'] ?? 'Sao Paulo');
$is_admin = $dado_top['is_admin'] ?? 0;

// XP
$xp_atual = $dado_top['xp'] ?? 0;
$xp_prox = $nivel * 100;
$pct_xp = ($xp_prox > 0) ? ($xp_atual / $xp_prox) * 100 : 0;
if($pct_xp > 100) $pct_xp = 100;

// Dados Carro
$tem_carro = !empty($dado_top['nome_carro']);
$nome_carro = $tem_carro ? $dado_top['nome_carro'] : 'A PÉ';
$id_carro_equipado = $dado_top['carro_id'] ?? 0;
$placa_carro = $dado_top['placa'] ?? '---'; 
$tanque = $dado_top['tanque_atual'] ?? 0;
$oleo = $dado_top['nivel_oleo'] ?? 0;
$saude = 100 - ($dado_top['danificado'] ?? 0);
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    .topbar-container {
        position: fixed; top: 0; left: 0; right: 0; height: 55px;
        background: rgba(10, 10, 10, 0.95); backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 99999;
        display: flex; justify-content: space-between; align-items: center;
        padding: 0 20px; font-family: 'Oswald', sans-serif;
        box-shadow: 0 5px 20px rgba(0,0,0,0.9);
    }

    /* ESQUERDA */
    .hud-left { display: flex; align-items: center; gap: 12px; width: 30%; }
    .level-badge {
        width: 38px; height: 38px; background: linear-gradient(135deg, #d32f2f, #b71c1c);
        color: white; font-size: 18px; font-weight: bold;
        display: flex; justify-content: center; align-items: center;
        transform: skewX(-10deg); border: 2px solid rgba(255,255,255,0.15);
        box-shadow: 0 0 15px rgba(211, 47, 47, 0.4);
    }
    .level-text { transform: skewX(10deg); }
    .location-info { display: flex; flex-direction: column; justify-content: center; }
    .city-name { font-size: 14px; font-weight: 700; color: #fff; letter-spacing: 1px; text-transform: uppercase; line-height: 1; }
    .xp-track { width: 100px; height: 2px; background: #333; margin-top: 4px; position: relative; }
    .xp-fill { height: 100%; background: #3498db; width: <?php echo $pct_xp; ?>%; box-shadow: 0 0 8px #3498db; transition: width 0.5s; }

    /* CENTRO */
    .hud-center { 
        position: absolute; left: 50%; transform: translateX(-50%);
        display: flex; gap: 40px; align-items: center; 
    }

    /* Painel do Carro */
    .active-car-widget {
        display: flex; flex-direction: column; align-items: center;
        border-right: 1px solid rgba(255,255,255,0.1);
        padding-right: 15px; min-width: 160px; /* Aumentei um pouco pra caber os números */
        cursor: pointer; transition: 0.2s;
    }
    .active-car-widget:hover .car-name-display { color: #3498db; text-shadow: 0 0 10px #3498db; }
    
    .car-name-display {
        font-size: 11px; color: #fff; font-weight: bold; letter-spacing: 1px; text-transform: uppercase;
        margin-bottom: 4px; width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px;
    }
    .car-id-plate {
        background: #fff; color: #000; padding: 0 3px; border-radius: 2px;
        font-family: monospace; font-size: 9px; letter-spacing: 0;
        border: 1px solid #999; font-weight: 900;
    }
    
    .car-dash-grid { display: flex; gap: 10px; justify-content: center; }
    .dash-item { display: flex; align-items: center; gap: 3px; }
    .dash-icon { font-size: 9px; width: 12px; text-align: center; }
    .dash-bar-bg { width: 25px; height: 3px; background: #333; border-radius: 1px; } /* Reduzi um pouco a barra para caber o numero */
    .dash-bar-fill { height: 100%; border-radius: 1px; transition: width 0.5s; }
    
    /* NOVO: NÚMERO DE PORCENTAGEM */
    .dash-pct { font-size: 9px; color: #bbb; font-weight: bold; min-width: 22px; text-align: right; font-family: Arial, sans-serif; }

    /* Dinheiro */
    .money-group { text-align: left; display: flex; flex-direction: column; align-items: flex-start; }
    .money-label { font-size: 8px; color: #777; letter-spacing: 1px; margin-bottom: 0; text-transform: uppercase; }
    .money-value { font-size: 20px; font-weight: bold; color: #2ecc71; text-shadow: 0 0 15px rgba(46, 204, 113, 0.3); display: flex; align-items: center; gap: 4px; line-height: 1; }

    /* Cores */
    .icon-fuel { color: #f1c40f; } .fill-gas { background: #f1c40f; width: <?php echo $tanque; ?>%; }
    .icon-oil { color: #bdc3c7; }  .fill-oil { background: #bdc3c7; width: <?php echo $oleo; ?>%; }
    .icon-hp { color: #3498db; }   .fill-hp  { background: <?php echo ($saude < 30) ? '#e74c3c' : '#3498db'; ?>; width: <?php echo $saude; ?>%; }

    /* DIREITA */
    .hud-right { display: flex; align-items: center; justify-content: flex-end; height: 100%; }
    .tray-buttons { display: flex; align-items: center; gap: 10px; margin-right: 15px; } 

    .sys-icon { color: #aaa; font-size: 16px; cursor: pointer; width: 32px; height: 32px; display: flex; justify-content: center; align-items: center; border-radius: 5px; transition: 0.2s; }
    .sys-icon:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .sys-icon.danger:hover { background: rgba(211, 47, 47, 0.2); color: #ff5252; }

    .music-btn { width: 32px; height: 32px; border-radius: 5px; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: all 0.3s; color: #aaa; font-size: 16px; }
    .music-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .music-btn.active { color: #2ecc71; text-shadow: 0 0 10px #2ecc71; }

    .tray-separator { width: 1px; height: 30px; background: rgba(255,255,255,0.15); margin-right: 10px; }
    .system-info-group { display: flex; align-items: center; gap: 12px; cursor: default; }
    
    /* === ESTILOS DO WIDGET DE CLIMA (Menor e sem fundo) === */
    .weather-widget { 
        background-color: transparent; /* Remove o fundo */
        border: none; /* Remove a borda */
        border-radius: 0; 
        padding: 0; /* Remove o padding interno */
        display: flex; /* Alinha horizontalmente */
        align-items: center;
        gap: 5px; /* Reduz o espaçamento */
        box-shadow: none; 
        cursor: help;
        transition: all 0.2s ease;
    }
    .weather-icon { 
        font-size: 16px; /* Ícone menor */
        margin-bottom: 0; 
    }
    .weather-temp { 
        color: #ff9900; 
        font-weight: bold;
        font-size: 14px; /* Fonte menor */
        text-shadow: 0 0 5px rgba(255, 153, 0, 0.4);
        min-width: auto;
    }
    #weather-text { /* Novo elemento para o nome do clima */
        color: #aaaaaa; 
        font-size: 14px; /* Fonte menor */
        font-weight: normal;
        margin-left: 2px;
    }
    /* === FIM ESTILOS CLIMA === */
    
    /* --- Novo Widget Online --- */
    .online-widget {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
        padding: 0 10px;
        border-right: 1px solid rgba(255, 255, 255, 0.15); /* Separador visual */
    }
    #online-count {
        color: #2ecc71; /* Cor verde para destaque */
        font-size: 16px;
    }
    /* --- Fim Widget Online --- */
    
    .system-clock { display: flex; flex-direction: column; align-items: center; min-width: 60px; }
    .clock-time { font-size: 18px; font-weight: 700; color: #fff; line-height: 1; }
    .clock-date { font-size: 9px; color: #ccc; margin-top: 2px; font-family: 'Arial', sans-serif; }
</style>

<div class="topbar-container">
    
    <div class="hud-left">
        <div class="level-badge" title="Nível">
            <span class="level-text"><?php echo $nivel; ?></span>
        </div>
        <div class="location-info">
            <div class="city-name"><?php echo $cidade; ?></div>
            <div class="xp-track" title="XP">
                <div class="xp-fill" style="width: <?php echo $pct_xp; ?>%;"></div>
            </div>
        </div>
    </div>

    <div class="hud-center">
        
        <div class="active-car-widget" title="Veículo Atual" 
             onclick="window.location.href='index.php?p=garagem&view=<?php echo $id_carro_equipado; ?>'">
            
            <div class="car-name-display">
                <span><?php echo $nome_carro; ?></span>
                <?php if($tem_carro): ?>
                    <span class="car-id-plate"><?php echo $placa_carro; ?></span>
                <?php endif; ?>
            </div>
            
            <?php if($tem_carro): ?>
            <div class="car-dash-grid">
                <div class="dash-item" title="Combustível">
                    <span class="dash-pct"><?php echo $tanque; ?>%</span>
                    <div class="dash-bar-bg"><div class="dash-bar-fill fill-gas"></div></div>
                    <i class="fas fa-gas-pump dash-icon icon-fuel"></i>
                </div>
                <div class="dash-item" title="Óleo">
                    <span class="dash-pct"><?php echo $oleo; ?>%</span>
                    <div class="dash-bar-bg"><div class="dash-bar-fill fill-oil"></div></div>
                    <i class="fas fa-oil-can dash-icon icon-oil"></i>
                </div>
                <div class="dash-item" title="Lataria">
                    <span class="dash-pct"><?php echo $saude; ?>%</span>
                    <div class="dash-bar-bg"><div class="dash-bar-fill fill-hp"></div></div>
                    <i class="fas fa-wrench dash-icon icon-hp"></i>
                </div>
            </div>
            <?php else: ?>
                <small style="color:#555; font-size:9px;">A PÉ</small>
            <?php endif; ?>
        </div>

        <div class="money-group">
            <div class="money-label">SALDO</div>
            <div class="money-value"><i class="fas fa-dollar-sign" style="font-size:12px;"></i> <?php echo $dinheiro_form; ?></div>
        </div>
    </div>

    <div class="hud-right">
        <div class="tray-buttons">
            <div id="btn-volume" class="music-btn" onclick="toggleMusic()" title="Som">
                <i class="fas fa-volume-up"></i>
            </div>
            <?php if($is_admin == 1): ?>
                <div class="sys-icon" onclick="toggleDebug()" title="God Mode" style="color:#f1c40f;">
                    <i class="fas fa-bug"></i>
                </div>
            <?php endif; ?>
            <a href="logout.php" class="sys-icon danger" title="Sair do Jogo">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
        <div class="tray-separator"></div>
        <div class="system-info-group">
            <div class="online-widget" title="Jogadores Online">
                <i class="fas fa-users" style="color:#2ecc71;"></i>
                <span id="online-count">—</span>
            </div>
            <div id="weather-box" class="weather-widget" title="Carregando...">
                <i id="weather-icon" class="fas fa-sun weather-icon" style="color:#f1c40f;"></i>
                <span id="weather-temp" class="weather-temp">--°C</span>
                <span id="weather-text">--</span>
            </div>
            <div class="system-clock">
                <div class="clock-time" id="game-clock">00:00</div>
                <div class="clock-date" id="game-date">--/--/--</div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateSystemClock() {
        const now = new Date();
        let h = now.getHours(); let m = now.getMinutes();
        h = h < 10 ? '0'+h : h; m = m < 10 ? '0'+m : m;
        let day = now.getDate(); let month = now.getMonth() + 1; let year = now.getFullYear();
        day = day < 10 ? '0'+day : day; month = month < 10 ? '0'+month : month;

        const clockEl = document.getElementById('game-clock');
        const dateEl = document.getElementById('game-date');
        
        if(clockEl) clockEl.innerText = h + ':' + m;
        if(dateEl) dateEl.innerText = day + '/' + month + '/' + year;
    }
    setInterval(updateSystemClock, 1000);
    updateSystemClock();
</script>