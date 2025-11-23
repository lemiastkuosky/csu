<?php
// api/clima.php
require_once '../config/db.php';

// CORREÇÃO: Define o fuso horário no início para todos os cálculos baseados em data/hora.
date_default_timezone_set('America/Sao_Paulo'); 

// Configurações
$climas_possiveis = ['limpo', 'limpo', 'chuva', 'nublado', 'tempestade', 'neve'];
$duracao_minima = 30; // Minutos
$duracao_maxima = 90; // Minutos

// 1. Pega o estado atual do clima
$sql = "SELECT * FROM config_jogo WHERE id = 1";
$res = $conn->query($sql);
$estado = $res->fetch_assoc();

$agora = time();

// 2. LÓGICA DE ATUALIZAÇÃO DO CLIMA (MANTIDA)
if ($agora >= $estado['proxima_mudanca']) {
    
    $novo_clima = $climas_possiveis[array_rand($climas_possiveis)];
    $duracao_segundos = rand($duracao_minima, $duracao_maxima) * 60;
    $nova_troca = $agora + $duracao_segundos;

    $stmt = $conn->prepare("UPDATE config_jogo SET clima_atual = ?, proxima_mudanca = ? WHERE id = 1");
    $stmt->bind_param("si", $novo_clima, $nova_troca);
    $stmt->execute();

    $estado['clima_atual'] = $novo_clima;
    $estado['proxima_mudanca'] = $nova_troca;
}

// === NOVO: CONTAGEM DE JOGADORES ONLINE ===
$tempo_limite = $agora - (5 * 60); // 5 minutos de inatividade
$sql_online = "SELECT COUNT(id) AS online_count FROM usuarios WHERE ultima_atividade >= $tempo_limite";
$res_online = $conn->query($sql_online);
$online_data = $res_online->fetch_assoc();
$jogadores_online = $online_data['online_count'];

// 3. Define Dia ou Noite
$hora = (int)date('H');
$modo_tempo = ($hora >= 6 && $hora < 19) ? 'dia' : 'noite';

// 4. Retorna JSON para o jogo
header('Content-Type: application/json');
echo json_encode([
    'clima' => $estado['clima_atual'],
    'modo' => $modo_tempo, 
    'proxima_troca' => $estado['proxima_mudanca'],
    'online' => $jogadores_online // <-- NOVO DADO
]);
?>