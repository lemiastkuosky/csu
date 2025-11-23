<div style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #0f0f0f;">
    
    <div class="cadastro-box" style="background: #1c1c1e; padding: 30px; border-radius: 8px; width: 350px; border-top: 4px solid var(--red-neon); box-shadow: 0 0 30px rgba(0,0,0,0.5);">
        
        <h2 style="color: white; text-align: center; font-family: 'Oswald', sans-serif;">NOVA HABILITAÇÃO</h2>
        <p style="text-align: center; color: #555; font-size: 12px; margin-bottom: 20px;">Preencha seus dados para começar a pilotar.</p>
        
        <form action="pages/processa_cadastro.php" method="POST">
            
            <div style="margin-bottom: 15px;">
                <label style="color: #aaa; font-size: 11px; font-weight: bold;">NOME DO PILOTO (NICK)</label>
                <input type="text" name="nome" required style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #333; color: white; box-sizing: border-box; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="color: #aaa; font-size: 11px; font-weight: bold;">E-MAIL</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #333; color: white; box-sizing: border-box; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="color: #aaa; font-size: 11px; font-weight: bold;">SENHA</label>
                <input type="password" name="senha" required style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #333; color: white; box-sizing: border-box; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="color: #aaa; font-size: 11px; font-weight: bold;">CIDADE INICIAL</label>
                <select name="cidade" style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #333; color: white; border-radius: 4px;">
                    <option value="saopaulo">São Paulo (Selva de Pedra)</option>
                    <option value="rio">Rio de Janeiro (Orla)</option>
                </select>
            </div>

            <button type="submit" class="btn-red" style="width: 100%; font-weight: bold;">EMITIR CNH</button>
        </form>

        <div style="margin-top: 20px; text-align: center; border-top: 1px solid #333; padding-top: 15px;">
            <a href="index.php?p=login" style="color: #777; font-size: 13px; text-decoration: none;">Já tenho conta (Login)</a>
        </div>

    </div>
</div>