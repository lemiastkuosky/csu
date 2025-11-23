// assets/js/audio.js

document.addEventListener('DOMContentLoaded', () => {
    initAudioSystem();
});

function initAudioSystem() {
    const bgm = document.getElementById('audio-bgm');
    const clickSound = document.getElementById('audio-click');
    const hoverSound = document.getElementById('audio-hover');
    const btnVolume = document.getElementById('btn-volume'); 

    let isMusicOn = localStorage.getItem('music_on');
    if (isMusicOn === null) isMusicOn = true;
    else isMusicOn = (isMusicOn === 'true');

    function renderState() {
        if (isMusicOn) {
            // LIGADO: Toca som e adiciona classe 'active'
            if (btnVolume) btnVolume.classList.add('active');
            if (bgm) {
                bgm.volume = 0.3;
                bgm.play().catch(e => {});
            }
        } else {
            // DESLIGADO: Remove classe 'active' e pausa
            if (btnVolume) btnVolume.classList.remove('active');
            if (bgm) bgm.pause();
        }
        localStorage.setItem('music_on', isMusicOn);
    }

    // Função Global
    window.toggleMusic = function() {
        isMusicOn = !isMusicOn;
        if (isMusicOn && clickSound) {
            clickSound.currentTime = 0;
            clickSound.play().catch(()=>{});
        }
        renderState();
    };

    function playHover() {
        if (isMusicOn && hoverSound) {
            hoverSound.currentTime = 0;
            hoverSound.volume = 0.4;
            hoverSound.play().catch(()=>{});
        }
    }
    
    // Sons em elementos interativos
    document.querySelectorAll('a, button, .pin, .music-btn, .level-badge').forEach(el => {
        el.addEventListener('mouseenter', playHover);
    });

    renderState();

    document.body.addEventListener('click', () => {
        if (isMusicOn && bgm && bgm.paused) {
            bgm.play().catch(()=>{});
        }
    }, { once: true });
}

// Debug Global
window.toggleDebug = function() {
    const modal = document.getElementById('debugModal');
    if(modal) modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'flex' : 'none';
};