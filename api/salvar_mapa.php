<?php
// api/salvar_mapa.php
session_start();
require_once '../config/db.php';

// Segurança: Só admin pode salvar
$uid = $_SESSION['usuario_id'];
$check = $conn->query("SELECT is_admin FROM usuarios WHERE id = $uid");
$u = $check->fetch_assoc();

if(!$u || $u['is_admin'] != 1) {
    http_response_code(403);
    exit(json_encode(['erro' => 'Acesso Negado']));
}

// Recebe o JSON do Javascript
$json = file_get_contents('php://input');
$pinos = json_decode($json, true);

if(is_array($pinos)) {
    foreach($pinos as $pino) {
        // Atualiza a posição baseado no Link (que é único por página)
        // Futuramente você pode usar o ID se preferir
        $stmt = $conn->prepare("UPDATE mapa_locais SET pos_x = ?, pos_y = ? WHERE link = ?");
        $stmt->bind_param("iis", $pino['x'], $pino['y'], $pino['link']);
        $stmt->execute();
    }
    echo json_encode(['status' => 'sucesso']);
} else {
    echo json_encode(['erro' => 'Dados inválidos']);
}
?>