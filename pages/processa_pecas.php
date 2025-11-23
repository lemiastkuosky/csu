<?php
// pages/processa_pecas.php
session_start();
require_once '../config/db.php';

if(!isset($_SESSION['usuario_id'])) exit;
$id = $_SESSION['usuario_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = $_POST['item'];
    $preco = floatval($_POST['preco']);

    // Verifica dinheiro
    $check = $conn->query("SELECT dinheiro FROM usuarios WHERE id = $id");
    $user = $check->fetch_assoc();

    if ($user['dinheiro'] >= $preco) {
        
        if ($item == 'radio') {
            // Desconta grana e ativa rádio
            $conn->query("UPDATE usuarios SET dinheiro = dinheiro - $preco, tem_radio = 1 WHERE id = $id");
            echo "<script>alert('Rádio instalado com sucesso! Ligue o som.'); window.location.href='../index.php?p=mapa';</script>";
        }

    } else {
        echo "<script>alert('Dinheiro insuficiente!'); window.location.href='../index.php?p=pecas';</script>";
    }
}
?>