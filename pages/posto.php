<?php
// pages/posto.php
if(!isset($_SESSION['usuario_id'])) exit;

$uid = $_SESSION['usuario_id'];

// 1. Economia
$sql_eco = "SELECT * FROM economia_posto WHERE id = 1";
$res_eco = $conn->query($sql_eco);
$eco = $res_eco->fetch_assoc();
if(!$eco) $eco = ['preco_etanol' => 3.50, 'preco_gasolina' => 5.50, 'preco_podium' => 8.00];

// 2. Dados Jogador
$sql = "SELECT g.*, m.nome, m.img_url, u.dinheiro FROM usuarios u 
        LEFT JOIN garagem_jogador g ON (g.usuario_id = u.id AND g.equipado = 1)
        LEFT JOIN carros_modelos m ON g.modelo_id = m.id
        WHERE u.id = $uid";
$res = $conn->query($sql);
$dados = $res->fetch_assoc();

$tem_carro = !empty($dados['nome']);
$tanque = $dados['tanque_atual'] ?? 0;
$oleo = $dados['nivel_oleo'] ?? 0;

// Imagens
$bg_gas = "assets/imgs/posto_gasolina.jpg";
if(!file_exists($bg_gas)) $bg_gas = "https://images.unsplash.com/photo-1571510192732-d6c6646c7f7c?q=80&w=1920&auto=format&fit=crop";
?>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">

