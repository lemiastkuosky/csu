<?php
// api/posto_engine.php
session_start();
require_once '../config/db.php';

if(!isset($_SESSION['usuario_id'])) exit(json_encode(['erro' => 'Logue-se']));

$uid = $_SESSION['usuario_id'];
$acao = $_POST['acao'] ?? 'info';

// --- 1. SISTEMA DE PREÇOS DIÁRIOS ---
$sql_eco = "SELECT * FROM economia_posto WHERE id = 1";
$res_eco = $conn->query($sql_eco);
$eco = $res_eco->fetch_assoc();
$hoje = date('Y-m-d');

if ($eco['data_atual'] != $hoje) {
    $novo_etanol = rand(300, 450) / 100;
    $novo_gasolina = rand(500, 650) / 100;
    $novo_podium = rand(750, 950) / 100;
    $stmt = $conn->prepare("UPDATE economia_posto SET data_atual = ?, preco_etanol = ?, preco_gasolina = ?, preco_podium = ? WHERE id = 1");
    $stmt->bind_param("sddd", $hoje, $novo_etanol, $novo_gasolina, $novo_podium);
    $stmt->execute();
    $eco['preco_etanol'] = $novo_etanol;
    $eco['preco_gasolina'] = $novo_gasolina;
    $eco['preco_podium'] = $novo_podium;
}

// --- 2. ABASTECER (COM VERIFICAÇÃO DE TIPO) ---
if ($acao == 'abastecer') {
    $tipo_escolhido = $_POST['tipo']; // etanol, gasolina, podium
    $litros = (float)$_POST['litros'];
    
    // Busca dados do carro (Incluindo tipo de combustível e kit flex)
    $sql_carro = "
        SELECT g.id, g.tanque_atual, g.kit_flex, m.tipo_combustivel 
        FROM garagem_jogador g
        JOIN carros_modelos m ON g.modelo_id = m.id
        WHERE g.usuario_id = $uid AND g.equipado = 1
    ";
    $carro = $conn->query($sql_carro)->fetch_assoc();
    
    if(!$carro) exit(json_encode(['status' => 'erro', 'msg' => 'Nenhum carro equipado!']));
    
    // === LÓGICA DE COMPATIBILIDADE ===
    $compativel = false;
    
    // 1. Se tem Kit Flex, aceita Gasolina e Etanol
    if ($carro['kit_flex'] == 1) {
        if ($tipo_escolhido == 'gasolina' || $tipo_escolhido == 'etanol' || $tipo_escolhido == 'podium') $compativel = true;
    } 
    // 2. Se não tem kit, verifica o tipo de fábrica
    else {
        if ($tipo_escolhido == $carro['tipo_combustivel']) $compativel = true;
        // Gasolina aceita Podium (geralmente)
        if ($carro['tipo_combustivel'] == 'gasolina' && $tipo_escolhido == 'podium') $compativel = true;
    }

    if (!$compativel) {
        exit(json_encode([
            'status' => 'erro', 
            'msg' => "ERRO: Este carro usa apenas " . strtoupper($carro['tipo_combustivel']) . "! Instale um Kit Flex."
        ]));
    }
    
    // Preços
    $preco_unitario = 0;
    if($tipo_escolhido == 'etanol') $preco_unitario = $eco['preco_etanol'];
    if($tipo_escolhido == 'gasolina') $preco_unitario = $eco['preco_gasolina'];
    if($tipo_escolhido == 'podium') $preco_unitario = $eco['preco_podium'];
    
    $custo_total = $litros * $preco_unitario;
    
    // Verifica saldo
    $user = $conn->query("SELECT dinheiro FROM usuarios WHERE id = $uid")->fetch_assoc();
    $espaco_livre = 100 - $carro['tanque_atual'];
    if($litros > $espaco_livre) $litros = $espaco_livre;
    if($custo_total > $user['dinheiro']) exit(json_encode(['status' => 'erro', 'msg' => 'Dinheiro insuficiente!']));

    // Atualiza
    $novo_tanque = $carro['tanque_atual'] + $litros;
    $novo_saldo = $user['dinheiro'] - $custo_total;
    
    $conn->query("UPDATE usuarios SET dinheiro = $novo_saldo WHERE id = $uid");
    $conn->query("UPDATE garagem_jogador SET tanque_atual = $novo_tanque WHERE id = {$carro['id']}");
    
    exit(json_encode(['status' => 'sucesso', 'msg' => 'Abastecido com Sucesso!']));
}

// --- 3. TROCA DE ÓLEO (PREÇO BASEADO EM LITROS) ---
if ($acao == 'trocar_oleo') {
    $marca = $_POST['marca'];
    
    // Busca capacidade de óleo do motor
    $sql_carro = "
        SELECT g.id, m.litros_oleo 
        FROM garagem_jogador g
        JOIN carros_modelos m ON g.modelo_id = m.id
        WHERE g.usuario_id = $uid AND g.equipado = 1
    ";
    $carro = $conn->query($sql_carro)->fetch_assoc();
    
    if(!$carro) exit(json_encode(['status' => 'erro', 'msg' => 'Sem carro!']));

    $capacidade = $carro['litros_oleo']; // Ex: 4 litros ou 6 litros
    $preco_por_litro = 0;
    
    // Preço base por litro de cada marca
    if($marca == 'basico') $preco_por_litro = 30;   // 4L = 120
    if($marca == 'sintetico') $preco_por_litro = 60; // 4L = 240
    if($marca == 'racing') $preco_por_litro = 150;   // 4L = 600
    
    $custo_total = $preco_por_litro * $capacidade;
    
    $user = $conn->query("SELECT dinheiro FROM usuarios WHERE id = $uid")->fetch_assoc();
    
    if($user['dinheiro'] >= $custo_total) {
        $conn->query("UPDATE usuarios SET dinheiro = dinheiro - $custo_total WHERE id = $uid");
        $conn->query("UPDATE garagem_jogador SET nivel_oleo = 100 WHERE id = {$carro['id']}");
        exit(json_encode(['status' => 'sucesso', 'msg' => "Óleo Trocado! ($capacidade Litros)"]));
    } else {
        exit(json_encode(['status' => 'erro', 'msg' => "Você precisa de R$ $custo_total!"]));
    }
}

echo json_encode($eco);
?>