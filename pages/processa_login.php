<?php
// pages/processa_login.php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, senha, cidade FROM usuarios WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verifica senha
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nome'] = $row['nome'];
            $_SESSION['usuario_cidade'] = $row['cidade'];

            header("Location: ../index.php?p=mapa");
            exit();
        } else {
            echo "<script>alert('Senha incorreta!'); window.location.href='../index.php?p=login';</script>";
        }
    } else {
        echo "<script>alert('Usuário não encontrado!'); window.location.href='../index.php?p=login';</script>";
    }
}
?>