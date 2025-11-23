<?php
// api/transito.php
session_start();
require_once '../config/db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Não logado']); exit;
}

$uid = $_SESSION['usuario_id'];

// Busca carro + configs de consumo
$sql = "
    SELECT g.*, m.consumo_base 
    FROM garagem_jogador g
    JOIN carros_modelos m ON g.modelo_id = m.id
    WHERE g.usuario_id = $uid AND g.equipado = 1
";
$res = $conn->query($sql);

if($res->num_rows == 0) {
    echo json_encode(['status' => 'sucesso', 'msg' => 'A pé...', 'ape' => true]); exit;
}

$carro = $res->fetch_assoc();

// --- CÁLCULO DE CONSUMO REALISTA ---
// Base do carro (ex: Civic gasta 3, Supra gasta 8)
$base = $carro['consumo_base'];

// Adicionais por Peças (Peças fortes gastam mais)
$extra_turbo = ($carro['nivel_turbo'] ?? 0) * 1.5; 
$extra_motor = ($carro['nivel_motor'] ?? 0) * 0.5;

// Consumo final por viagem (arredondado)
$gasto_gasolina = ceil($base + $extra_turbo + $extra_motor);

// Variação de trânsito (aleatória +/- 1%)
$gasto_gasolina += rand(-1, 1);
if($gasto_gasolina < 1) $gasto_gasolina = 1; // Mínimo 1%

// Verificações
if ($carro['tanque_atual'] <= 0) {
    echo json_encode(['status' => 'erro', 'msg' => 'TANQUE SECO! Vá ao posto.']); exit;
}
if ($carro['danificado'] >= 100) {
    echo json_encode(['status' => 'erro', 'msg' => 'MOTOR FUNDIDO! Chame o guincho.']); exit;
}

// Aplica descontos
$novo_tanque = max(0, $carro['tanque_atual'] - $gasto_gasolina);
$novo_oleo = max(0, $carro['nivel_oleo'] - 1); // Óleo gasta devagar (1% fixo)
$novo_dano = $carro['danificado'];

// Se óleo estiver ruim, danifica o motor
if ($carro['nivel_oleo'] < 20) $novo_dano += rand(1, 3);
if ($carro['nivel_oleo'] <= 0) $novo_dano += rand(5, 10);

$novo_dano = min(100, $novo_dano);

// Salva
$stmt = $conn->prepare("UPDATE garagem_jogador SET tanque_atual = ?, nivel_oleo = ?, danificado = ? WHERE id = ?");
$stmt->bind_param("iiii", $novo_tanque, $novo_oleo, $novo_dano, $carro['id']);
$stmt->execute();

// Avisos
$aviso = "";
if($novo_tanque < 10) $aviso = "⚠️ Reserva!";
if($novo_oleo < 15) $aviso = "⚠️ Óleo crítico!";

echo json_encode([
    'status' => 'sucesso', 
    'msg' => "Viagem OK (-{$gasto_gasolina}% Gas)", 
    'aviso' => $aviso
]);
?>