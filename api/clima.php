<?php
// api/clima.php
require_once '../config/db.php';

// Configurações
$climas_possiveis = ['limpo', 'limpo', 'chuva', 'nublado', 'tempestade', 'neve'];
$duracao_minima = 30; // Minutos
$duracao_maxima = 90; // Minutos

// 1. Pega o estado atual
$sql = "SELECT * FROM config_jogo WHERE id = 1";
$res = $conn->query($sql);
$estado = $res->fetch_assoc();

$agora = time();

// 2. Verifica se precisa trocar o clima (Se o tempo expirou)
if ($agora >= $estado['proxima_mudanca']) {
    
    // Sorteia novo clima
    $novo_clima = $climas_possiveis[array_rand($climas_possiveis)];
    
    // Sorteia quanto tempo vai durar (em segundos)
    $duracao_segundos = rand($duracao_minima, $duracao_maxima) * 60;
    $nova_troca = $agora + $duracao_segundos;

    // Atualiza no banco
    $stmt = $conn->prepare("UPDATE config_jogo SET clima_atual = ?, proxima_mudanca = ? WHERE id = 1");
    $stmt->bind_param("si", $novo_clima, $nova_troca);
    $stmt->execute();

    // Atualiza a variável local para retornar o novo
    $estado['clima_atual'] = $novo_clima;
    $estado['proxima_mudanca'] = $nova_troca;
}

// 3. Define Dia ou Noite baseado na HORA DO SERVIDOR (Brasil)
date_default_timezone_set('America/Sao_Paulo');
$hora = (int)date('H');
$modo_tempo = ($hora >= 6 && $hora < 18) ? 'dia' : 'noite';

// 4. Retorna JSON para o jogo
header('Content-Type: application/json');
echo json_encode([
    'clima' => $estado['clima_atual'],
    'modo' => $modo_tempo, // Agora o servidor decide se é dia ou noite
    'proxima_troca' => $estado['proxima_mudanca']
]);
?>