<?php
// pages/detran.php
if(!isset($_SESSION['usuario_id'])) exit;

$uid = $_SESSION['usuario_id'];
$msg_resultado = "";

// --- AÇÕES (POST) ---

// 1. Trocar Skin da Placa
if(isset($_POST['acao']) && $_POST['acao'] == 'trocar_placa') {
    $carro_id = (int)$_POST['carro_id'];
    $nova_skin = $_POST['skin'];
    // Segurança básica para skins permitidas
    $skins_validas = ['plate-mercosul', 'plate-cinza', 'plate-preta', 'plate-neon'];
    
    if(in_array($nova_skin, $skins_validas)) {
        $conn->query("UPDATE garagem_jogador SET plate_skin = '$nova_skin' WHERE id = $carro_id AND usuario_id = $uid");
        $msg_resultado = "<div class='msg success'>Placa atualizada com sucesso!</div>";
    }
}

// 2. Pagar Multa (Liberar Carro)
if(isset($_POST['acao']) && $_POST['acao'] == 'liberar') {
    $carro_id = (int)$_POST['carro_id'];
    
    // Busca carro e saldo
    $sql_check = "SELECT g.valor_multa, u.dinheiro FROM garagem_jogador g JOIN usuarios u ON u.id = g.usuario_id WHERE g.id = $carro_id AND g.usuario_id = $uid AND g.apreendido = 1";
    $res_check = $conn->query($sql_check);
    
    if($res_check->num_rows > 0) {
        $data = $res_check->fetch_assoc();
        $custo = $data['valor_multa'];
        
        if($data['dinheiro'] >= $custo) {
            // Paga e Libera
            $conn->query("UPDATE usuarios SET dinheiro = dinheiro - $custo WHERE id = $uid");
            $conn->query("UPDATE garagem_jogador SET apreendido = 0, valor_multa = 0 WHERE id = $carro_id");
            $msg_resultado = "<div class='msg success'>Veículo liberado! Já está na sua garagem.</div>";
        } else {
            $msg_resultado = "<div class='msg error'>Dinheiro insuficiente para pagar a multa.</div>";
        }
    }
}

// --- BUSCA DADOS ---

// Carros Presos
$sql_presos = "SELECT g.*, m.nome FROM garagem_jogador g JOIN carros_modelos m ON g.modelo_id = m.id WHERE g.usuario_id = $uid AND g.apreendido = 1";
$res_presos = $conn->query($sql_presos);

// Carros Livres (Para emplacar)
$sql_livres = "SELECT g.*, m.nome FROM garagem_jogador g JOIN carros_modelos m ON g.modelo_id = m.id WHERE g.usuario_id = $uid AND g.apreendido = 0";
$res_livres = $conn->query($sql_livres);

// Fundo
$bg_detran = "https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1000&auto=format&fit=crop"; // Imagem estilo prédio público/moderno
if(file_exists("assets/imgs/detran.jpg")) $bg_detran = "assets/imgs/detran.jpg";
?>

