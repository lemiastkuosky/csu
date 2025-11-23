<?php
// pages/processa_cadastro.php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conn->real_escape_string($_POST['nome']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $cidade = $conn->real_escape_string($_POST['cidade']);

    // Verifica duplicidade
    $check = $conn->query("SELECT id FROM usuarios WHERE email = '$email'");
    if ($check->num_rows > 0) {
        echo "<script>alert('E-mail já cadastrado!'); window.location.href='../index.php?p=cadastro';</script>";
        exit();
    }

    $sql = "INSERT INTO usuarios (nome, email, senha, cidade, dinheiro) VALUES ('$nome', '$email', '$senha', '$cidade', 1000.00)";

    if ($conn->query($sql) === TRUE) {
        // Login automático
        $_SESSION['usuario_id'] = $conn->insert_id;
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_cidade'] = $cidade;

        header("Location: ../index.php?p=mapa");
    } else {
        echo "Erro: " . $conn->error;
    }
}
?>