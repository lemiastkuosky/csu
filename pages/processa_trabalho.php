<?php
// pages/processa_trabalho.php
session_start();
require_once '../config/db.php';

if(!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php?p=login");
    exit();
}

$id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'];
    $ganho = 0;
    $xp_ganho = 0;
    $mensagem = "";

    if($tipo == 'entregador') {
        $ganho = 50;
        $xp_ganho = 10;
        $mensagem = "Você entregou algumas peças de motor.";
    } elseif ($tipo == 'lavador') {
        $ganho = 80;
        $xp_ganho = 15;
        $mensagem = "Você lavou uma Ferrari cheia de lama.";
    }

    // Atualiza Dinheiro e XP no banco
    $sql = "UPDATE usuarios SET dinheiro = dinheiro + $ganho, xp = xp + $xp_ganho WHERE id = $id";
    
    if($conn->query($sql)) {
        echo "<script>
            alert('$mensagem\\n\\n+ R$ $ganho,00\\n+ $xp_ganho XP');
            window.location.href = '../index.php?p=trabalho';
        </script>";
    } else {
        echo "<script>alert('Erro no sistema.'); window.location.href='../index.php?p=trabalho';</script>";
    }
}
?>