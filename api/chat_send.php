<?php
session_start();
require_once '../config/db.php';

if(isset($_SESSION['usuario_id']) && isset($_POST['msg'])) {
    $uid = $_SESSION['usuario_id'];
    $msg = trim($_POST['msg']);
    
    if(!empty($msg)) {
        // Limpa tags HTML para segurança
        $msg = htmlspecialchars($msg);
        
        // Insere no banco
        $stmt = $conn->prepare("INSERT INTO chat_global (usuario_id, mensagem) VALUES (?, ?)");
        $stmt->bind_param("is", $uid, $msg);
        $stmt->execute();
    }
}
?>