import './bootstrap';

// --- SISTEMA DE AUDITORÍA DE CLICS (FRONTEND - CAJA NEGRA) ---
// Captura clics en botones, enlaces y elementos Livewire para enviarlos al log
document.addEventListener('click', function (event) {
    try {
        // Buscamos si el clic fue en un elemento interactivo o dentro de uno
        let target = event.target.closest('button, a, input, select, [wire\\:click]');
        
        if (target) {
            // Recopilar información útil
            const data = {
                tag: target.tagName,
                text: target.innerText ? target.innerText.substring(0, 50) : '', // Primeros 50 caracteres
                id: target.id || '',
                classes: target.className || '',
                href: target.href || '',
                wire_click: target.getAttribute('wire:click') || '',
                url: window.location.href
            };

            // Enviar "beacon" al servidor (no espera respuesta, no ralentiza al usuario)
            navigator.sendBeacon('/api/log-click', JSON.stringify(data));
        }
    } catch (e) {
        // Silencio en caso de error para no afectar al usuario
        console.error("Audit Log Error:", e);
    }
}, true); // UseCapture true para agarrar el evento antes que nadie

// ¡Eso es todo!
// DEJA ESTE ARCHIVO ASÍ DE SIMPLE.

// No importes Alpine aquí.
// Livewire lo cargará automáticamente (junto con su propio Alpine)
// a través de la etiqueta @livewireScripts en tu archivo de layout.