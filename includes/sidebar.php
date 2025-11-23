<div class="sidebar">
    <div class="logo-area">
        <div class="logo-text">
            Street Car<br>
            <span>UNDERGROUND</span>
        </div>
    </div>

    <div class="menu-items">
        <a href="index.php?p=mapa" class="menu-item <?php echo ($pagina == 'mapa') ? 'active' : ''; ?>">
            <i class="fas fa-map-marked-alt"></i> <span>Cidade</span>
        </a>
        
        <a href="index.php?p=garagem" class="menu-item <?php echo ($pagina == 'garagem') ? 'active' : ''; ?>">
            <i class="fas fa-car"></i> <span>Garagem</span>
        </a>
        
        <a href="index.php?p=loja" class="menu-item <?php echo ($pagina == 'loja') ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> <span>Loja</span>
        </a>

        <a href="logout.php" class="menu-item">
            <i class="fas fa-sign-out-alt"></i> <span>Sair</span>
        </a>
    </div>
</div>