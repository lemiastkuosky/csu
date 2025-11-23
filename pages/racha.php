<?php
// pages/racha.php
if(!isset($_SESSION['usuario_id'])) exit;

date_default_timezone_set('America/Sao_Paulo');
$hora = (int)date('H');

// Rachas funcionam das 20:00 até 05:00
$aberto_racha = ($hora >= 20 || $hora < 5);

// $aberto_racha = true; // Descomente para testar se for de dia
?>

<style>
    .racha-container {
        width: 100%; height: 100vh;
        background-color: #000;
        background-image: url('https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?q=80&w=1920');
        background-size: cover; background-position: center;
        display: flex; justify-content: center; align-items: center; flex-direction: column;
        text-align: center; color: white;
    }
    .racha-overlay {
        position: absolute; top:0; left:0; width:100%; height:100%;
        background: rgba(0,0,0,0.8); pointer-events: none;
    }
    .racha-content { position: relative; z-index: 10; }
</style>

<div class="racha-container">
    <div class="racha-overlay"></div>
    
    <div class="racha-content">
        <?php if($aberto_racha): ?>
            
            <i class="fas fa-flag-checkered" style="font-size: 60px; color: var(--red-neon); margin-bottom: 20px;"></i>
            <h1 style="font-family: 'Oswald', sans-serif; font-size: 40px; margin: 0;">ZONA DE RACHAS</h1>
            <p style="color: #aaa; margin-bottom: 30px;">A noite é uma criança. Escolha seu oponente.</p>
            
            <div style="border: 1px dashed #555; padding: 20px; color: #777;">
                [LISTA DE CORREDORES EM BREVE]
            </div>

        <?php else: ?>
            
            <i class="fas fa-sun" style="font-size: 60px; color: #f1c40f; margin-bottom: 20px;"></i>
            <h1 style="font-family: 'Oswald', sans-serif;">MUITO ARRISCADO!</h1>
            <p>A polícia está patrulhando forte agora.</p>
            <p style="color: #aaa; margin-bottom: 30px;">Volte depois das 20:00.</p>
            
            <a href="index.php?p=mapa" class="btn-red">FUGIR DAQUI</a>

        <?php endif; ?>
    </div>
</div>