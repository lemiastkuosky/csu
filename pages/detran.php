<?php
// pages/detran.php
if(!isset($_SESSION['usuario_id'])) exit;

$uid = $_SESSION['usuario_id'];
$msg_resultado = "";

// PREÇOS
$PRECO_TROCA_PLACA = 500;
$VALOR_DIARIA_PATIO = 250;
$DIAS_LIMITE_LEILAO = 7;

// --- AÇÕES ---
if(isset($_POST['acao']) && $_POST['acao'] == 'trocar_placa') {
    $carro_id = (int)$_POST['carro_id'];
    $nova_skin = $_POST['skin'];
    $skins_validas = ['plate-mercosul', 'plate-cinza', 'plate-preta', 'plate-neon'];
    
    $user = $conn->query("SELECT dinheiro FROM usuarios WHERE id = $uid")->fetch_assoc();
    
    if($user['dinheiro'] >= $PRECO_TROCA_PLACA) {
        if(in_array($nova_skin, $skins_validas)) {
            $conn->query("UPDATE usuarios SET dinheiro = dinheiro - $PRECO_TROCA_PLACA WHERE id = $uid");
            $conn->query("UPDATE garagem_jogador SET plate_skin = '$nova_skin' WHERE id = $carro_id AND usuario_id = $uid");
            $msg_resultado = "<div class='msg success'>Placa atualizada! (-R$ $PRECO_TROCA_PLACA)</div>";
        }
    } else {
        $msg_resultado = "<div class='msg error'>Sem dinheiro suficiente!</div>";
    }
}

if(isset($_POST['acao']) && $_POST['acao'] == 'liberar') {
    $carro_id = (int)$_POST['carro_id'];
    $sql_check = "SELECT g.valor_multa, g.data_apreensao, u.dinheiro FROM garagem_jogador g JOIN usuarios u ON u.id = g.usuario_id WHERE g.id = $carro_id AND g.usuario_id = $uid AND g.apreendido = 1";
    $res_check = $conn->query($sql_check);
    
    if($res_check->num_rows > 0) {
        $data = $res_check->fetch_assoc();
        $custo = $data['valor_multa']; 
        
        if($data['dinheiro'] >= $custo) {
            $conn->query("UPDATE usuarios SET dinheiro = dinheiro - $custo WHERE id = $uid");
            $conn->query("UPDATE garagem_jogador SET apreendido = 0, valor_multa = 0 WHERE id = $carro_id");
            $msg_resultado = "<div class='msg success'>Veículo liberado!</div>";
        } else {
            $msg_resultado = "<div class='msg error'>Dinheiro insuficiente!</div>";
        }
    }
}

// --- DADOS ---
$sql_presos = "SELECT g.*, m.nome, m.img_url FROM garagem_jogador g JOIN carros_modelos m ON g.modelo_id = m.id WHERE g.usuario_id = $uid AND g.apreendido = 1";
$res_presos = $conn->query($sql_presos);

$sql_livres = "SELECT g.*, m.nome FROM garagem_jogador g JOIN carros_modelos m ON g.modelo_id = m.id WHERE g.usuario_id = $uid AND g.apreendido = 0 ORDER BY g.equipado DESC";
$res_livres = $conn->query($sql_livres);

$bg_detran = "assets/imgs/detran.jpg";
if(!file_exists($bg_detran)) $bg_detran = "https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1000&auto=format&fit=crop";
?>

