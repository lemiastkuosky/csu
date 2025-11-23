<?php
// pages/loja.php
if(!isset($_SESSION['usuario_id'])) exit;

// 1. Busca o dinheiro atual do jogador
$id_usuario = $_SESSION['usuario_id'];
$sql = "SELECT dinheiro FROM usuarios WHERE id = $id_usuario";
$res = $conn->query($sql);
$user_data = $res->fetch_assoc();
$meu_dinheiro = $user_data['dinheiro'];

// 2. LÓGICA DE NAVEGAÇÃO
$marca_selecionada = isset($_GET['marca']) ? $_GET['marca'] : null;
$page_title = "Concessionária"; 

// ARRAY DE LOGOS
$logos_marcas = [
    'Honda' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7b/Honda_Logo.svg/1200px-Honda_Logo.svg.png',
    'Volkswagen' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Volkswagen_logo_2019.svg/2048px-Volkswagen_logo_2019.svg.png',
    'Chevrolet' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1e/Chevrolet_logo.svg/2560px-Chevrolet_logo.svg.png',
    'Fiat' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Fiat_Automobiles_logo.svg/2048px-Fiat_Automobiles_logo.svg.png',
    'Mitsubishi' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/Mitsubishi_logo.svg/2048px-Mitsubishi_logo.svg.png',
    'Toyota' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Toyota_EU.svg/2560px-Toyota_EU.svg.png'
];
?>

<?php 
    // CORREÇÃO AQUI: Removido o "../"
    $page_title = $marca_selecionada ? "CONCESSIONÁRIA - " . $marca_selecionada : "CONCESSIONÁRIA";
    include 'includes/modal_header.php'; 
?>

<div class="shop-wrapper">
    <div class="shop-container">
        
        <div class="shop-header">
            <div style="font-weight: bold; color:white;">SEU SALDO</div>
            <div class="shop-money">
                <i class="fas fa-dollar-sign"></i> <?php echo number_format($meu_dinheiro, 2, ',', '.'); ?>
            </div>
        </div>

        <?php if(!$marca_selecionada): ?>
            
            <?php
                $sql_marcas = "SELECT DISTINCT marca FROM modelos_carros ORDER BY marca ASC";
                $res_marcas = $conn->query($sql_marcas);
            ?>

            <div class="brands-grid">
                
                <div class="brand-card classified-card" onclick="alert('Funcionalidade Classificados (Usados) em desenvolvimento.')">
                    <i class="fas fa-newspaper"></i>
                    <span class="brand-name">CLASSIFICADOS (USADOS)</span>
                </div>

                <?php while($row = $res_marcas->fetch_assoc()): ?>
                    <?php 
                        $nome_marca = $row['marca'];
                        $logo_url = isset($logos_marcas[$nome_marca]) ? $logos_marcas[$nome_marca] : 'https://cdn-icons-png.flaticon.com/512/741/741407.png';
                    ?>
                    
                    <a href="index.php?p=loja&marca=<?php echo $nome_marca; ?>" class="brand-card">
                        <img src="<?php echo $logo_url; ?>" class="brand-logo" alt="<?php echo $nome_marca; ?>">
                        <span class="brand-name"><?php echo $nome_marca; ?></span>
                    </a>
                <?php endwhile; ?>

            </div>

        <?php else: ?>
            
            <a href="index.php?p=loja" class="btn-back-brands"><i class="fas fa-arrow-left"></i> VOLTAR PARA MARCAS</a>

            <?php
                $marca_busca = $conn->real_escape_string($marca_selecionada);
                $sql_carros = "SELECT * FROM modelos_carros WHERE marca = '$marca_busca' ORDER BY preco ASC";
                $result_carros = $conn->query($sql_carros);
            ?>

            <div class="cars-grid">
                <?php if($result_carros->num_rows > 0): ?>
                    <?php while($carro = $result_carros->fetch_assoc()): ?>
                        
                        <?php $pode = ($meu_dinheiro >= $carro['preco']); ?>

                        <div class="car-card">
                            <img src="<?php echo $carro['imagem']; ?>" class="car-img" loading="lazy">
                            <div class="car-info">
                                <h3 class="car-name"><?php echo $carro['nome']; ?></h3>
                                
                                <div class="stats-row">
                                    <div class="stat"><small>VEL.</small><strong><?php echo $carro['velocidade_max']; ?> Km/h</strong></div>
                                    <div class="stat"><small>ACEL.</small><strong><?php echo $carro['aceleracao']; ?>.0</strong></div>
                                    <div class="stat price"><small>PREÇO</small><strong style="color:var(--green-neon)">R$ <?php echo number_format($carro['preco'], 0, ',', '.'); ?></strong></div>
                                </div>

                                <form action="pages/processa_compra.php" method="POST">
                                    <input type="hidden" name="id_carro" value="<?php echo $carro['id']; ?>">
                                    <?php if($pode): ?>
                                        <button class="btn-buy affordable">COMPRAR AGORA</button>
                                    <?php else: ?>
                                        <button type="button" class="btn-buy expensive" disabled>FALTA GRANA</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color:#777">Nenhum modelo desta marca disponível.</p>
                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>
</div>