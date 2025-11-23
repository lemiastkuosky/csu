<?php
// pages/trabalho.php
if(!isset($_SESSION['usuario_id'])) exit;

// Fuso Horário
date_default_timezone_set('America/Sao_Paulo');
$hora_atual = (int)date('H'); 

// Horário de Funcionamento: 08h as 18h
$abre = 8;
$fecha = 18;
$esta_aberto = ($hora_atual >= $abre && $hora_atual < $fecha);

// $esta_aberto = true; // Descomente para testar se for de noite
?>

<style>
    .job-container {
        width: 100%; height: 100vh;
        background: #111; color: white;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .closed-sign {
        border: 4px solid #e74c3c; padding: 20px 40px;
        color: #e74c3c; font-family: 'Oswald', sans-serif; font-size: 40px;
        text-transform: uppercase; transform: rotate(-5deg);
        box-shadow: 0 0 30px rgba(231, 76, 60, 0.2); background: rgba(0,0,0,0.8);
    }
    .job-card {
        background: #1c1c1e; border: 1px solid #333; width: 320px;
        padding: 20px; border-radius: 10px; margin-bottom: 20px;
        text-align: center; transition: 0.2s;
    }
    .job-card:hover { border-color: #2ecc71; transform: translateY(-5px); }
    
    .job-icon { font-size: 40px; color: #2ecc71; margin-bottom: 10px; }
    .job-title { font-family: 'Oswald', sans-serif; font-size: 22px; margin-bottom: 5px; }
    .job-pay { color: #aaa; font-size: 14px; margin-bottom: 15px; }
    
    .btn-work {
        background: #2ecc71; color: #000; border: none; padding: 10px 20px;
        font-weight: bold; font-family: 'Oswald', sans-serif; cursor: pointer;
        width: 100%; border-radius: 5px; text-transform: uppercase;
    }
    .btn-work:hover { background: #27ae60; }
</style>

<div class="job-container">

    <?php if ($esta_aberto): ?>
        <h1 style="font-family:'Oswald', sans-serif; margin-bottom:30px;">AGÊNCIA DE EMPREGOS</h1>

        <div class="job-card">
            <div class="job-icon"><i class="fas fa-box-open"></i></div>
            <div class="job-title">ENTREGADOR DE PEÇAS</div>
            <div class="job-pay">Salário: R$ 50,00 / entrega | +10 XP</div>
            <form action="pages/processa_trabalho.php" method="POST">
                <input type="hidden" name="tipo" value="entregador">
                <button class="btn-work">TRABALHAR</button>
            </form>
        </div>

        <div class="job-card">
            <div class="job-icon"><i class="fas fa-soap"></i></div>
            <div class="job-title">LAVADOR DE CARROS</div>
            <div class="job-pay">Salário: R$ 80,00 / lavagem | +15 XP</div>
            <form action="pages/processa_trabalho.php" method="POST">
                <input type="hidden" name="tipo" value="lavador">
                <button class="btn-work">TRABALHAR</button>
            </form>
        </div>
        
        <small style="color:#555; margin-top:20px;">Fechamos às 18:00.</small>

    <?php else: ?>
        <div class="closed-sign">FECHADO</div>
        <p style="margin-top: 20px; color: #aaa; text-align: center;">
            Horário de funcionamento: 08:00 às 18:00<br>
            <span style="color: #555;">Agora são <?php echo $hora_atual; ?>:00</span>
        </p>
        <a href="index.php?p=mapa" class="btn-red" style="margin-top:30px;">VOLTAR PARA RUA</a>
    <?php endif; ?>

</div>