<style>
    body, html { overflow: hidden; height: 100%; margin: 0; padding: 0; background: #000; }

    .detran-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: url('<?php echo $bg_detran; ?>') no-repeat center center; background-size: cover; z-index: -1; }
    .detran-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 15, 30, 0.9); }

    .detran-wrapper {
        position: relative; width: 100%; height: 100vh; 
        padding-top: 60px; display: flex; flex-direction: column; align-items: center; overflow: hidden;
    }

    .detran-header {
        text-align: center; margin-bottom: 15px; z-index: 10;
        border-bottom: 1px solid #333; padding-bottom: 10px; width: 90%; max-width: 800px;
    }
    .detran-title { font-family: 'Oswald'; font-size: 28px; color: #fff; letter-spacing: 1px; }
    .detran-subtitle { font-size: 12px; color: #3498db; text-transform: uppercase; letter-spacing: 2px; }

    .detran-nav { display: flex; gap: 10px; margin-bottom: 15px; z-index: 10; }
    .nav-btn { 
        padding: 10px 20px; background: rgba(255,255,255,0.05); border: 1px solid #333; 
        color: #aaa; cursor: pointer; font-family: 'Oswald'; font-size: 14px; border-radius: 20px; 
        transition: 0.2s; display: flex; align-items: center; gap: 8px;
    }
    .nav-btn:hover { color: #fff; background: rgba(255,255,255,0.1); }
    .nav-btn.active { background: #3498db; color: white; border-color: #3498db; box-shadow: 0 0 15px rgba(52, 152, 219, 0.3); }

    .tab-content { 
        display: none; width: 95%; max-width: 900px; height: 100%; 
        animation: fadeIn 0.3s ease; overflow: hidden; padding-bottom: 20px;
    }
    .tab-content.active { display: flex; flex-direction: column; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* EMPLACAMENTO */
    .emplacamento-layout { display: flex; gap: 20px; height: 100%; align-items: flex-start; }
    
    .car-list-col { 
        flex: 1; background: #1a1a1a; border: 1px solid #333; border-radius: 8px; 
        display: flex; flex-direction: column; overflow: hidden; 
        min-width: 250px; max-height: 80vh;
    }
    .list-header { padding: 10px; background: #222; border-bottom: 1px solid #333; color: #aaa; font-size: 12px; font-weight: bold; text-align: center; }
    
    /* --- SCROLL INVISÍVEL --- */
    .car-list-scroll { 
        flex: 1; overflow-y: auto; padding: 10px; 
        display: flex; flex-direction: column; gap: 5px;
        
        /* Esconde Barra de Rolagem */
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none;  /* IE 10+ */
        
        /* Sombra interna no topo/fundo para indicar rolagem */
        box-shadow: inset 0 10px 10px -10px rgba(0,0,0,0.5), inset 0 -10px 10px -10px rgba(0,0,0,0.5);
    }
    .car-list-scroll::-webkit-scrollbar { 
        display: none; /* Chrome/Safari/Webkit */
    }
    
    .car-item { padding: 10px; background: #252525; border: 1px solid #333; cursor: pointer; border-radius: 4px; display: flex; justify-content: space-between; align-items: center; transition: 0.2s; }
    .car-item:hover, .car-item.selected { background: #333; border-color: #3498db; }
    .car-item-name { font-size: 13px; color: #fff; font-weight: bold; }
    .car-item-plate { font-size: 11px; color: #777; font-family: monospace; }

    .editor-col { 
        flex: 2; background: #151515; border: 1px solid #333; border-radius: 8px; 
        padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; 
        position: relative; min-height: 500px; 
        overflow-y: auto; /* Scroll invisivel tbm */
        scrollbar-width: none; -ms-overflow-style: none;
    }
    .editor-col::-webkit-scrollbar { display: none; }

    .plate-preview-container { margin-bottom: 30px; margin-top: 10px; }
    
    .license-plate-lg { width: 220px; height: 70px; display: flex; flex-direction: column; border-radius: 5px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.8); font-family: 'Courier New', monospace; font-weight: 900; transition: 0.2s; }
    .ph { height: 20px; width: 100%; display:flex; align-items:center; justify-content:right; padding-right:8px; font-size:9px; text-transform:uppercase; }
    .pt { flex: 1; display: flex; justify-content: center; align-items: center; font-size: 36px; letter-spacing: 4px; }

    .plate-mercosul { background: white; border: 3px solid #003399; }
    .plate-mercosul .ph { background: #003399; color:white; } .plate-mercosul .pt { color: #000; }
    .plate-cinza { background: #ccc; border: 3px solid #555; } .plate-cinza .ph { display: none; } .plate-cinza .pt { color: #333; text-shadow: 1px 1px 0 #fff; }
    .plate-preta { background: #111; border: 3px solid #444; } .plate-preta .ph { display: none; } .plate-preta .pt { color: #ccc; }
    .plate-neon { background: #000; border: 3px solid #00f3ff; box-shadow: 0 0 20px #00f3ff; } .plate-neon .ph { display: none; } .plate-neon .pt { color: #00f3ff; text-shadow: 0 0 10px #00f3ff; }

    .skin-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; width: 100%; max-width: 400px; margin-bottom: 20px; }
    .skin-btn { padding: 15px; background: #222; border: 1px solid #444; text-align: center; cursor: pointer; border-radius: 5px; font-size: 12px; color: #aaa; transition: 0.2s; }
    .skin-btn:hover, .skin-btn.selected { background: #3498db; color: white; border-color: #3498db; }

    .mini-plate-display { width: 100%; height: 30px; border-radius: 3px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; font-family: 'Courier New'; margin-bottom: 5px; }
    .mini-mercosul { background: white; border: 1px solid #003399; color: black; border-top: 4px solid #003399; }
    .mini-cinza { background: #ccc; border: 1px solid #555; color: #333; }
    .mini-preta { background: #111; border: 1px solid #444; color: #ccc; }
    .mini-neon { background: #000; border: 1px solid #00f3ff; color: #00f3ff; text-shadow: 0 0 5px #00f3ff; }

    .btn-confirm { width: 100%; max-width: 300px; padding: 15px; background: #27ae60; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; font-family: 'Oswald'; font-size: 16px; }
    .btn-confirm:hover { background: #2ecc71; }

    /* PÁTIO (Scroll Invisível tbm) */
    .patio-scroll { 
        overflow-y: auto; height: 80vh; padding-right: 5px; width: 100%; 
        scrollbar-width: none; -ms-overflow-style: none;
    }
    .patio-scroll::-webkit-scrollbar { display: none; }

    .patio-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
    .patio-card { background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; padding: 15px; border-radius: 8px; text-align: center; position: relative; }
    .patio-img { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; opacity: 0.6; filter: grayscale(100%); margin-bottom: 10px; }
    .patio-badge { position: absolute; top: 10px; right: 10px; background: #e74c3c; color: white; font-size: 10px; padding: 2px 6px; border-radius: 3px; font-weight: bold; }
    .patio-name { font-family: 'Oswald'; font-size: 16px; color: #e74c3c; }
    .patio-multa { font-size: 18px; color: white; font-weight: bold; margin: 5px 0; }
    .auction-timer { background: #333; height: 6px; width: 100%; border-radius: 3px; margin: 10px 0; overflow: hidden; }
    .auction-bar { height: 100%; background: #e74c3c; transition: width 0.5s; }
    .btn-pay-multa { background: #e74c3c; color: white; border: none; padding: 8px 0; width: 100%; cursor: pointer; border-radius: 4px; font-weight: bold; margin-top: 5px; }
    .btn-pay-multa:hover { background: #c0392b; }

    .msg { padding: 10px; margin-bottom: 20px; border-radius: 5px; text-align: center; width: 90%; max-width: 800px; }
    .msg.success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }
    .msg.error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
    
    .btn-back { position: fixed; top: 75px; left: 20px; z-index: 200; background: rgba(0,0,0,0.6); padding: 8px 20px; border-radius: 30px; color: white; text-decoration: none; font-family: 'Oswald'; font-size: 12px; border: 1px solid #555; }
    
    @media (max-width: 768px) { .emplacamento-layout { flex-direction: column; } .car-list-col { height: 150px; min-height: 150px; } .editor-col { flex: 1; } }
</style>

<div class="detran-bg"><div class="detran-overlay"></div></div>
<a href="index.php?p=mapa" class="btn-back"><i class="fas fa-arrow-left"></i> VOLTAR AO MAPA</a>

<div class="detran-wrapper">
    
    <div class="detran-header">
        <div class="detran-title">DETRAN</div>
        <div class="detran-subtitle">REGULARIZAÇÃO VEICULAR</div>
    </div>

    <?php echo $msg_resultado; ?>

    <div class="detran-nav">
        <div class="nav-btn active" id="btn-placas" onclick="openTab('placas')"><i class="fas fa-paint-roller"></i> EMPLACAMENTO</div>
        <div class="nav-btn" id="btn-patio" onclick="openTab('patio')"><i class="fas fa-lock"></i> PÁTIO DE APREENSÃO</div>
    </div>

    <div id="tab-placas" class="tab-content active">
        <div class="emplacamento-layout">
            
            <div class="car-list-col">
                <div class="list-header">SELECIONE O VEÍCULO</div>
                <div class="car-list-scroll">
                    <?php if($res_livres->num_rows > 0): ?>
                        <?php while($c = $res_livres->fetch_assoc()): ?>
                            <div class="car-item" onclick="selectCarToPlate(this, '<?php echo $c['id']; ?>', '<?php echo $c['placa']; ?>', '<?php echo $c['plate_skin']; ?>')">
                                <div class="car-item-name"><?php echo $c['nome']; ?></div>
                                <div class="car-item-plate"><?php echo $c['placa']; ?></div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="padding:20px; text-align:center; color:#555;">Nenhum carro na garagem.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="editor-col">
                <div id="noCarSelected" style="color:#555; font-size:14px;">
                    <i class="fas fa-arrow-left"></i> Selecione um carro ao lado
                </div>

                <div id="plateEditor" style="display:none; width:100%; display:flex; flex-direction:column; align-items:center;">
                    
                    <div id="previewPlate" class="license-plate-lg plate-mercosul plate-preview-container">
                        <div class="ph">BRASIL</div>
                        <div class="pt" id="previewText">AAA-0000</div>
                    </div>

                    <div class="skin-grid">
                        <div class="skin-btn" onclick="changeSkin('plate-mercosul', this)">
                            <div class="mini-plate-display mini-mercosul">ABC-123</div>
                            <span class="skin-name">Padrão</span>
                            <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                        </div>
                        <div class="skin-btn" onclick="changeSkin('plate-cinza', this)">
                            <div class="mini-plate-display mini-cinza">ABC-1234</div>
                            <span class="skin-name">Clássica</span>
                            <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                        </div>
                        <div class="skin-btn" onclick="changeSkin('plate-preta', this)">
                            <div class="mini-plate-display mini-preta">ABC-123</div>
                            <span class="skin-name">Colecionador</span>
                            <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                        </div>
                        <div class="skin-btn" onclick="changeSkin('plate-neon', this)">
                            <div class="mini-plate-display mini-neon">ABC-123</div>
                            <span class="skin-name">Street Neon</span>
                            <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                        </div>
                    </div>

                    <form method="POST" style="width:100%; display:flex; justify-content:center; margin-top:20px;">
                        <input type="hidden" name="acao" value="trocar_placa">
                        <input type="hidden" name="carro_id" id="formCarId">
                        <input type="hidden" name="skin" id="formSkin" value="plate-mercosul">
                        <button type="submit" class="btn-confirm" onclick="return confirm('Pagar R$ <?php echo $PRECO_TROCA_PLACA; ?>?')">PAGAR E APLICAR</button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <div id="tab-patio" class="tab-content">
        <div class="patio-scroll">
            <?php if($res_presos->num_rows > 0): ?>
                <div class="patio-grid">
                    <?php while($p = $res_presos->fetch_assoc()): 
                        $dt_apreensao = new DateTime($p['data_apreensao']);
                        $hoje = new DateTime();
                        $dias = $dt_apreensao->diff($hoje)->days;
                        $dias_restantes = $DIAS_LIMITE_LEILAO - $dias;
                        if($dias_restantes < 0) $dias_restantes = 0;
                        $porcentagem_risco = ($dias / $DIAS_LIMITE_LEILAO) * 100;
                        $cor_risco = ($dias_restantes <= 2) ? '#e74c3c' : '#f39c12';
                        $total_pagar = $p['valor_multa'] + ($dias * $VALOR_DIARIA_PATIO);
                    ?>
                    <div class="patio-card">
                        <div style="position:absolute; top:10px; left:10px; font-size:10px; color:#aaa;">APREENDIDO HÁ <?php echo $dias; ?> DIAS</div>
                        <img src="<?php echo $p['img_url']; ?>" class="patio-img">
                        <div class="patio-info">
                            <div class="patio-name"><?php echo $p['nome']; ?></div>
                            <div style="font-size:11px; color:#888; margin-top:2px;"><?php echo $p['placa']; ?></div>
                            <div style="font-size:10px; color:<?php echo $cor_risco; ?>; margin-top:10px; text-align:left;">
                                <i class="fas fa-exclamation-triangle"></i> LEILÃO EM <?php echo $dias_restantes; ?> DIAS
                            </div>
                            <div class="auction-timer"><div class="auction-bar" style="width:<?php echo $porcentagem_risco; ?>%; background:<?php echo $cor_risco; ?>;"></div></div>
                            <div style="display:flex; justify-content:space-between; font-size:12px; color:#aaa; margin-top:5px;">
                                <span>Multa: R$ <?php echo $p['valor_multa']; ?></span>
                                <span>Diárias: R$ <?php echo ($dias * $VALOR_DIARIA_PATIO); ?></span>
                            </div>
                            <div style="font-size:20px; color:#fff; font-weight:bold; margin:10px 0;">R$ <?php echo number_format($total_pagar, 0, ',', '.'); ?></div>
                            <form method="POST">
                                <input type="hidden" name="acao" value="liberar">
                                <input type="hidden" name="carro_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="btn-pay-multa" onclick="return confirm('Pagar R$ <?php echo $total_pagar; ?>?')">LIBERAR VEÍCULO</button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding:50px; color:#777;">
                    <i class="fas fa-check-circle" style="font-size:50px; margin-bottom:20px; color:#2ecc71;"></i><br>
                    Nenhum veículo apreendido.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    function openTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        document.getElementById('btn-' + tab).classList.add('active');
    }

    function selectCarToPlate(el, id, placa, currentSkin) {
        document.querySelectorAll('.car-item').forEach(item => item.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('noCarSelected').style.display = 'none';
        document.getElementById('plateEditor').style.display = 'flex';
        document.getElementById('previewText').innerText = placa;
        document.getElementById('formCarId').value = id;
        if(!currentSkin) currentSkin = 'plate-mercosul';
        changeSkin(currentSkin, null);
    }

    function changeSkin(skinName, btn) {
        const plate = document.getElementById('previewPlate');
        plate.className = 'license-plate-lg plate-preview-container ' + skinName;
        document.getElementById('formSkin').value = skinName;
        if(btn) {
            document.querySelectorAll('.skin-btn').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        }
    }
</script>