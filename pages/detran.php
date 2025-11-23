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
            $msg_resultado = "<div class='msg success'>Visual da placa atualizado!</div>";
        }
    } else {
        $msg_resultado = "<div class='msg error'>Você precisa de R$ $PRECO_TROCA_PLACA!</div>";
    }
}

if(isset($_POST['acao']) && $_POST['acao'] == 'liberar') {
    $carro_id = (int)$_POST['carro_id'];
    $sql_check = "SELECT g.valor_multa, g.data_apreensao, u.dinheiro FROM garagem_jogador g JOIN usuarios u ON u.id = g.usuario_id WHERE g.id = $carro_id AND g.usuario_id = $uid AND g.apreendido = 1";
    $res_check = $conn->query($sql_check);
    
    if($res_check->num_rows > 0) {
        $data = $res_check->fetch_assoc();
        $dt_apreensao = new DateTime($data['data_apreensao']);
        $agora = new DateTime();
        $dias = $dt_apreensao->diff($agora)->days;
        if($dias < 1) $dias = 0;
        $custo = $data['valor_multa'] + ($dias * $VALOR_DIARIA_PATIO);
        
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
$sql_livres = "SELECT g.*, m.nome FROM garagem_jogador g JOIN carros_modelos m ON g.modelo_id = m.id WHERE g.usuario_id = $uid AND g.apreendido = 0 ORDER BY g.equipado DESC";
$res_livres = $conn->query($sql_livres);

$sql_presos = "SELECT g.*, m.nome, m.img_url FROM garagem_jogador g JOIN carros_modelos m ON g.modelo_id = m.id WHERE g.usuario_id = $uid AND g.apreendido = 1";
$res_presos = $conn->query($sql_presos);

$bg_detran = "assets/imgs/detran.jpg";
if(!file_exists($bg_detran)) $bg_detran = "https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1000&auto=format&fit=crop";
?>

<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">

<style>
    body, html { overflow: hidden; height: 100%; margin: 0; padding: 0; background: #000; }

    .detran-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: url('<?php echo $bg_detran; ?>') no-repeat center center; background-size: cover; z-index: -1; }
    .detran-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(10, 15, 25, 0.95); }

    /* WRAPPER CENTRALIZADO */
    .detran-wrapper {
        position: relative; width: 100%; height: 100vh; 
        padding-top: 60px; display: flex; align-items: center; justify-content: center;
    }

    /* PAINEL UNIFICADO (DASHBOARD) */
    .detran-panel {
        width: 900px; max-width: 95%; height: 550px;
        background: #111; border: 1px solid #333; border-radius: 10px;
        display: flex; overflow: hidden;
        box-shadow: 0 0 60px rgba(0,0,0,0.8);
    }

    /* COLUNA ESQUERDA (MENU/LISTA) */
    .panel-sidebar {
        width: 300px; background: #0a0a0a; border-right: 1px solid #222;
        display: flex; flex-direction: column;
    }
    
    .sidebar-header {
        padding: 20px; border-bottom: 1px solid #222;
        font-family: 'Oswald'; text-align: center;
    }
    .sidebar-title { font-size: 24px; color: #fff; letter-spacing: 1px; }
    .sidebar-sub { font-size: 10px; color: #3498db; text-transform: uppercase; letter-spacing: 2px; }

    /* ABAS INTERNAS DO MENU */
    .sidebar-tabs { display: flex; background: #151515; }
    .sidebar-tab {
        flex: 1; padding: 15px; text-align: center; cursor: pointer;
        font-size: 12px; color: #555; font-weight: bold; border-bottom: 2px solid transparent;
        transition: 0.2s;
    }
    .sidebar-tab:hover { color: #aaa; background: #1a1a1a; }
    .sidebar-tab.active { color: #fff; border-bottom-color: #3498db; background: #1a1a1a; }

    /* LISTA DE CARROS */
    .car-list { flex: 1; overflow-y: auto; padding: 10px; }
    .car-list::-webkit-scrollbar { width: 4px; }
    .car-list::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }
    
    .car-item {
        padding: 12px 15px; border-radius: 5px; margin-bottom: 5px;
        background: #151515; border: 1px solid transparent; cursor: pointer;
        display: flex; justify-content: space-between; align-items: center;
        transition: 0.2s;
    }
    .car-item:hover { background: #1a1a1a; border-color: #444; }
    .car-item.selected { background: #1a1a1a; border-color: #3498db; box-shadow: inset 3px 0 0 #3498db; }
    
    .car-info-l h4 { margin: 0; color: #fff; font-size: 13px; font-family: sans-serif; }
    .car-info-l span { font-size: 11px; color: #666; font-family: monospace; }
    .car-icon { color: #333; }
    .car-item.selected .car-icon { color: #3498db; }

    /* COLUNA DIREITA (CONTEÚDO) */
    .panel-content { flex: 1; background: #131313; position: relative; }
    
    .content-view { display: none; height: 100%; padding: 30px; flex-direction: column; align-items: center; }
    .content-view.active { display: flex; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

    /* --- EDITOR DE PLACAS --- */
    .plate-preview-box {
        background: #1a1a1a; border: 1px solid #333; border-radius: 8px;
        padding: 25px; width: 100%; display: flex; flex-direction: column; align-items: center;
        margin-bottom: 20px; box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
    }
    
    /* PLACA GRANDE */
    .license-plate-lg {
        width: 260px; height: 80px; display: flex; flex-direction: column;
        border-radius: 5px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.6);
        font-family: 'Share Tech Mono', monospace; font-weight: bold; transition: 0.3s;
        transform: scale(1.1);
    }
    .ph { height: 22px; width: 100%; display:flex; align-items:center; justify-content:flex-end; padding-right:10px; font-size:10px; }
    .pt { flex: 1; display: flex; justify-content: center; align-items: center; font-size: 42px; letter-spacing: 4px; }

    /* SKINS (Estilos CSS das Placas) */
    .plate-mercosul { background: white; border: 3px solid #003399; }
    .plate-mercosul .ph { background: #003399; color:white; } .plate-mercosul .pt { color: #000; }
    
    .plate-cinza { background: #bdc3c7; border: 3px solid #7f8c8d; }
    .plate-cinza .ph { display: none; } .plate-cinza .pt { color: #2c3e50; text-shadow: 1px 1px 0 white; }
    
    .plate-preta { background: #111; border: 3px solid #555; }
    .plate-preta .ph { display: none; } .plate-preta .pt { color: #95a5a6; }
    
    .plate-neon { background: #050505; border: 2px solid #00f3ff; box-shadow: 0 0 15px #00f3ff; }
    .plate-neon .ph { display: none; } .plate-neon .pt { color: #00f3ff; text-shadow: 0 0 10px #00f3ff; }

    /* GRID DE ESCOLHA */
    .skins-grid { 
        display: grid; grid-template-columns: 1fr 1fr; gap: 15px; width: 100%;
    }
    
    .skin-option {
        background: #222; border: 1px solid #333; padding: 15px; border-radius: 6px;
        cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 10px;
        transition: 0.2s;
    }
    .skin-option:hover { background: #2a2a2a; border-color: #555; }
    .skin-option.selected { border-color: #3498db; background: #1a2530; box-shadow: 0 0 15px rgba(52, 152, 219, 0.1); }

    /* MINI PLACAS (Visual nos botões) */
    .mini-plate { width: 80px; height: 25px; border-radius: 2px; display: flex; align-items: center; justify-content: center; font-size: 8px; font-weight: bold; }
    .mini-mercosul { background: white; border: 1px solid #003399; color:black; border-top: 4px solid #003399; }
    .mini-cinza { background: #ccc; border: 1px solid #555; color:#333; }
    .mini-preta { background: #222; border: 1px solid #555; color:#aaa; }
    .mini-neon { background: #000; border: 1px solid #00f3ff; color:#00f3ff; }

    .skin-meta { text-align: center; }
    .skin-name { display: block; font-size: 11px; color: #fff; font-weight: bold; }
    .skin-price { display: block; font-size: 10px; color: #2ecc71; }

    .btn-action { width: 100%; padding: 15px; background: #3498db; color: white; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; font-family: 'Oswald'; font-size: 16px; margin-top: auto; }
    .btn-action:hover { background: #2980b9; }
    .btn-action:disabled { background: #333; color: #555; cursor: not-allowed; }

    /* ESTADIA (PÁTIO) */
    .patio-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; width: 100%; }
    .patio-item { background: rgba(231, 76, 60, 0.1); border: 1px solid #c0392b; padding: 15px; border-radius: 5px; text-align: center; }
    .patio-warn { color: #e74c3c; font-size: 12px; margin-top: 5px; }
    
    .btn-back { position: fixed; top: 75px; left: 20px; z-index: 200; background: rgba(0,0,0,0.6); padding: 8px 20px; border-radius: 30px; color: white; text-decoration: none; font-family: 'Oswald'; font-size: 12px; border: 1px solid #555; }
    
    .msg-float { position: absolute; top: 20px; right: 20px; background: #2ecc71; color: white; padding: 10px 20px; border-radius: 5px; z-index: 2000; animation: fadeOut 3s forwards; }
    @keyframes fadeOut { 0% {opacity:1;} 80% {opacity:1;} 100% {opacity:0; display:none;} }
</style>

<div class="detran-bg"><div class="detran-overlay"></div></div>
<a href="index.php?p=mapa" class="btn-back"><i class="fas fa-arrow-left"></i> VOLTAR AO MAPA</a>

<?php if($msg_resultado) echo $msg_resultado; ?>

<div class="detran-wrapper">
    
    <div class="detran-panel">
        <div class="panel-sidebar">
            <div class="sidebar-header">
                <div class="detran-title">DETRAN</div>
                <div class="detran-subtitle">SERVIÇOS ONLINE</div>
            </div>
            
            <div class="sidebar-tabs">
                <div class="sidebar-tab active" onclick="switchTab('placas')"><i class="fas fa-paint-roller"></i> PLACAS</div>
                <div class="sidebar-tab" onclick="switchTab('patio')"><i class="fas fa-lock"></i> PÁTIO</div>
            </div>

            <div id="list-placas" class="car-list">
                <div style="font-size:10px; color:#555; margin-bottom:5px; text-transform:uppercase;">Seus Veículos</div>
                <?php if($res_livres->num_rows > 0): ?>
                    <?php while($c = $res_livres->fetch_assoc()): ?>
                        <div class="car-item" onclick="selectCar(this, '<?php echo $c['id']; ?>', '<?php echo $c['placa']; ?>', '<?php echo $c['plate_skin']; ?>')">
                            <div class="car-info-l">
                                <h4><?php echo $c['nome']; ?></h4>
                                <span><?php echo $c['placa']; ?></span>
                            </div>
                            <i class="fas fa-chevron-right car-icon"></i>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="color:#444; font-size:12px; text-align:center; margin-top:20px;">Nenhum carro na garagem.</div>
                <?php endif; ?>
            </div>
            
            <div id="list-patio" class="car-list" style="display:none;">
                <div style="font-size:10px; color:#e74c3c; margin-bottom:5px; text-transform:uppercase;">Veículos Apreendidos</div>
                <div style="color:#444; font-size:11px; padding:10px;">Selecione os veículos no painel ao lado para liberar.</div>
            </div>
        </div>

        <div class="panel-content">
            
            <div id="view-placas" class="content-view active">
                <div id="noCarSelected" style="flex:1; display:flex; align-items:center; justify-content:center; color:#444; flex-direction:column;">
                    <i class="fas fa-car" style="font-size:40px; margin-bottom:10px;"></i>
                    Selecione um veículo à esquerda
                </div>

                <div id="editorBox" style="display:none; width:100%; height:100%; flex-direction:column;">
                    <div class="plate-preview-box">
                        <div id="previewPlate" class="license-plate-lg plate-mercosul">
                            <div class="ph">BRASIL</div>
                            <div class="pt" id="previewText">AAA-0000</div>
                        </div>
                    </div>

                    <div style="font-size:12px; color:#777; margin-bottom:10px; text-transform:uppercase;">Estilos Disponíveis</div>

                    <div class="skins-grid">
                        <div class="skin-option" onclick="changeSkin('plate-mercosul', this)">
                            <div class="mini-plate mini-mercosul">ABC-123</div>
                            <div class="skin-meta">
                                <span class="skin-name">PADRÃO</span>
                                <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                            </div>
                        </div>

                        <div class="skin-option" onclick="changeSkin('plate-cinza', this)">
                            <div class="mini-plate mini-cinza">ABC-123</div>
                            <div class="skin-meta">
                                <span class="skin-name">CLÁSSICA</span>
                                <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                            </div>
                        </div>

                        <div class="skin-option" onclick="changeSkin('plate-preta', this)">
                            <div class="mini-plate mini-preta">ABC-123</div>
                            <div class="skin-meta">
                                <span class="skin-name">PRETA</span>
                                <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                            </div>
                        </div>

                        <div class="skin-option" onclick="changeSkin('plate-neon', this)">
                            <div class="mini-plate mini-neon">ABC-123</div>
                            <div class="skin-meta">
                                <span class="skin-name">NEON</span>
                                <span class="skin-price">R$ <?php echo $PRECO_TROCA_PLACA; ?></span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" style="margin-top:auto; width:100%;">
                        <input type="hidden" name="acao" value="trocar_placa">
                        <input type="hidden" name="carro_id" id="formCarId">
                        <input type="hidden" name="skin" id="formSkin" value="plate-mercosul">
                        <button type="submit" class="btn-action" onclick="return confirm('Confirmar compra da placa?')">APLICAR VISUAL</button>
                    </form>
                </div>
            </div>

            <div id="view-patio" class="content-view">
                <h3 style="margin-top:0; color:#e74c3c;">VEÍCULOS APREENDIDOS</h3>
                
                <?php if($res_presos->num_rows > 0): ?>
                    <div class="patio-container" style="overflow-y:auto; max-height:400px; padding-right:5px;">
                        <?php while($p = $res_presos->fetch_assoc()): 
                            $dt = new DateTime($p['data_apreensao']);
                            $agora = new DateTime();
                            $dias = $dt->diff($agora)->days;
                            $total = $p['valor_multa'] + ($dias * $VALOR_DIARIA_PATIO);
                        ?>
                            <div class="patio-item">
                                <strong style="color:#fff; font-size:14px;"><?php echo $p['nome']; ?></strong><br>
                                <span style="color:#aaa; font-size:11px;"><?php echo $p['placa']; ?></span>
                                <div style="margin:10px 0; font-size:12px; color:#ccc;">
                                    Multa: R$ <?php echo $p['valor_multa']; ?><br>
                                    + Dias: <?php echo $dias; ?> (R$ <?php echo $dias * $VALOR_DIARIA_PATIO; ?>)
                                </div>
                                <div style="font-size:16px; color:#e74c3c; font-weight:bold;">R$ <?php echo $total; ?></div>
                                
                                <form method="POST">
                                    <input type="hidden" name="acao" value="liberar">
                                    <input type="hidden" name="carro_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" class="btn-action" style="background:#e74c3c; padding:8px; font-size:12px; margin-top:10px;">PAGAR E LIBERAR</button>
                                </form>
                                <div class="patio-warn"><i class="fas fa-clock"></i> Leilão em <?php echo max(0, $DIAS_LIMITE_LEILAO - $dias); ?> dias</div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="color:#555; margin-top:50px; text-align:center;">
                        <i class="fas fa-smile" style="font-size:40px; margin-bottom:10px;"></i><br>
                        Tudo certo! Nenhum veículo preso.
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Abas do Menu
        document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
        event.currentTarget.classList.add('active');

        // Telas de Conteúdo
        document.querySelectorAll('.content-view').forEach(v => v.classList.remove('active'));
        document.getElementById('view-' + tabName).classList.add('active');

        // Listas Laterais (Opcional, para esconder lista de carros quando no patio)
        if(tabName === 'placas') {
            document.getElementById('list-placas').style.display = 'block';
            document.getElementById('list-patio').style.display = 'none';
        } else {
            document.getElementById('list-placas').style.display = 'none';
            document.getElementById('list-patio').style.display = 'block';
        }
    }

    function selectCar(el, id, placa, skin) {
        document.querySelectorAll('.car-item').forEach(i => i.classList.remove('selected'));
        el.classList.add('selected');

        document.getElementById('noCarSelected').style.display = 'none';
        document.getElementById('editorBox').style.display = 'flex';
        
        document.getElementById('previewText').innerText = placa;
        document.getElementById('formCarId').value = id;
        
        if(!skin) skin = 'plate-mercosul';
        changeSkin(skin, null);
    }

    function changeSkin(skinName, btn) {
        const plate = document.getElementById('previewPlate');
        plate.className = 'license-plate-lg ' + skinName;
        document.getElementById('formSkin').value = skinName;

        if(btn) {
            document.querySelectorAll('.skin-option').forEach(b => b.classList.remove('selected'));
            btn.classList.add('selected');
        }
    }
</script>