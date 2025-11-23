<?php
// config/db.php
$host = 'localhost';
$db   = 'game_rpg_carros';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro fatal: " . $conn->connect_error);
}

// Define charset para aceitar acentos
$conn->set_charset("utf8");
?>