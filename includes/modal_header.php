<?php
$titulo_exibir = isset($page_title) ? $page_title : "MENU";
?>

<div class="modal-fixed-header">
    <h3 class="modal-title"><?php echo strtoupper($titulo_exibir); ?></h3>
    
    <a href="index.php?p=mapa" class="modal-close-btn" title="Fechar">
        <i class="fas fa-times"></i>
    </a>
</div>