<?php
// content_loader.php
session_start();
require_once 'config/db.php';

// Este script apenas inclui o conteúdo da página solicitada
// sem incluir cabeçalhos, rodapés ou o HTML principal
$page = isset($_GET['p']) ? $_GET['p'] : 'mapa';

if (isset($_SESSION['usuario_id'])) {
    $path = "pages/{$page}.php";
    if (file_exists($path)) {
        // Inclui a página (loja, garagem, etc.) para que o AJAX pegue o HTML
        include $path;
    } else {
        echo "<h1 style='color:red;'>404 - Conteúdo não encontrado</h1>";
    }
} else {
    echo "Acesso negado. Faça login.";
}
?>