<?php
// pages/garagem.php
if(!isset($_SESSION['usuario_id'])) exit;

$uid = $_SESSION['usuario_id'];

// 1. Busca TODOS os carros do jogador + Detalhes Técnicos + Status RPG + Placa/Ano/Chassi
$sql_carros = "
    SELECT 
        g.*, g.chassi_codigo, g.placa,
        m.nome, m.img_url, m.descricao, m.historia, m.ano_modelo,
        m.velocidade as vel_base, m.aceleracao as acc_base, m.controle as ctrl_base,
        m.potencia_base, m.peso_base, m.arranque_base,
        m.tracao, m.motor, m.aspiracao
    FROM garagem_jogador g
    JOIN carros_modelos m ON g.modelo_id = m.id
    WHERE g.usuario_id = $uid
    ORDER BY g.equipado DESC, g.id DESC
";

$meus_carros = [];
if(isset($conn)) {
    $res = $conn->query($sql_carros);
    while($row = $res->fetch_assoc()) {
        // Cálculos de Performance (Base + Upgrades)
        $lvl_motor = $row['nivel_motor'] ?? 0;
        $lvl_turbo = $row['nivel_turbo'] ?? 0;
        $lvl_peso  = $row['nivel_alivio'] ?? 0;

        $cv_ganho = ($lvl_motor * 15) + ($lvl_turbo * 40);
        $row['potencia_total'] = $row['potencia_base'] + $cv_ganho;
        
        $kg_perda = ($lvl_peso * 20);
        $row['peso_total'] = $row['peso_base'] - $kg_perda;

        // Barras Visuais (0-100)
        $row['bar_vel'] = min(100, $row['vel_base'] + ($cv_ganho / 5));
        $row['bar_acc'] = min(100, $row['acc_base'] + ($cv_ganho / 4));
        $row['bar_ctrl'] = $row['ctrl_base'];
        
        // Dados RPG (Combustivel, Oleo, Dano)
        $row['tanque'] = $row['tanque_atual'] ?? 100;
        $row['oleo'] = $row['nivel_oleo'] ?? 100;
        $row['saude'] = 100 - ($row['danificado'] ?? 0);
        
        // Textos Padrão (Fallback)
        if(empty($row['chassi_codigo'])) $row['chassi_codigo'] = "N/A-" . $row['id'];
        if(empty($row['ano_modelo'])) $row['ano_modelo'] = "????";
        if(empty($row['placa'])) $row['placa'] = "SEM-PLACA";

        $meus_carros[] = $row;
    }
}

// Placeholder se garagem vazia
if(empty($meus_carros)) {
    $meus_carros[] = [
        'id' => 0, 'nome' => 'Carro Exemplo', 'img_url' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf', 
        'historia' => 'Um carro simples para começar sua jornada.', 'potencia_base'=>100, 'potencia_total'=>100, 'peso_base'=>1000, 'peso_total'=>1000, 
        'bar_vel'=>30, 'bar_acc'=>30, 'bar_ctrl'=>50, 'danificado'=>0, 'equipado'=>0,
        'tracao'=>'FWD', 'motor'=>'I4', 'aspiracao'=>'Natural', 'arranque_total'=>9.5,
        'tanque'=>100, 'oleo'=>100, 'saude'=>100,
        'chassi_codigo'=>'EX-0000', 'ano_modelo'=>'2024', 'placa'=>'TESTE'
    ];
}

// --- FUNDO DA GARAGEM ---
$bg_local = "assets/imgs/garagem.jpg";
$bg_fisico = realpath(__DIR__ . '/../assets/imgs/garagem.jpg');

if (file_exists($bg_fisico)) {
    $bg_css = "url('$bg_local')";
} else {
    $bg_css = "url('https://images.unsplash.com/photo-1589820296156-2454bb8a6d54?q=80&w=1920&auto=format&fit=crop')";
}
?>