<style>
    /* --- ESTRUTURA PADRÃO (Igual Garagem) --- */
    .station-bg { 
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: url('<?php echo $bg_gas; ?>') no-repeat center center; 
        background-size: cover; z-index: -1; 
    }
    .station-overlay { 
        position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.9); /* Fundo escuro para destaque */
    }

    /* Botão Voltar (Igual Garagem) */
    .btn-back-site {
        position: fixed; top: 80px; left: 30px;
        display: flex; align-items: center; gap: 10px; padding: 10px 25px;
        background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 30px; color: #fff; font-family: 'Oswald'; text-decoration: none; 
        z-index: 100; backdrop-filter: blur(5px); transition: 0.3s; font-size: 12px;
    }
    .btn-back-site:hover { background: #d32f2f; border-color: #d32f2f; }

    /* Container Central (Igual Garagem) */
    .station-container { 
        padding: 140px 50px 50px 50px; 
        max-width: 1200px; margin: 0 auto; 
        display: flex; flex-direction: column; align-items: center;
        min-height: 100vh;
    }

    /* --- MENU DE NAVEGAÇÃO (Igual Garagem) --- */
    .station-nav { 
        display: flex; gap: 15px; margin-bottom: 30px; 
        border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;
        width: 100%; max-width: 600px;
    }
    .nav-item { 
        flex: 1; padding: 15px; text-align: center; 
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); 
        color: #aaa; text-decoration: none; font-family: 'Oswald'; font-size: 16px; 
        border-radius: 5px; transition: all 0.3s; 
        display: flex; justify-content: center; align-items: center; gap: 10px; cursor: pointer;
    }
    .nav-item:hover { background: rgba(255,255,255,0.1); color: #fff; transform: translateY(-2px); }
    
    /* Estado Ativo */
    .nav-item.active { 
        background: #3498db; color: white; border-color: #3498db; 
        box-shadow: 0 0 15px rgba(52, 152, 219, 0.3); 
    }
    /* Cor diferente para Oficina (Opcional) */
    .nav-item.active.oil-tab { background: #e67e22; border-color: #e67e22; box-shadow: 0 0 15px rgba(230, 126, 34, 0.3); }


    /* --- ÁREAS DE CONTEÚDO --- */
    .content-area { display: none; width: 100%; max-width: 500px; animation: fadeIn 0.3s ease; }
    .content-area.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* --- MÁQUINA/BOMBA --- */
    .machine-box {
        background: #222; border: 4px solid #111; border-radius: 10px;
        padding: 30px; box-shadow: 0 0 60px rgba(0,0,0,0.8);
        display: flex; flex-direction: column; gap: 20px;
        position: relative;
    }
    .pump-machine { border-top: 6px solid #f1c40f; }
    .oil-machine { background: #2c3e50; border-color: #1a252f; border-top: 6px solid #e67e22; }

    /* DISPLAY DIGITAL */
    .pump-display {
        background: #000; border: 4px inset #444; border-radius: 5px; padding: 20px;
        display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
        box-shadow: inset 0 0 20px rgba(0,0,0,0.8);
    }
    .oil-display {
        background: #000; border: 4px inset #444; border-radius: 5px; padding: 20px;
        display: flex; justify-content: center; align-items: center;
        box-shadow: inset 0 0 20px rgba(0,0,0,0.8);
    }

    .lcd-screen {
        background: #081008; border: 1px solid #222; padding: 10px;
        display: flex; flex-direction: column; align-items: flex-end; justify-content: center;
        border-radius: 4px; box-shadow: inset 0 0 10px rgba(0,0,0,1);
    }
    .lcd-oil { width: 100%; align-items: center; background: #050810; }

    .lcd-label { font-size: 10px; color: #555; font-family: Arial; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
    .lcd-value { 
        font-family: 'Orbitron', sans-serif; font-size: 32px; 
        font-weight: 700; line-height: 1; letter-spacing: 2px;
    }
    .lcd-green { color: #2ecc71; text-shadow: 0 0 10px rgba(46, 204, 113, 0.6); }
    .lcd-blue { color: #3498db; text-shadow: 0 0 15px rgba(52, 152, 219, 0.8); }
    .lcd-red { color: #e74c3c; text-shadow: 0 0 15px rgba(231, 76, 60, 0.8); }

    /* BOTÕES COMBUSTÍVEL */
    .fuel-grades { display: flex; gap: 10px; }
    .grade-btn {
        flex: 1; height: 120px; 
        background: linear-gradient(to bottom, #2a2a2a, #1a1a1a);
        border: 2px solid #444; border-radius: 8px;
        color: #888; cursor: pointer;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        transition: 0.1s; user-select: none; box-shadow: 0 4px 0 #111;
    }
    .grade-name { font-family: 'Oswald'; font-size: 16px; font-weight: bold; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
    .grade-price { font-size: 12px; color: #ccc; font-family: 'Orbitron', monospace; letter-spacing: 1px; }
    
    .grade-btn:active, .grade-btn.pressing { transform: translateY(4px); box-shadow: none; color: #fff; border-color: #fff; }
    
    /* Cores Específicas */
    .btn-etanol { border-bottom: 4px solid #3498db; } 
    .btn-etanol:active, .btn-etanol.pressing { background: #3498db; box-shadow: 0 0 30px #3498db; }
    
    .btn-gasolina { border-bottom: 4px solid #f1c40f; } 
    .btn-gasolina:active, .btn-gasolina.pressing { background: #f1c40f; color:#000; box-shadow: 0 0 30px #f1c40f; }
    .btn-gasolina.pressing .grade-price { color: #000; }
    
    .btn-podium { border-bottom: 4px solid #e74c3c; } 
    .btn-podium:active, .btn-podium.pressing { background: #e74c3c; box-shadow: 0 0 30px #e74c3c; }

    .hint-text { text-align: center; font-size: 10px; color: #666; margin-top: 15px; letter-spacing: 1px; text-transform: uppercase; }

    /* OFICINA LISTA */
    .oil-options { display: flex; flex-direction: column; gap: 10px; }
    .oil-can-btn {
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(to right, #34495e, #2c3e50);
        border: 1px solid #4c6072; border-radius: 6px;
        padding: 15px; cursor: pointer; transition: 0.2s;
        box-shadow: 0 4px 0 #1a252f; height: 70px;
    }
    .oil-can-btn:hover { filter: brightness(1.2); border-color: #3498db; }
    .oil-can-btn:active { transform: translateY(4px); box-shadow: none; }
    
    .oil-left { display: flex; align-items: center; gap: 15px; }
    .oil-icon-box { width: 40px; height: 40px; background: rgba(0,0,0,0.3); border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 18px; color: #fff; border: 1px solid #7f8c8d; }
    .oil-details h4 { margin: 0; font-family: 'Oswald'; font-size: 14px; color: #fff; }
    .oil-details span { font-size: 10px; color: #bdc3c7; }
    .oil-cost { font-family: 'Orbitron'; font-weight: bold; color: #2ecc71; font-size: 14px; }

    /* MAQUININHA */
    .pos-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 3000; display: none; justify-content: center; align-items: center; backdrop-filter: blur(8px); }
    .pos-overlay.active { display: flex; }
    .pos-machine { width: 260px; background: #2c3e50; border-radius: 20px; box-shadow: 0 20px 50px black; border: 2px solid #34495e; padding: 20px; display: flex; flex-direction: column; gap: 15px; animation: slideUp 0.3s ease; }
    @keyframes slideUp { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .pos-screen { background: #95a5a6; height: 100px; border-radius: 8px; border: 3px solid #1a252f; box-shadow: inset 0 0 10px rgba(0,0,0,0.2); padding: 10px; display: flex; flex-direction: column; justify-content: center; align-items: center; font-family: 'Share Tech Mono', monospace; text-align: center; }
    .pos-msg { font-size: 12px; color: #2c3e50; margin-bottom: 5px; }
    .pos-val { font-size: 24px; font-weight: bold; color: #000; }
    .pos-actions { display: flex; gap: 10px; margin-top: 10px; }
    .pos-btn { flex: 1; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; color: white; box-shadow: 0 4px 0 rgba(0,0,0,0.3); transition: 0.1s; }
    .pos-btn:active { transform: translateY(4px); box-shadow: none; }
    .btn-cancel { background: #e74c3c; } .btn-pay { background: #2ecc71; }
    
    @media (max-width: 600px) { 
        .garage-container { padding: 100px 20px; }
        .garage-nav { flex-direction: column; gap: 10px; }
    }
</style>

<div class="station-bg"><div class="station-overlay"></div></div>

<a href="index.php?p=mapa" class="btn-back-site"><i class="fas fa-arrow-left"></i> VOLTAR AO MAPA</a>

<div class="garage-container">
    
    <div class="station-nav">
        <div class="nav-item active" onclick="switchTab('fuel', this)">
            <i class="fas fa-gas-pump"></i> BOMBAS DE COMBUSTÍVEL
        </div>
        <div class="nav-item" onclick="switchTab('oil', this)">
            <i class="fas fa-oil-can"></i> OFICINA / LUBRIFICAÇÃO
        </div>
    </div>

    <div id="area-fuel" class="content-area active">
        <div class="pump-machine machine-box">
            <div class="pump-display">
                <div class="lcd-screen">
                    <span class="lcd-label">LITROS</span>
                    <div class="lcd-value lcd-green" id="displayLitros">0.00</div>
                </div>
                <div class="lcd-screen">
                    <span class="lcd-label">TOTAL R$</span>
                    <div class="lcd-value lcd-green" id="displayPreco">0.00</div>
                </div>
            </div>
            
            <div class="pump-label" style="text-align:center; color:#f1c40f; font-family:'Oswald'; font-size:18px; border-bottom:1px solid #444; padding-bottom:10px;">
                SELECIONE E SEGURE PARA ENCHER
            </div>

            <div class="fuel-grades">
                <?php if($tem_carro): ?>
                    <div class="grade-btn btn-etanol" 
                         onmousedown="startFilling('etanol', this)" onmouseup="stopFilling()" onmouseleave="stopFilling()"
                         ontouchstart="startFilling('etanol', this)" ontouchend="stopFilling()">
                        <span class="grade-name">ETANOL</span>
                        <span class="grade-price">R$ <?php echo number_format($eco['preco_etanol'], 2); ?></span>
                    </div>
                    <div class="grade-btn btn-gasolina" 
                         onmousedown="startFilling('gasolina', this)" onmouseup="stopFilling()" onmouseleave="stopFilling()"
                         ontouchstart="startFilling('gasolina', this)" ontouchend="stopFilling()">
                            <span class="grade-name">GASOLINA</span>
                            <span class="grade-price">R$ <?php echo number_format($eco['preco_gasolina'], 2); ?></span>
                    </div>
                    <div class="grade-btn btn-podium" 
                         onmousedown="startFilling('podium', this)" onmouseup="stopFilling()" onmouseleave="stopFilling()"
                         ontouchstart="startFilling('podium', this)" ontouchend="stopFilling()">
                            <span class="grade-name">PODIUM</span>
                            <span class="grade-price">R$ <?php echo number_format($eco['preco_podium'], 2); ?></span>
                    </div>
                <?php else: ?>
                    <div style="width:100%; text-align:center; color:#e74c3c; padding:20px;">VOCÊ ESTÁ A PÉ!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="area-oil" class="content-area">
        <div class="oil-machine machine-box">
            
            <div class="oil-display">
                <div class="lcd-screen lcd-oil">
                    <span class="lcd-label">VIDA ÚTIL DO ÓLEO ATUAL</span>
                    <div class="lcd-value <?php echo ($oleo < 30 ? 'lcd-red' : 'lcd-blue'); ?>">
                        <?php echo $oleo; ?>%
                    </div>
                </div>
            </div>

            <div class="oil-options">
                <?php if($tem_carro): ?>
                    <div class="oil-can-btn" onclick="prePayOil('basico', 150)">
                        <div class="oil-left"><div class="oil-icon-box"><i class="fas fa-tint"></i></div><div class="oil-details"><h4>MINERAL</h4><span>Básico 20W50</span></div></div><div class="oil-cost">R$ 150</div>
                    </div>
                    <div class="oil-can-btn" onclick="prePayOil('sintetico', 300)">
                        <div class="oil-left"><div class="oil-icon-box" style="border-color:#3498db; color:#3498db;"><i class="fas fa-flask"></i></div><div class="oil-details"><h4>SINTÉTICO</h4><span>Performance</span></div></div><div class="oil-cost">R$ 300</div>
                    </div>
                    <div class="oil-can-btn" onclick="prePayOil('racing', 800)">
                        <div class="oil-left"><div class="oil-icon-box" style="border-color:#e74c3c; color:#e74c3c;"><i class="fas fa-fire"></i></div><div class="oil-details"><h4>RACING</h4><span>Extremo</span></div></div><div class="oil-cost">R$ 800</div>
                    </div>
                <?php else: ?>
                    <div style="text-align:center;color:#777; padding:20px;">Nenhum veículo.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<div class="pos-overlay" id="posModal">
    <div class="pos-machine">
        <div style="width:100%; text-align:center; color:#7f8c8d; font-size:12px; font-weight:bold; margin-bottom:10px;">MÁQUINA DE CARTÃO</div>
        <div class="pos-screen">
            <div class="pos-msg" id="posMsg">APROXIME O CARTÃO</div>
            <div class="pos-val">R$ <span id="posValue">0.00</span></div>
        </div>
        <div class="pos-actions">
            <button class="pos-btn btn-cancel" onclick="closePOS()">CANCELAR</button>
            <button class="pos-btn btn-pay" onclick="processPayment()">PAGAR</button>
        </div>
    </div>
</div>

<script>
    let prices = { etanol: <?php echo $eco['preco_etanol']; ?>, gasolina: <?php echo $eco['preco_gasolina']; ?>, podium: <?php echo $eco['preco_podium']; ?> };
    let currentFuel = '';
    let fillInterval = null;
    let currentLiters = 0;
    let maxTank = 100 - <?php echo $tanque; ?>; 
    let pendingPayment = { type: '', data: null, cost: 0 };

    function switchTab(screen, btn) {
        document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active', 'oil-tab'));
        document.querySelectorAll('.content-area').forEach(c => c.classList.remove('active'));
        
        btn.classList.add('active');
        if(screen === 'oil') btn.classList.add('oil-tab');
        
        document.getElementById('area-' + screen).classList.add('active');
    }

    document.addEventListener('contextmenu', event => event.preventDefault());
    window.addEventListener('mouseup', stopFilling);
    window.addEventListener('touchend', stopFilling);

    function startFilling(type, btn) {
        if(maxTank <= 0.5) { document.getElementById('displayLitros').innerText = "CHEIO"; setTimeout(() => updateDisplay(), 1000); return; }
        currentFuel = type;
        btn.classList.add('pressing');

        if(fillInterval) return;
        fillInterval = setInterval(() => {
            currentLiters += 0.4; 
            if(currentLiters >= maxTank) { currentLiters = maxTank; stopFilling(); }
            updateDisplay();
        }, 50);
    }

    function stopFilling() {
        document.querySelectorAll('.grade-btn').forEach(b => b.classList.remove('pressing'));
        if(!fillInterval) return;
        clearInterval(fillInterval);
        fillInterval = null;

        if(currentLiters > 0.2) {
            let cost = currentLiters * prices[currentFuel];
            pendingPayment = { type: 'fuel', data: { tipo: currentFuel, litros: currentLiters }, cost: cost };
            openPOS(cost);
        } else { currentLiters = 0; updateDisplay(); }
    }

    function updateDisplay() {
        let price = prices[currentFuel] || 0;
        let total = currentLiters * price;
        document.getElementById('displayLitros').innerText = currentLiters.toFixed(2);
        document.getElementById('displayPreco').innerText = total.toFixed(2);
    }

    function prePayOil(marca, cost) {
        pendingPayment = { type: 'oil', data: { marca: marca }, cost: cost };
        openPOS(cost);
    }

    function openPOS(value) {
        document.getElementById('posValue').innerText = value.toFixed(2);
        document.getElementById('posMsg').innerText = "CONFIRMAR VALOR";
        document.getElementById('posMsg').style.color = "#2c3e50";
        document.getElementById('posModal').classList.add('active');
    }

    function closePOS() {
        document.getElementById('posModal').classList.remove('active');
        if(pendingPayment.type === 'fuel') { currentLiters = 0; updateDisplay(); }
    }

    function processPayment() {
        document.getElementById('posMsg').innerText = "PROCESSANDO...";
        setTimeout(() => {
            const fd = new FormData();
            if(pendingPayment.type === 'fuel') {
                fd.append('acao', 'abastecer'); fd.append('tipo', pendingPayment.data.tipo); fd.append('litros', pendingPayment.data.litros);
            } else {
                fd.append('acao', 'trocar_oleo'); fd.append('marca', pendingPayment.data.marca);
            }
            fetch('api/posto_engine.php', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
                if(d.status === 'sucesso') {
                    document.getElementById('posMsg').innerText = "APROVADO!";
                    document.getElementById('posMsg').style.color = "#2ecc71";
                    setTimeout(() => location.reload(), 1000);
                } else {
                    document.getElementById('posMsg').innerText = "RECUSADO";
                    document.getElementById('posMsg').style.color = "#e74c3c";
                    setTimeout(() => closePOS(), 1500);
                }
            });
        }, 1500);
    }
</script>