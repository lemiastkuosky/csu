<?php
session_start();
require_once '../config/db.php';

// Pega o último ID que o navegador já tem
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

if ($last_id == 0) {
    // Carga inicial: Últimas 30 mensagens
    $sql = "SELECT * FROM (
        SELECT c.id, c.mensagem, c.data_envio, u.nome, u.is_admin, u.nivel
        FROM chat_global c 
        JOIN usuarios u ON c.usuario_id = u.id 
        ORDER BY c.id DESC LIMIT 30
    ) sub ORDER BY id ASC";
} else {
    // Atualização: Só mensagens novas
    $sql = "SELECT c.id, c.mensagem, c.data_envio, u.nome, u.is_admin, u.nivel
            FROM chat_global c 
            JOIN usuarios u ON c.usuario_id = u.id 
            WHERE c.id > $last_id 
            ORDER BY c.id ASC";
}

$res = $conn->query($sql);
$msgs = [];

while($row = $res->fetch_assoc()) {
    // Formata hora (HH:MM)
    $dt = new DateTime($row['data_envio']);
    $row['hora'] = $dt->format('H:i');
    $msgs[] = $row;
}

header('Content-Type: application/json');
echo json_encode($msgs);
?>