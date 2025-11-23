<?php
// includes/game_engine.php

// Se não tiver conexão, conecta
if (!isset($conn)) {
    require_once __DIR__ . '/../config/db.php';
}

// Se não tiver sessão, inicia
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Variáveis Globais do Jogo (Defaults)
$CLIMA_GLOBAL = 'limpo';
$IS_NIGHT = false;
$HORA_JOGO = date('H:i');

// Se usuário estiver logado, tenta pegar configs específicas
if (isset($_SESSION['usuario_id'])) {
    $uid = $_SESSION['usuario_id'];
    
    // Tenta buscar dados vitais
    // Usamos @ para suprimir warnings leves se a coluna não existir
    $sql_engine = "SELECT * FROM usuarios WHERE id = $uid";
    $res_engine = $conn->query($sql_engine);
    
    if ($res_engine && $res_engine->num_rows > 0) {
        $u_engine = $res_engine->fetch_assoc();
        
        // --- CORREÇÃO DO ERRO DE DATA ---
        // Se 'hora_offset' não existir, usa 0
        $offset = isset($u_engine['hora_offset']) ? $u_engine['hora_offset'] : 0;
        
        try {
            // Tenta criar a data com o offset
            // Se $offset for inválido, vai pro catch
            if(is_numeric($offset)) {
                $now = new DateTime();
                if($offset != 0) {
                    $now->modify("+$offset seconds");
                }
                $HORA_JOGO = $now->format('H:i');
                $hora_h = (int)$now->format('H');
                
                // Define dia/noite pelo PHP tbm (backup)
                $IS_NIGHT = ($hora_h < 6 || $hora_h >= 18);
            }
        } catch (Exception $e) {
            // Se der erro, usa hora do servidor e segue o jogo
            $HORA_JOGO = date('H:i');
            $IS_NIGHT = false;
        }
    }
}

// Busca Clima Global do Banco (Sistema Novo)
$sql_clima = "SELECT clima_atual FROM config_jogo WHERE id = 1";
$res_clima = $conn->query($sql_clima);
if($res_clima && $row_clima = $res_clima->fetch_assoc()) {
    $CLIMA_GLOBAL = $row_clima['clima_atual'];
}
?>