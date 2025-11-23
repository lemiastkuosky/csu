<?php
// pages/processa_debug.php
session_start();
require_once '../config/db.php';
// --- LINHA DE CORREÇÃO: Carrega o motor para ter acesso à função de cálculo de temperatura ---
require_once '../includes/game_engine.php'; 
// ------------------------------------------------------------------------------------------

date_default_timezone_set('America/Sao_Paulo');


// 1. SEGURANÇA
if (!isset($_SESSION['usuario_id'])) die("Erro de sessão.");
$id = $_SESSION['usuario_id'];
$check = $conn->query("SELECT is_admin FROM usuarios WHERE id = $id");
$u = $check->fetch_assoc();

if ($u['is_admin'] != 1) die("ACESSO NEGADO.");


// 2. PROCESSAMENTO
if (isset($_GET['acao'])) {
    $acao = $_GET['acao'];
    
    // --- COMANDOS PLAYER (TRAPAÇAS) ---
    if ($acao == 'grana') {
        $conn->query("UPDATE usuarios SET dinheiro = dinheiro + 5000 WHERE id = $id");
    
    } elseif ($acao == 'nivel') {
        $conn->query("UPDATE usuarios SET nivel = nivel + 1 WHERE id = $id");
    
    } elseif ($acao == 'radio') {
        $conn->query("UPDATE usuarios SET tem_radio = 1 WHERE id = $id");
    
    } elseif ($acao == 'reset_p') {
        $conn->query("UPDATE usuarios SET dinheiro = 1000, nivel = 1, xp = 0, tem_radio = 0 WHERE id = $id");
    
    } 
    // --- COMANDOS DE CLIMA (MUNDO) ---
    elseif ($acao == 'reset') {
        $conn->query("UPDATE config_jogo SET clima_atual = 'sol', temperatura = 25, hora_offset = 0, proxima_mudanca = NOW() WHERE id = 1");
    } else {
        // Lógica de Clima Forçado com Temperatura Realista (baseada na hora)
        $date = new DateTime();
        $date->add(new DateInterval('PT1H'));
        $nova_proxima = $date->format('Y-m-d H:i:s');
        
        $hora_real = (int)date('H'); // Hora real do servidor
        
        // CÁLCULO DA NOVA TEMPERATURA USANDO A FUNÇÃO
        // (A função CalcularTemperaturaRealista agora está disponível!)
        $temp = CalcularTemperaturaRealista($acao, $hora_real);
        
        // Adiciona um pequeno toque aleatório, mas não interfere com a lógica da curva
        $temp += rand(-1, 1);


        // Atualiza o banco de dados global
        $stmt = $conn->prepare("UPDATE config_jogo SET clima_atual = ?, temperatura = ?, proxima_mudanca = ?, hora_offset = 0 WHERE id = 1");
        $stmt->bind_param("sis", $acao, $temp, $nova_proxima);
        
        if (!$stmt->execute()) {
            echo "Erro ao atualizar clima: " . $conn->error;
            exit;
        }
    }
}

// 3. REDIRECIONAR DE VOLTA PARA O MAPA
header("Location: ../index.php?p=mapa");
exit();
?>