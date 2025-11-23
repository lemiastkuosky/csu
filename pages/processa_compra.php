<?php
// pages/processa_compra.php
session_start();
require_once '../config/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.php?p=login");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $carro_id = intval($_POST['id_carro']);

    // 1. Busca preço e nome do carro
    $sql_carro = "SELECT preco, nome FROM modelos_carros WHERE id = $carro_id";
    $res_carro = $conn->query($sql_carro);
    $dados_carro = $res_carro->fetch_assoc();
    $preco_carro = $dados_carro['preco'];

    // 2. Busca dinheiro do usuário
    $sql_user = "SELECT dinheiro FROM usuarios WHERE id = $usuario_id";
    $res_user = $conn->query($sql_user);
    $dados_user = $res_user->fetch_assoc();
    $dinheiro_atual = $dados_user['dinheiro'];

    // 3. Verifica se tem saldo
    if ($dinheiro_atual >= $preco_carro) {
        
        $conn->begin_transaction();

        try {
            // A. Desconta o dinheiro
            $novo_saldo = $dinheiro_atual - $preco_carro;
            $conn->query("UPDATE usuarios SET dinheiro = $novo_saldo WHERE id = $usuario_id");

            // B. Adiciona na garagem
            $conn->query("INSERT INTO garagem (usuario_id, modelo_id) VALUES ($usuario_id, $carro_id)");

            // C. Confirma
            $conn->commit();

            // SUCESSO: Redireciona para o MAPA, que é o Hub principal
            echo "<script>
                alert('Parabéns! Você comprou um {$dados_carro['nome']}! Visite sua Garagem.');
                window.location.href = '../index.php?p=mapa'; 
            </script>";

        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Erro na transação bancária.'); window.location.href = '../index.php?p=loja';</script>";
        }

    } else {
        echo "<script>alert('Você não tem dinheiro suficiente!'); window.location.href = '../index.php?p=loja';</script>";
    }
}
?>