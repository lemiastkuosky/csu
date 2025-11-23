<?php
// pages/pecas.php
if(!isset($_SESSION['usuario_id'])) exit;

$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT dinheiro, tem_radio FROM usuarios WHERE id = $id_usuario";
$res = $conn->query($sql);
$u = $res->fetch_assoc();

$meu_dinheiro = $u['dinheiro'];
$tem_radio = $u['tem_radio'];
?>

<style>
    .parts-container { padding: 20px; padding-bottom: 120px; color: white; }
    
    .parts-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 15px;
    }
    .parts-title { font-family: 'Oswald', sans-serif; font-size: 24px; color: var(--red-neon); }

    .parts-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;
    }

    .part-card {
        background: #1c1c1e; border: 1px solid #333; border-radius: 8px;
        padding: 20px; display: flex; align-items: center; gap: 20px;
        transition: 0.2s;
    }
    .part-card:hover { border-color: #8e44ad; transform: translateY(-5px); }

    .part-img {
        width: 80px; height: 80px; background: #111; border-radius: 50%;
        display: flex; justify-content: center; align-items: center;
        font-size: 30px; color: #8e44ad; border: 2px solid #8e44ad;
    }

    .part-info h3 { margin: 0; font-family: 'Oswald', sans-serif; font-size: 18px; }
    .part-info p { color: #aaa; font-size: 12px; margin: 5px 0 10px 0; }

    .btn-buy-part {
        background: #27ae60; color: white; border: none; padding: 8px 15px;
        border-radius: 4px; cursor: pointer; font-weight: bold; text-transform: uppercase;
    }
    .btn-buy-part:hover { background: #2ecc71; }
    .btn-owned { background: #333; color: #777; cursor: not-allowed; }

</style>

<div class="shop-wrapper">
    <div class="parts-container">
        
        <div class="parts-header">
            <div class="parts-title"><i class="fas fa-cogs"></i> LOJA DE PEÇAS</div>
            <div style="font-family:'Oswald', sans-serif; color:#2ecc71;">
                R$ <?php echo number_format($meu_dinheiro, 2, ',', '.'); ?>
            </div>
        </div>

        <div class="parts-grid">

            <div class="part-card">
                <div class="part-img"><i class="fas fa-music"></i></div>
                <div class="part-info">
                    <h3>SISTEMA DE SOM PIONEER</h3>
                    <p>Instala um rádio no seu painel. Permite ouvir músicas e rádios online enquanto joga.</p>
                    
                    <?php if($tem_radio): ?>
                        <button class="btn-buy-part btn-owned" disabled>JÁ POSSUI</button>
                    <?php else: ?>
                        <form action="pages/processa_pecas.php" method="POST">
                            <input type="hidden" name="item" value="radio">
                            <input type="hidden" name="preco" value="500">
                            <button type="submit" class="btn-buy-part">COMPRAR (R$ 500)</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="part-card" style="opacity: 0.5;">
                <div class="part-img" style="color:#e74c3c; border-color:#e74c3c;"><i class="fas fa-fire"></i></div>
                <div class="part-info">
                    <h3>NITROUS OXIDE (NOS)</h3>
                    <p>Aumenta a aceleração drasticamente. (Indisponível - Nível 5)</p>
                    <button class="btn-buy-part btn-owned" disabled>BLOQUEADO</button>
                </div>
            </div>

        </div>
    </div>
</div>