<style>
    .detran-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: url('<?php echo $bg_detran; ?>') no-repeat center center; background-size: cover; z-index: -1; }
    .detran-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 15, 30, 0.9); }

    .detran-container { 
        padding: 100px 20px 20px 20px; max-width: 1000px; margin: 0 auto; 
        display: flex; flex-direction: column; gap: 30px; color: white;
    }

    /* MENU ABAS */
    .detran-nav { display: flex; gap: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
    .nav-btn { 
        padding: 12px 25px; background: rgba(255,255,255,0.05); border: 1px solid #333; 
        color: #aaa; cursor: pointer; font-family: 'Oswald'; font-size: 16px; border-radius: 5px; transition: 0.2s;
    }
    .nav-btn:hover { color: white; background: rgba(255,255,255,0.1); }
    .nav-btn.active { background: #3498db; color: white; border-color: #3498db; }
    
    .tab-content { display: none; animation: fadeIn 0.3s ease; }
    .tab-content.active { display: block; }

    /* --- ESTILOS DAS PLACAS (SKINS) --- */
    .plate-preview-box {
        background: #1a1a1a; padding: 20px; border-radius: 8px; border: 1px solid #333;
        display: flex; flex-direction: column; align-items: center; gap: 15px;
    }
    
    /* Base da Placa */
    .license-plate-lg {
        width: 200px; height: 65px; 
        display: flex; flex-direction: column; 
        border-radius: 6px; overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        font-family: 'Courier New', monospace; font-weight: 900;
        transition: 0.2s;
    }
    
    /* SKINS */
    .plate-mercosul { background: white; border: 2px solid #003399; }
    .plate-mercosul .ph { height: 18px; background: #003399; width: 100%; display:flex; align-items:center; justify-content:right; padding-right:5px; color:white; font-size:8px; }
    .plate-mercosul .pt { flex: 1; display: flex; justify-content: center; align-items: center; font-size: 32px; color: #000; letter-spacing: 2px; }

    .plate-cinza { background: #ccc; border: 2px solid #555; }
    .plate-cinza .ph { display: none; }
    .plate-cinza .pt { flex: 1; display: flex; justify-content: center; align-items: center; font-size: 32px; color: #333; letter-spacing: 4px; text-shadow: 1px 1px 0 #fff; }

    .plate-preta { background: #111; border: 2px solid #444; }
    .plate-preta .ph { display: none; }
    .plate-preta .pt { flex: 1; display: flex; justify-content: center; align-items: center; font-size: 32px; color: #fff; letter-spacing: 2px; }

    .plate-neon { background: #000; border: 2px solid #00f3ff; box-shadow: 0 0 15px #00f3ff; }
    .plate-neon .ph { display: none; }
    .plate-neon .pt { flex: 1; display: flex; justify-content: center; align-items: center; font-size: 32px; color: #00f3ff; letter-spacing: 2px; text-shadow: 0 0 10px #00f3ff; }

    /* Seleção de Carro e Skin */
    .emplacamento-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
    
    .car-select-list { max-height: 400px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
    .car-item { 
        padding: 10px; background: #222; border: 1px solid #333; cursor: pointer; border-radius: 5px;
        display: flex; justify-content: space-between; align-items: center; transition: 0.2s;
    }
    .car-item:hover, .car-item.selected { background: #333; border-color: #3498db; }
    
    .skin-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; margin-top: 20px; }
    .skin-btn { 
        padding: 10px; background: #222; border: 1px solid #444; text-align: center; cursor: pointer; border-radius: 5px; 
        font-size: 12px; color: #aaa; transition: 0.2s;
    }
    .skin-btn:hover, .skin-btn.selected { background: #3498db; color: white; border-color: #3498db; }

    /* PÁTIO */
    .patio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .patio-card { background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; padding: 15px; border-radius: 8px; text-align: center; }
    .patio-title { font-size: 18px; font-weight: bold; color: #e74c3c; margin-bottom: 10px; }
    .btn-pay { background: #27ae60; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 4px; width: 100%; font-weight: bold; }
    .btn-pay:hover { background: #2ecc71; }

    .msg { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
    .msg.success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
    .msg.error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
    
    .btn-back { position: fixed; top: 75px; left: 20px; z-index: 200; background: rgba(0,0,0,0.6); padding: 8px 20px; border-radius: 30px; color: white; text-decoration: none; font-family: 'Oswald'; font-size: 12px; border: 1px solid #555; }
</style>

<div class="detran-bg"><div class="detran-overlay"></div></div>
<a href="index.php?p=mapa" class="btn-back"><i class="fas fa-arrow-left"></i> VOLTAR AO MAPA</a>

<div class="detran-container">
    <div style="font-size:32px; font-family:'Oswald'; border-left:5px solid #3498db; padding-left:15px;">
        DETRAN <small style="font-size:14px; color:#777; display:block;">DEPARTAMENTO DE TRÂNSITO</small>
    </div>

    <?php echo $msg_resultado; ?>

    <div class="detran-nav">
        <div class="nav-btn active" onclick="openTab('placas')"><i class="fas fa-paint-roller"></i> EMPLACAMENTO</div>
        <div class="nav-btn" onclick="openTab('patio')"><i class="fas fa-lock"></i> PÁTIO DE APREENSÃO</div>
    </div>

    <div id="tab-placas" class="tab-content active">
        <div class="emplacamento-grid">
            
            <div class="car-list-box">
                <h4 style="margin-bottom:10px; color:#aaa;">SELECIONE O VEÍCULO</h4>
                <div class="car-select-list">
                    <?php while($c = $res_livres->fetch_assoc()): ?>
                        <div class="car-item" onclick="selectCarToPlate(this, '<?php echo $c['id']; ?>', '<?php echo $c['placa']; ?>', '<?php echo $c['plate_skin']; ?>')">
                            <span><?php echo $c['nome']; ?></span>
                            <small style="color:#777;"><?php echo $c['placa']; ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="plate-editor" id="plateEditor" style="display:none;">
                <div class="plate-preview-box">
                    <h4>VISUALIZAÇÃO</h4>
                    
                    <div id="previewPlate" class="license-plate-lg plate-mercosul">
                        <div class="ph">BRASIL</div>
                        <div class="pt" id="previewText">ABC-1234</div>
                    </div>

                    <p style="font-size:12px; color:#777;">Escolha o modelo da placa:</p>
                    
                    <div class="skin-options">
                        <div class="skin-btn" onclick="changeSkin('plate-mercosul')">Mercosul (Padrão)</div>
                        <div class="skin-btn" onclick="changeSkin('plate-cinza')">Cinza (Clássica)</div>
                        <div class="skin-btn" onclick="changeSkin('plate-preta')">Preta (Colecionador)</div>
                        <div class="skin-btn" onclick="changeSkin('plate-neon')">Neon (Street)</div>
                    </div>

                    <form method="POST" style="width:100%; margin-top:20px;">
                        <input type="hidden" name="acao" value="trocar_placa">
                        <input type="hidden" name="carro_id" id="formCarId">
                        <input type="hidden" name="skin" id="formSkin" value="plate-mercosul">
                        
                        <button type="submit" class="btn-pay" style="width:100%; padding:15px; background:#3498db; border:none; color:white; font-weight:bold; cursor:pointer; border-radius:5px;">
                            CONFIRMAR NOVO ESTILO
                        </button>
                    </form>
                </div>
            </div>
            
            <div id="noCarSelected" style="display:flex; align-items:center; justify-content:center; color:#555; border:1px dashed #444; border-radius:10px;">
                Selecione um carro ao lado para editar a placa.
            </div>

        </div>
    </div>

    <div id="tab-patio" class="tab-content">
        <?php if($res_presos->num_rows > 0): ?>
            <div class="patio-grid">
                <?php while($p = $res_presos->fetch_assoc()): ?>
                    <div class="patio-card">
                        <div class="patio-title"><i class="fas fa-car-crash"></i> <?php echo $p['nome']; ?></div>
                        <p style="font-size:12px; color:#aaa;">Placa: <?php echo $p['placa']; ?></p>
                        <p style="font-size:14px; margin:15px 0;">Multa: <strong style="color:#fff;">R$ <?php echo number_format($p['valor_multa'], 0, ',', '.'); ?></strong></p>
                        
                        <form method="POST">
                            <input type="hidden" name="acao" value="liberar">
                            <input type="hidden" name="carro_id" value="<?php echo $p['id']; ?>">
                            <button type="submit" class="btn-pay">PAGAR E LIBERAR</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:50px; color:#777;">
                <i class="fas fa-check-circle" style="font-size:50px; margin-bottom:20px; color:#2ecc71;"></i><br>
                Nenhum veículo apreendido no momento.
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    function openTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function selectCarToPlate(el, id, placa, currentSkin) {
        // Visual selection
        document.querySelectorAll('.car-item').forEach(item => item.classList.remove('selected'));
        el.classList.add('selected');

        // Show Editor
        document.getElementById('noCarSelected').style.display = 'none';
        document.getElementById('plateEditor').style.display = 'block';

        // Fill Data
        document.getElementById('previewText').innerText = placa;
        document.getElementById('formCarId').value = id;
        
        // Set current skin
        if(!currentSkin) currentSkin = 'plate-mercosul';
        changeSkin(currentSkin);
    }

    function changeSkin(skinName) {
        // Update Visual Preview
        const plate = document.getElementById('previewPlate');
        plate.className = 'license-plate-lg ' + skinName;

        // Update Form Input
        document.getElementById('formSkin').value = skinName;

        // Update Buttons Visual
        document.querySelectorAll('.skin-btn').forEach(btn => btn.classList.remove('selected'));
        // (Simples logic to highlight button would require IDs on buttons, skipping for brevity)
    }
</script>