<style>
    /* --- FUNDO --- */
    .garage-bg { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-image: <?php echo $bg_css; ?>;
        background-repeat: no-repeat; background-position: center center; background-size: cover; 
        background-color: #111; z-index: -1; 
    }
    .garage-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); }

    /* --- BOTÃO VOLTAR --- */
    .btn-back-site {
        position: fixed; top: 80px; left: 30px;
        display: flex; align-items: center; gap: 10px; padding: 8px 20px;
        background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 30px; color: #fff; font-family: 'Oswald'; text-decoration: none; 
        z-index: 100; backdrop-filter: blur(5px); transition: 0.3s; font-size: 12px;
        cursor: pointer;
    }
    .btn-back-site:hover { background: #d32f2f; border-color: #d32f2f; }

    .garage-container { padding: 140px 50px 50px 50px; max-width: 1200px; margin: 0 auto; }

    /* --- MENU NAVEGAÇÃO --- */
    .garage-nav { display: flex; gap: 15px; margin-bottom: 30px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px; }
    .nav-item { flex: 1; padding: 12px; text-align: center; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #aaa; text-decoration: none; font-family: 'Oswald'; font-size: 14px; border-radius: 5px; transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px; cursor: pointer; }
    .nav-item:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateY(-2px); }
    .nav-item.active { background: #3498db; color: white; border-color: #3498db; box-shadow: 0 0 15px rgba(52, 152, 219, 0.3); }

    /* --- GRID DE CARROS --- */
    .garage-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }

    .car-card {
        background: rgba(20, 20, 20, 0.8); border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px; overflow: hidden; transition: 0.3s; position: relative; cursor: pointer;
    }
    .car-card:hover { transform: translateY(-4px); background: rgba(40, 40, 40, 0.9); border-color: #555; }
    .car-card.selected-card { border-color: #3498db; box-shadow: 0 0 20px rgba(52, 152, 219, 0.3); }

    /* FOTO DO CARRO */
    .car-image-box { position: relative; width: 100%; height: 130px; overflow: hidden; border-bottom: 1px solid #333; }
    .car-thumb { width: 100%; height: 100%; object-fit: cover; }

    /* BOTÃO "i" (Sobre a foto) */
    .info-btn {
        position: absolute; top: 8px; left: 8px;
        width: 24px; height: 24px; border-radius: 50%;
        background: rgba(0,0,0,0.8); border: 1px solid rgba(255,255,255,0.5);
        color: #fff; display: flex; justify-content: center; align-items: center;
        font-size: 11px; transition: 0.2s; z-index: 10;
    }
    .info-btn:hover { background: #3498db; border-color: #3498db; transform: scale(1.1); }

    /* BADGE EQUIPADO (Sobre a foto) */
    .equipped-badge { position: absolute; top: 8px; right: 8px; background: #2ecc71; color: #000; font-weight: bold; font-size: 9px; padding: 2px 6px; border-radius: 3px; z-index: 5; }

    /* INFO DO CARD (Parte Cinza) */
    .card-info { padding: 12px; }

    /* Chassi e Ano */
    .card-top-info {
        display: flex; justify-content: space-between; margin-bottom: 6px; 
        font-family: monospace; font-size: 9px; color: #888; border-bottom: 1px solid #444; padding-bottom: 4px;
    }
    .vin-code { letter-spacing: 0.5px; }

    /* Cabeçalho do Card (Nome + Placa) */
    .card-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .card-title { font-family: 'Oswald'; font-size: 16px; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px; }
    
    /* PLACA NO CARD */
    .mini-plate {
        background: white; border: 1px solid #777; border-radius: 2px;
        display: flex; flex-direction: column; overflow: hidden; height: 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }
    .mp-header { height: 4px; background: #003399; width: 100%; }
    .mp-text { 
        padding: 0 4px; font-family: 'Courier New', monospace; font-weight: 900; 
        font-size: 10px; color: #000; letter-spacing: 0.5px; line-height: 12px; 
        text-transform: uppercase;
    }

    /* STATUS RPG (Combustível, Óleo, Lataria) */
    .rpg-stats { display: flex; justify-content: space-between; background: rgba(0,0,0,0.4); padding: 6px; border-radius: 4px; }
    .rpg-item { display: flex; align-items: center; gap: 4px; font-size: 10px; color: #aaa; font-weight: bold; }
    .text-fuel { color: #f1c40f; } .text-oil { color: #bdc3c7; } .text-hp { color: #3498db; } .text-danger { color: #e74c3c; }


    /* --- MODAL COMPACTO --- */
    .car-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 2000; display: none; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
    .car-modal-content { width: 750px; height: 420px; background: #111; border: 1px solid #333; border-radius: 8px; display: flex; overflow: hidden; position: relative; box-shadow: 0 0 50px rgba(0,0,0,1); animation: fadeIn 0.2s ease; }
    @keyframes fadeIn { from { opacity:0; transform: scale(0.98); } to { opacity:1; transform: scale(1); } }
    
    .modal-left { width: 50%; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .modal-img { width: 100%; height: 100%; object-fit: cover; opacity: 0.9; mask-image: linear-gradient(to right, black 85%, transparent 100%); }
    
    .modal-right { width: 50%; padding: 20px; display: flex; flex-direction: column; }
    
    .modal-header { margin-bottom: 10px; border-bottom: 1px solid #333; padding-bottom: 8px; display: flex; justify-content: space-between; align-items: flex-end; }
    .modal-title-group h2 { font-family: 'Oswald'; font-size: 24px; color: #fff; margin: 0; line-height: 1; }
    .vin-display { font-family: monospace; color: #666; font-size: 10px; letter-spacing: 0.5px; display: block; margin-top: 2px; }
    .plate-tag { background: white; color: black; padding: 1px 6px; border-radius: 2px; font-weight: bold; font-size: 10px; margin-left: 5px; border: 1px solid #999; font-family: 'Courier New'; }
    
    .modal-tabs { display: flex; gap: 5px; }
    .tab-btn { background: transparent; border: 1px solid #444; color: #666; width: 28px; height: 28px; border-radius: 4px; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: 0.2s; font-size: 12px; }
    .tab-btn:hover { color: #fff; border-color: #777; }
    .tab-btn.active { background: #3498db; border-color: #3498db; color: #fff; }

    #contentSpecs, #contentLore { display: none; flex-direction: column; gap: 10px; height: 100%; overflow-y: auto; }
    #contentSpecs.active, #contentLore.active { display: flex; }

    .lore-box { font-size: 13px; line-height: 1.5; color: #ccc; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 4px; border-left: 3px solid #3498db; font-style: italic; }

    .modal-rpg-row { display: flex; justify-content: space-between; background: rgba(255,255,255,0.05); padding: 8px; border-radius: 4px; margin-bottom: 10px; border: 1px solid #222; }
    .mod-rpg-item { font-size: 11px; color: #ccc; display: flex; align-items: center; gap: 5px; }

    .stats-group { display: flex; flex-direction: column; gap: 6px; }
    .stat-item { display: flex; align-items: center; justify-content: space-between; }
    .stat-label { font-size: 9px; color: #888; width: 60px; }
    .stat-bar-bg { flex: 1; height: 4px; background: #222; border-radius: 2px; margin: 0 8px; }
    .stat-bar-fill { height: 100%; width: 0%; transition: width 0.5s ease; box-shadow: 0 0 4px currentColor; }
    .stat-val { font-size: 10px; color: #fff; width: 30px; text-align: right; font-weight: bold; }

    .specs-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; background: rgba(255,255,255,0.03); padding: 8px; border-radius: 4px; border: 1px solid #222; }
    .spec-badge { display: flex; flex-direction: column; align-items: center; text-align: center; background: #000; border: 1px solid #333; padding: 4px; border-radius: 3px; }
    .spec-icon { font-size: 10px; color: #3498db; margin-bottom: 1px; }
    .spec-name { font-size: 7px; color: #666; text-transform: uppercase; }
    .spec-val { font-size: 9px; font-weight: bold; color: #fff; }

    .modal-footer { display: flex; gap: 8px; margin-top: auto; padding-top: 10px; border-top: 1px solid #222; }
    .btn-modal { flex: 1; padding: 8px; border: none; cursor: pointer; font-family: 'Oswald'; font-weight: bold; color: white; text-transform: uppercase; font-size: 12px; transition: 0.2s; border-radius: 2px; clip-path: polygon(10px 0, 100% 0, 100% calc(100% - 10px), calc(100% - 10px) 100%, 0 100%, 0 10px); }
    .btn-equip { background: #3498db; } .btn-equip:hover { background: #2980b9; }
    .btn-sell { background: #222; border: 1px solid #e74c3c; color: #e74c3c; } .btn-sell:hover { background: #e74c3c; color: white; }
    .btn-close-modal { background: #444; } .btn-close-modal:hover { background: #666; }
    .close-x { position: absolute; top: 10px; right: 15px; font-size: 20px; color: #555; cursor: pointer; transition: 0.2s; z-index: 20; }
    .close-x:hover { color: #fff; }
    
    @media (max-width: 768px) { 
        .car-modal-content { flex-direction: column; width: 90%; height: 80vh; overflow-y: auto; } 
        .modal-left { height: 180px; width: 100%; } .modal-right { width: 100%; }
        .garage-nav { flex-direction: column; gap: 8px; }
    }
</style>

<div class="garage-bg"><div class="garage-overlay"></div></div>

<div class="btn-back-site" onclick="window.location.href='index.php?p=mapa'">
    <i class="fas fa-arrow-left"></i> VOLTAR AO MAPA
</div>

<div class="garage-container">
    <div class="garage-nav">
        <div class="nav-item" onclick="window.location.href='index.php?p=perfil'"><i class="fas fa-user-circle"></i> PERFIL</div>
        <div class="nav-item active" onclick="window.location.href='index.php?p=garagem'"><i class="fas fa-car"></i> GARAGEM</div>
        <div class="nav-item" onclick="window.location.href='index.php?p=config'"><i class="fas fa-cog"></i> CONFIGURAÇÃO</div>
    </div>

    <div class="garage-grid">
        <?php foreach($meus_carros as $c): ?>
            <div class="car-card <?php echo ($c['equipado']) ? 'selected-card' : ''; ?>" 
                 id="card-<?php echo $c['id']; ?>"
                 onclick="selectCar(this, 'specs', <?php echo htmlspecialchars(json_encode($c)); ?>)">
                
                <div class="info-btn" onclick="event.stopPropagation(); selectCar(document.getElementById('card-<?php echo $c['id']; ?>'), 'lore', <?php echo htmlspecialchars(json_encode($c)); ?>)" title="Ver História">
                    <i class="fas fa-info"></i>
                </div>

                <?php if($c['equipado']): ?>
                    <div class="equipped-badge">EM USO</div>
                <?php endif; ?>

                <div class="car-image-box">
                    <img src="<?php echo $c['img_url']; ?>" class="car-thumb">
                </div>
                
                <div class="card-info">
                    <div class="card-top-info">
                        <span class="vin-code">CHASSI: <?php echo $c['chassi_codigo']; ?></span>
                        <span class="year-code"><?php echo $c['ano_modelo']; ?></span>
                    </div>

                    <div class="card-header-flex">
                        <div class="card-title"><?php echo $c['nome']; ?></div>
                        <div class="mini-plate">
                            <div class="mp-header"></div>
                            <div class="mp-text"><?php echo $c['placa']; ?></div>
                        </div>
                    </div>
                    
                    <div class="rpg-stats">
                        <div class="rpg-item" title="Combustível"><i class="fas fa-gas-pump text-fuel"></i> <?php echo $c['tanque']; ?>%</div>
                        <div class="rpg-item" title="Nível de Óleo"><i class="fas fa-oil-can text-oil"></i> <?php echo $c['oleo']; ?>%</div>
                        <div class="rpg-item" title="Condição"><i class="fas fa-wrench <?php echo ($c['saude'] < 50) ? 'text-danger' : 'text-hp'; ?>"></i> <?php echo $c['saude']; ?>%</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="car-modal-overlay" id="modalOverlay">
    <div class="car-modal-content">
        <div class="close-x" onclick="closeModal()">&times;</div>
        <div class="modal-left"><img src="" id="modImg" class="modal-img"></div>
        
        <div class="modal-right">
            <div class="modal-header">
                <div class="modal-title-group">
                    <h2 id="modTitle">Nome</h2>
                    <small class="vin-display">
                        <span id="modAno">0000</span> • CHASSI: <span id="modVin">...</span> 
                        <span id="modPlaca" class="plate-tag">...</span>
                    </small>
                </div>
                
                <div class="modal-tabs">
                    <button class="tab-btn active" id="tabSpecs" onclick="switchTab('specs')" title="Ficha Técnica"><i class="fas fa-cogs"></i></button>
                    <button class="tab-btn" id="tabLore" onclick="switchTab('lore')" title="História"><i class="fas fa-info-circle"></i></button>
                </div>
            </div>

            <div id="contentSpecs" class="modal-body active">
                <div class="modal-rpg-row">
                    <div class="mod-rpg-item"><i class="fas fa-gas-pump text-fuel"></i> <span id="viewFuel">100%</span></div>
                    <div class="mod-rpg-item"><i class="fas fa-oil-can text-oil"></i> <span id="viewOil">100%</span></div>
                    <div class="mod-rpg-item"><i id="viewHpIcon" class="fas fa-wrench text-hp"></i> <span id="viewHp">100%</span></div>
                </div>

                <div class="stats-group">
                    <div class="stat-item"><span class="stat-label">VELOCIDADE</span><div class="stat-bar-bg"><div id="barVel" class="stat-bar-fill" style="background:#e74c3c;"></div></div><span id="txtVel" class="stat-val">0</span></div>
                    <div class="stat-item"><span class="stat-label">ACELERAÇÃO</span><div class="stat-bar-bg"><div id="barAcc" class="stat-bar-fill" style="background:#f1c40f;"></div></div><span id="txtAcc" class="stat-val">0</span></div>
                    <div class="stat-item"><span class="stat-label">CONTROLE</span><div class="stat-bar-bg"><div id="barCtrl" class="stat-bar-fill" style="background:#2ecc71;"></div></div><span id="txtCtrl" class="stat-val">0</span></div>
                </div>
                <div class="specs-grid">
                    <div class="spec-badge"><i class="fas fa-car-side spec-icon"></i><span class="spec-name">TRAÇÃO</span><span id="specTracao" class="spec-val">---</span></div>
                    <div class="spec-badge"><i class="fas fa-cogs spec-icon"></i><span class="spec-name">MOTOR</span><span id="specMotor" class="spec-val">---</span></div>
                    <div class="spec-badge"><i class="fas fa-wind spec-icon"></i><span class="spec-name">ASPIRAÇÃO</span><span id="specTurbo" class="spec-val">---</span></div>
                    <div class="spec-badge"><i class="fas fa-horse spec-icon"></i><span class="spec-name">POTÊNCIA</span><span id="specHP" class="spec-val">---</span></div>
                    <div class="spec-badge"><i class="fas fa-weight-hanging spec-icon"></i><span class="spec-name">PESO</span><span id="specKg" class="spec-val">---</span></div>
                    <div class="spec-badge"><i class="fas fa-stopwatch spec-icon"></i><span class="spec-name">0-100</span><span id="specArr" class="spec-val">---</span></div>
                </div>
            </div>

            <div id="contentLore" class="modal-body">
                <div id="loreText" class="lore-box">...</div>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-equip" id="btnEquip">EQUIPAR</button>
                <button class="btn-modal btn-sell" id="btnSell">VENDER</button>
                <button class="btn-modal btn-close-modal" onclick="closeModal()">VOLTAR</button>
            </div>
        </div>
    </div>
</div>

<script>
    function selectCar(cardElement, mode, car) {
        document.querySelectorAll('.car-card').forEach(el => el.classList.remove('selected-card'));
        cardElement.classList.add('selected-card');

        document.getElementById('modTitle').innerText = car.nome;
        document.getElementById('modImg').src = car.img_url;
        document.getElementById('modAno').innerText = car.ano_modelo;
        document.getElementById('modVin').innerText = car.chassi_codigo;
        document.getElementById('modPlaca').innerText = car.placa;
        
        document.getElementById('viewFuel').innerText = car.tanque + '%';
        document.getElementById('viewOil').innerText = car.oleo + '%';
        document.getElementById('viewHp').innerText = car.saude + '%';
        const hpIcon = document.getElementById('viewHpIcon');
        if(car.saude < 50) hpIcon.className = "fas fa-wrench text-danger";
        else hpIcon.className = "fas fa-wrench text-hp";

        document.getElementById('txtVel').innerText = car.bar_vel;
        document.getElementById('txtAcc').innerText = car.bar_acc;
        document.getElementById('txtCtrl').innerText = car.bar_ctrl;
        
        document.getElementById('specTracao').innerText = car.tracao || "-";
        document.getElementById('specMotor').innerText = car.motor || "-";
        document.getElementById('specTurbo').innerText = car.aspiracao || "-";
        document.getElementById('specHP').innerText = car.potencia_total + "cv";
        document.getElementById('specKg').innerText = car.peso_total + "kg";
        document.getElementById('specArr').innerText = car.arranque_total + "s";
        
        document.getElementById('loreText').innerText = car.historia || "Sem histórico disponível.";

        const btn = document.getElementById('btnEquip');
        if(car.equipado == 1) {
            btn.innerText = "EM USO"; btn.style.background = "#333"; btn.disabled = true;
        } else {
            btn.innerText = "EQUIPAR"; btn.style.background = "#3498db"; btn.disabled = false;
        }

        switchTab(mode);
        document.getElementById('modalOverlay').style.display = 'flex';

        setTimeout(() => {
            document.getElementById('barVel').style.width = car.bar_vel + '%';
            document.getElementById('barAcc').style.width = car.bar_acc + '%';
            document.getElementById('barCtrl').style.width = car.bar_ctrl + '%';
        }, 50);
    }

    function switchTab(mode) {
        const tabSpecs = document.getElementById('tabSpecs');
        const tabLore = document.getElementById('tabLore');
        const contentSpecs = document.getElementById('contentSpecs');
        const contentLore = document.getElementById('contentLore');

        if (mode === 'lore') {
            tabLore.classList.add('active'); tabSpecs.classList.remove('active');
            contentLore.classList.add('active'); contentSpecs.classList.remove('active');
        } else {
            tabSpecs.classList.add('active'); tabLore.classList.remove('active');
            contentSpecs.classList.add('active'); contentLore.classList.remove('active');
        }
    }

    function closeModal() {
        document.getElementById('modalOverlay').style.display = 'none';
        document.getElementById('barVel').style.width = '0%';
        document.getElementById('barAcc').style.width = '0%';
        document.getElementById('barCtrl').style.width = '0%';
    }

    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Auto-abrir se tiver view na URL
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const viewId = urlParams.get('view');
        if (viewId) {
            const card = document.getElementById('card-' + viewId);
            if (card) card.click();
        }
    });
</script>