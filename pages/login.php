<div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #111;">
    
    <div class="login-box" style="background: #1c1c1e; padding: 40px; border-radius: 10px; width: 320px; border: 1px solid #444; border-top: 4px solid var(--red-neon);">
        
        <h2 style="text-align: center; color: white; margin: 0;">ACESSO</h2>
        
        <div style="background: #333; padding: 10px; margin: 20px 0; text-align: center; border-radius: 5px;">
            <p style="color: #aaa; font-size: 12px; margin: 0 0 5px 0;">Não tem conta?</p>
            <a href="index.php?p=cadastro" style="color: var(--red-neon); font-weight: bold; text-transform: uppercase; font-size: 14px;">>> CRIAR CONTA AQUI <<</a>
        </div>

        <form action="pages/processa_login.php" method="POST">
            
            <label style="color: #ccc; font-size: 12px;">E-MAIL</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; margin-bottom: 15px; background: #000; border: 1px solid #555; color: white;">

            <label style="color: #ccc; font-size: 12px;">SENHA</label>
            <input type="password" name="senha" required style="width: 100%; padding: 10px; margin-bottom: 20px; background: #000; border: 1px solid #555; color: white;">

            <button type="submit" class="btn-red" style="width: 100%; padding: 15px; font-weight: bold; cursor: pointer;">ENTRAR</button>

        </form>

    </div>
</div>