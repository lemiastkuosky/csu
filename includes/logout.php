<?php
// logout.php

// Inicia a sessão (necessário para manipulá-la)
session_start();

// Destrói a sessão do usuário
session_unset();
session_destroy();

// Redireciona o usuário para a página de login
// index.php?p=login garante que o seu roteador index.php exiba a tela de login
header("Location: index.php?p=login"); 
exit();
?>