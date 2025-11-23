<?php
// pages/mapa.php
if(!isset($_SESSION['usuario_id'])) exit;

$id = $_SESSION['usuario_id'];

// Verifica conexão antes de usar
if(isset($conn)) {
    $sql = "SELECT * FROM usuarios WHERE id = $id";
    $res = $conn->query($sql);
    $u = $res->fetch_assoc();
}

$nivel = $u['nivel'] ?? 1;
$xp = $u['xp'] ?? 0;
$cidade = $u['cidade'] ?? 'saopaulo'; 
$xp_proximo = $nivel * 100;
$pct_xp = ($xp_proximo > 0) ? ($xp / $xp_proximo) * 100 : 0;

// Imagem do Mapa (Local ou Fallback)
$arquivo_local = "assets/imgs/mapa.jpg";
$arquivo_fisico = realpath(__DIR__ . '/../assets/imgs/mapa.jpg');

if (file_exists($arquivo_fisico)) {
    $img_mapa = $arquivo_local;
} else {
    $img_mapa = "https://placehold.co/1800x1200/1a1a1a/555555?text=MAPA+NAO+ENCONTRADO";
}
?>

<style>
    :root { --red-neon: #d32f2f; }

    /* Container da Janela */
    .map-viewport {
        position: relative; width: 100%; height: 100%;
        background-color: #000; overflow: hidden; cursor: grab; touch-action: none;
    }
    .map-viewport:active { cursor: grabbing; }

    /* O Mapa */
    .map-world {
        position: absolute; top: 0; left: 0; width: 1800px; height: 1200px;
        background-image: url('<?php echo $img_mapa; ?>');
        background-size: cover; background-position: center;
        background-repeat: no-repeat;
        background-color: #111; 
        transition: filter 3s ease; transform-origin: 0 0; will-change: transform;
    }

    /* --- FILTROS DE CLIMA --- */
    .map-world.mode-day { filter: brightness(1.1) contrast(1.05) saturation(1.1); }
    .map-world.mode-night { filter: brightness(0.6) contrast(1.2) hue-rotate(220deg) grayscale(0.3); }
    .map-world.weather-rain { filter: brightness(0.9) grayscale(0.5) !important; }
    .map-world.weather-storm { filter: brightness(0.4) contrast(1.5) grayscale(0.9) !important; }
    .map-world.weather-cloudy { filter: brightness(0.8) grayscale(0.5) contrast(0.9) !important; }
    .map-world.weather-snow { filter: brightness(1.15) contrast(0.95) !important; }

    /* --- EFEITOS --- */
    .storm-flash { position: absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index: 50; background: white; opacity: 0; mix-blend-mode: hard-light; transition: opacity 0.05s linear; }
    .weather-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 5; opacity: 0; transition: opacity 2s ease; }
    
    /* Partículas */
    .fog-cloud { position: absolute; border-radius: 50%; filter: blur(40px); opacity: 0; pointer-events: none; z-index: 8; will-change: transform, opacity; background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 70%); }
    .map-world.mode-night ~ .fog-cloud, .map-world.mode-night .fog-cloud { background: radial-gradient(circle, rgba(150,170,200,0.5) 0%, rgba(150,170,200,0) 70%); }
    @keyframes fog-drift-real { 0% { transform: translateX(-400px) scale(0.8); opacity: 0; } 10% { opacity: var(--fog-opacity); } 90% { opacity: var(--fog-opacity); } 100% { transform: translateX(2200px) scale(1.5); opacity: 0; } }
    
    .snowflake { position: absolute; top: -20px; background: white; border-radius: 50%; opacity: 0.8; pointer-events: none; z-index: 6; will-change: transform; }
    .raindrop { position: absolute; top: -100px; width: 2px; height: 80px; background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0.5)); pointer-events: none; z-index: 6; will-change: transform; }
    @keyframes snow-drop-realistic { 0% { transform: translateY(0) translateX(0) rotate(0deg); opacity: 0.9; } 100% { transform: translateY(1300px) translateX(calc(50px + var(--drift))) rotate(360deg); opacity: 0.3; } }
    @keyframes rain-drop-realistic { 0% { transform: translateY(0); opacity: 0; } 20% { opacity: 1; } 100% { transform: translateY(1300px); opacity: 0; } }

    /* Tráfego */
    .traffic-light { position: absolute; height: 2px; width: 60px; border-radius: 2px; opacity: 0; pointer-events: none; z-index: 4; box-shadow: 0 0 8px currentColor; will-change: transform, left, right; }
    .traffic-light.headlight { background: linear-gradient(90deg, rgba(255,255,200,0), rgba(255,255,200,1)); color: #fffacd; }
    .traffic-light.taillight { background: linear-gradient(-90deg, rgba(255,0,0,0), rgba(255,0,0,1)); color: #ff0000; }
    .traffic-light.police { background: linear-gradient(90deg, blue, red, blue); animation: siren-strobe 0.1s infinite; width: 50px; height: 3px; box-shadow: 0 0 15px rgba(0,0,255,0.8); }
    .traffic-light.speeder { background: linear-gradient(90deg, transparent, #fff); box-shadow: 0 0 10px white; width: 80px; height: 2px; }
    @keyframes siren-strobe { 0% { background: linear-gradient(90deg, blue, transparent); } 100% { background: linear-gradient(90deg, transparent, red); } }
    .aircraft { position: absolute; width: 4px; height: 4px; border-radius: 50%; background: red; box-shadow: 0 0 5px red; z-index: 3; opacity: 0; pointer-events: none; animation: blink-aircraft 1.5s infinite; will-change: transform; }
    .aircraft.white { background: white; box-shadow: 0 0 5px white; animation-duration: 2s; }
    @keyframes blink-aircraft { 0%, 100% { opacity: 0; } 50% { opacity: 1; } }

    /* PINOS */
    .pin { position: absolute; display: flex; flex-direction: column; align-items: center; cursor: pointer; z-index: 1000; width: 60px; transition: transform 0.2s; }
    .pin:hover { transform: scale(1.3); z-index: 1050; }
    .pin-circle { width: 40px; height: 40px; border-radius: 50%; border: 2px solid white; display: flex; justify-content: center; align-items: center; font-size: 16px; color: white; background: rgba(0,0,0,0.8); box-shadow: 0 0 10px rgba(255,255,255,0.3); }
    .mode-night .pin-circle { box-shadow: 0 0 15px currentColor; border-color: #fff; text-shadow: 0 0 10px currentColor; }
    
    /* Correção Neon Dourado */
    .mode-night .c-gold .pin-circle { box-shadow: 0 0 20px #f1c40f; border-color: #fffacd; color: black; text-shadow: none; }

    .pin-lbl { background: black; color: white; padding: 2px 5px; border-radius: 3px; font-size: 9px; font-weight: bold; margin-top: 3px; text-transform: uppercase; white-space: nowrap; border-bottom: 2px solid transparent; text-shadow: 0 1px 2px black; opacity: 0; transform: translateY(10px); transition: 0.2s; pointer-events: none; }
    .pin:hover .pin-lbl { opacity: 1; transform: translateY(0); }
    .pin.locked { filter: grayscale(100%); opacity: 0.7; cursor: not-allowed; }
    .lock-icon { position: absolute; top: -5px; right: 5px; background: #d32f2f; color: white; width: 16px; height: 16px; font-size: 8px; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: 1px solid white; }
    
    /* Cores */
    .c-blue { color: #2980b9; } .c-blue .pin-circle { background: #2980b9; }
    .c-green { color: #27ae60; } .c-green .pin-circle { background: #27ae60; }
    .c-red { color: #c0392b; } .c-red .pin-circle { background: #c0392b; }
    .c-purple { color: #8e44ad; } .c-purple .pin-circle { background: #8e44ad; }
    .c-orange { color: #d35400; } .c-orange .pin-circle { background: #d35400; }
    .c-gray { color: #7f8c8d; } .c-gray .pin-circle { background: #7f8c8d; }
    .c-pink { color: #e056fd; } .c-pink .pin-circle { background: #e056fd; }
    .c-gold { color: #f1c40f; } .c-gold .pin-circle { background: #f1c40f; color:black; }
    .c-teal { color: #1abc9c; } .c-teal .pin-circle { background: #1abc9c; }

    /* EDITOR */
    .map-world.is-editing .pin { border: 2px dashed yellow; background: rgba(255, 255, 0, 0.2); cursor: move !important; }
    .map-world.is-editing { cursor: crosshair !important; }
    #editor-panel { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #111; border: 1px solid #fff; padding: 15px; display: none; flex-direction: column; align-items: center; gap: 10px; z-index: 100000; box-shadow: 0 0 20px black; width: 90%; max-width: 400px; }
    
    .hud-fixed-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 100; }
</style>

<div class="map-viewport" id="mapViewport">
    
    <div id="editor-panel">
        <strong style="color:yellow;">MODO EDIÇÃO ATIVADO</strong>
        <small>Arraste e Salve.</small>
        <div style="display:flex; gap:10px; width:100%;">
            <button class="d-btn" onclick="saveMapToDB()" style="flex:1; background:#2ecc71; color:white;">SALVAR</button>
            <button class="d-btn" onclick="window.toggleMapEditor()" style="flex:1; background:#c0392b; color:white;">CANCELAR</button>
        </div>
    </div>

    <div class="map-world" id="mapWorld">
        <div class="weather-overlay fog-overlay"></div>
        <div class="storm-flash"></div> 

        <?php
        if (!function_exists('CriarPino')) {
            function CriarPino($link, $icone, $titulo, $left, $top, $cor, $nivel_req, $nivel_atual) {
                $bloqueado = ($nivel_atual < $nivel_req);
                $parts = explode('=', $link);
                $page_name = end($parts);
                $dataAttrs = "data-link='$link' data-icone='$icone' data-titulo='$titulo' data-cor='$cor' data-lvl='$nivel_req'";
                if ($bloqueado) {
                    echo '<div class="pin locked '.$cor.'" '.$dataAttrs.' style="left:'.$left.'px; top:'.$top.'px;" onclick="if(!window.editingMode) alert(\'Bloqueado! Requer Nível '.$nivel_req.'\')"><div class="lock-icon"><i class="fas fa-lock"></i></div><div class="pin-circle"><i class="'.$icone.'"></i></div><div class="pin-lbl">REQ. LVL '.$nivel_req.'</div></div>';
                } else {
                    echo '<div class="pin '.$cor.'" '.$dataAttrs.' style="left:'.$left.'px; top:'.$top.'px;" onclick="if(!window.editingMode) window.location.href=\'index.php?p='.$page_name.'\'"><div class="pin-circle"><i class="'.$icone.'"></i></div><div class="pin-lbl">'.$titulo.'</div></div>';
                }
            }
        }
        ?>
        
        <?php
        // Tenta ler do banco. Se falhar, usa array fixo
        $pinos_carregados = false;
        if(isset($conn)) {
            $sql_mapa = "SELECT * FROM mapa_locais";
            $res_mapa = $conn->query($sql_mapa);
            if ($res_mapa && $res_mapa->num_rows > 0) {
                while($pino = $res_mapa->fetch_assoc()) {
                    CriarPino($pino['link'], $pino['icone'], $pino['titulo'], $pino['pos_x'], $pino['pos_y'], $pino['cor'], $pino['nivel_req'], $nivel);
                }
                $pinos_carregados = true;
            }
        }
        
        if (!$pinos_carregados) {
             // Fallback se banco estiver vazio
             CriarPino('index.php?p=garagem', 'fas fa-warehouse', 'MINHA CASA', 150, 180, 'c-blue', 1, $nivel);
             CriarPino('index.php?p=trabalho', 'fas fa-briefcase', 'EMPREGOS', 240, 420, 'c-gray', 1, $nivel);
        }
        ?>
    </div>
</div>

<script>
    // === SETUP & ARRASTO ===
    const viewport = document.getElementById('mapViewport');
    const world = document.getElementById('mapWorld');
    const flashOverlay = document.querySelector('.storm-flash');
    let isDown = false; let startX, startY, currentX = 0, currentY = 0;

    function checkBounds() {
        const vpW = viewport.offsetWidth; const vpH = viewport.offsetHeight;
        const minX = vpW - 1800; const minY = vpH - 1200;
        if (vpW > 1800) currentX = (vpW - 1800) / 2; else { if (currentX > 0) currentX = 0; if (currentX < minX) currentX = minX; }
        if (vpH > 1200) currentY = (vpH - 1200) / 2; else { if (currentY > 0) currentY = 0; if (currentY < minY) currentY = minY; }
        world.style.transform = `translate(${currentX}px, ${currentY}px)`;
    }
    viewport.addEventListener('mousedown', (e) => { if(window.editingMode && e.target.closest('.pin')) return; isDown = true; viewport.style.cursor = 'grabbing'; startX = e.pageX - currentX; startY = e.pageY - currentY; });
    viewport.addEventListener('mouseleave', () => { isDown = false; viewport.style.cursor = 'grab'; });
    viewport.addEventListener('mouseup', () => { isDown = false; viewport.style.cursor = 'grab'; });
    viewport.addEventListener('mousemove', (e) => { if (!isDown) return; e.preventDefault(); currentX = e.pageX - startX; currentY = e.pageY - startY; checkBounds(); });
    
    window.onload = function() { 
        currentX = (viewport.offsetWidth - 1800) / 2; currentY = (viewport.offsetHeight - 1200) / 2; 
        checkBounds(); 
        
        // --- CORREÇÃO: INICIALIZA TEMPERATURA IMEDIATAMENTE ---
        // Evita o "--" inicial chamando a função com um valor padrão
        updateTopBarWeather(currentWeather);

        syncGameEnvironment(); 
        startTraffic();
    };
    window.onresize = checkBounds;

    // === EDITOR ===
    window.editingMode = false; let draggedPin = null;
    window.toggleMapEditor = function() {
        window.editingMode = !window.editingMode;
        const panel = document.getElementById('editor-panel');
        if(window.editingMode) { world.classList.add('is-editing'); panel.style.display = 'flex'; enablePinDragging(); } 
        else { world.classList.remove('is-editing'); panel.style.display = 'none'; }
    };
    function enablePinDragging() {
        document.querySelectorAll('.pin').forEach(pin => {
            pin.onmousedown = function(e) {
                if(!window.editingMode) return;
                e.preventDefault(); draggedPin = pin;
                let shiftX = e.clientX - pin.getBoundingClientRect().left;
                let shiftY = e.clientY - pin.getBoundingClientRect().top;
                function moveAt(pageX, pageY) {
                    let worldRect = world.getBoundingClientRect();
                    let left = Math.round(pageX - worldRect.left - shiftX);
                    let top = Math.round(pageY - worldRect.top - shiftY);
                    pin.style.left = left + 'px'; pin.style.top = top + 'px';
                }
                moveAt(e.pageX, e.pageY);
                function onMouseMove(e) { moveAt(e.pageX, e.pageY); }
                document.addEventListener('mousemove', onMouseMove);
                pin.onmouseup = function() { document.removeEventListener('mousemove', onMouseMove); pin.onmouseup = null; draggedPin = null; };
            };
            pin.ondragstart = function() { return false; };
        });
    }
    window.saveMapToDB = function() {
        const pins = [];
        document.querySelectorAll('.pin').forEach(pin => { pins.push({ link: pin.dataset.link, x: parseInt(pin.style.left), y: parseInt(pin.style.top) }); });
        fetch('api/salvar_mapa.php', { method: 'POST', body: JSON.stringify(pins), headers: { 'Content-Type': 'application/json' } })
        .then(r => r.json()).then(d => { if(d.status === 'sucesso') { alert('Salvo!'); window.toggleMapEditor(); } else alert('Erro.'); });
    };

    // CLIMA
    const weatherTypes = ['limpo', 'limpo', 'chuva', 'nublado', 'tempestade', 'neve']; 
    let currentWeather = 'limpo'; let particleInterval = null; let stormTimeout = null;

    function syncGameEnvironment() {
        fetch('api/clima.php').then(r => r.json()).then(data => {
            if (data.modo === 'dia') { world.classList.remove('mode-night'); world.classList.add('mode-day'); } 
            else { world.classList.remove('mode-day'); world.classList.add('mode-night'); }
            
            // Só atualiza se mudou, para não piscar
            if (data.clima !== currentWeather) { 
                currentWeather = data.clima; 
                applyWeatherClasses(); 
            }
            // Garante que a temperatura esteja sempre lá
            if(typeof updateTopBarWeather === "function") updateTopBarWeather(currentWeather);
            
        }).catch(e => {
            console.log("Modo Offline: Usando clima local");
            // Fallback local se a API falhar
            if(typeof updateTopBarWeather === "function") updateTopBarWeather(currentWeather);
        });
    }

    function updateTopBarWeather(type) {
        const iconEl = document.getElementById('weather-icon');
        const tempEl = document.getElementById('weather-temp');
        const boxEl = document.getElementById('weather-box');
        
        // Se os elementos da Topbar não existirem ainda, sai.
        if(!iconEl || !tempEl) return;

        const isNight = world.classList.contains('mode-night');
        
        let iconClass = isNight ? 'fa-moon' : 'fa-sun'; 
        let color = isNight ? '#f1c40f' : '#f39c12'; 
        let tempBase = isNight ? 18 : 28; 
        let weatherText = 'Céu Limpo';

        if(type === 'chuva') { iconClass = isNight ? 'fa-cloud-moon-rain' : 'fa-cloud-sun-rain'; color='#3498db'; tempBase=isNight ? 15 : 22; weatherText='Chuvoso'; }
        if(type === 'tempestade') { iconClass = 'fa-bolt'; color='#e74c3c'; tempBase=isNight ? 14 : 19; weatherText='Tempestade'; }
        if(type === 'nublado') { iconClass = isNight ? 'fa-cloud-moon' : 'fa-cloud-sun'; color='#bdc3c7'; tempBase=isNight ? 17 : 24; weatherText='Neblina'; }
        if(type === 'neve') { iconClass = 'fa-snowflake'; color='#fff'; tempBase=isNight ? -5 : 0; weatherText='Neve'; }

        // Cálculo simples para variar a temperatura um pouquinho
        const finalTemp = Math.floor(tempBase + (Math.random() * 2 - 1));
        
        iconEl.className = 'fas ' + iconClass; 
        iconEl.style.color = color; 
        
        // Só atualiza o texto se ainda estiver com traços, ou aleatoriamente para não ficar mudando toda hora
        if(tempEl.innerText === '--°C' || Math.random() > 0.9) {
            tempEl.innerText = finalTemp + '°C'; 
        }
        
        if(boxEl) boxEl.title = weatherText;
    }

    function applyWeatherClasses() {
        world.classList.remove('weather-rain', 'weather-storm', 'weather-cloudy', 'weather-snow');
        if(particleInterval) clearInterval(particleInterval); 
        if(stormTimeout) clearTimeout(stormTimeout); 
        document.querySelectorAll('.snowflake, .raindrop, .fog-cloud').forEach(el => el.remove());
        flashOverlay.style.opacity = 0; 

        if(currentWeather === 'chuva') { world.classList.add('weather-rain'); startRaining(80); }
        if(currentWeather === 'tempestade') { world.classList.add('weather-storm'); startRaining(20); triggerLightning(); }
        if(currentWeather === 'nublado') { world.classList.add('weather-cloudy'); startFog(); }
        if(currentWeather === 'neve') { world.classList.add('weather-snow'); startSnowing(); }
        
        // Atualiza visual imediatamente
        updateTopBarWeather(currentWeather);
    }

    function createFogCloud() { const f=document.createElement('div'); f.className='fog-cloud'; f.style.width='500px'; f.style.height='500px'; f.style.top=Math.random()*80+'%'; f.style.animation='fog-drift-real 20s linear'; world.appendChild(f); setTimeout(()=>f.remove(),20000); }
    function createSnowflake() { const f=document.createElement('div'); f.className='snowflake'; f.style.width='4px'; f.style.height='4px'; f.style.left=Math.random()*100+'%'; f.style.animation='snow-drop-realistic 10s linear'; world.appendChild(f); setTimeout(()=>f.remove(),10000); }
    function createRaindrop() { const f=document.createElement('div'); f.className='raindrop'; f.style.left=Math.random()*100+'%'; f.style.animation='rain-drop-realistic 0.4s linear'; world.appendChild(f); setTimeout(()=>f.remove(),400); }
    function startSnowing() { particleInterval = setInterval(createSnowflake, 200); }
    function startRaining(s) { particleInterval = setInterval(createRaindrop, s); }
    function startFog() { particleInterval = setInterval(createFogCloud, 1000); }
    function triggerLightning() { if(currentWeather!=='tempestade')return; flashOverlay.style.opacity=0.9; setTimeout(()=>flashOverlay.style.opacity=0,80); setTimeout(()=>flashOverlay.style.opacity=0.6,150); setTimeout(()=>flashOverlay.style.opacity=0,300); stormTimeout=setTimeout(triggerLightning,Math.random()*5000+1500); }

    function startTraffic() { setInterval(() => createTrafficCar('normal'), 1200); setInterval(() => { if(Math.random() > 0.7) triggerChase(); }, 15000); setInterval(() => { if(Math.random() > 0.6) createAircraft(); }, 10000); }
    function createTrafficCar(type='normal') {
        const car = document.createElement('div'); car.classList.add('traffic-light');
        const direction = Math.random() > 0.5 ? 'right' : 'left';
        const speed = (type === 'chase') ? '1s' : (Math.random() * 4 + 3 + 's');
        const posY = Math.random() * 90 + 5 + '%'; car.style.top = posY;
        if (direction === 'right') { if(type==='normal') car.classList.add('headlight'); else if(type==='chase') car.classList.add('speeder'); else if(type==='cop') car.classList.add('police'); car.style.left = '-100px'; car.style.transition = `left ${speed} linear, opacity 0.5s`; setTimeout(() => { car.style.opacity = 0.7; car.style.left = '1900px'; }, 50); } 
        else { if(type==='normal') car.classList.add('taillight'); else if(type==='chase') car.classList.add('speeder'); else if(type==='cop') car.classList.add('police'); car.style.left = '1900px'; car.style.transition = `left ${speed} linear, opacity 0.5s`; setTimeout(() => { car.style.opacity = 0.7; car.style.left = '-100px'; }, 50); }
        world.appendChild(car); setTimeout(() => car.remove(), parseFloat(speed) * 1000 + 100);
    }
    function triggerChase() { createTrafficCar('chase'); setTimeout(() => { createTrafficCar('cop'); }, 150); }
    function createAircraft() { const p = document.createElement('div'); p.className = 'aircraft ' + (Math.random()>0.5?'white':''); p.style.top = Math.random()*40+'%'; p.style.left = '-10px'; p.style.transition = 'left 30s linear'; world.appendChild(p); setTimeout(()=>p.style.left='1850px',100); setTimeout(()=>p.remove(),30000); }

    window.forceWeather = function(type) { currentWeather = type; applyWeatherClasses(); };
    window.forceTime = function(mode) { 
        if(mode === 'dia') { world.classList.remove('mode-night'); world.classList.add('mode-day'); }
        else { world.classList.remove('mode-day'); world.classList.add('mode-night'); }
        updateTopBarWeather(currentWeather);
    };

    setInterval(syncGameEnvironment, 30000);
</script>