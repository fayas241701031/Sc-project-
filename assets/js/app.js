document.addEventListener('DOMContentLoaded', function () {
    if (window.location.pathname === '/index.php' || window.location.pathname === '/') {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();
        oscillator.connect(gain);
        gain.connect(audioContext.destination);
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
        gain.gain.setValueAtTime(0.03, audioContext.currentTime);
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.12);
    }
    document.querySelectorAll('form[action*="/cart.php"] button[type="submit"]').forEach(function (button) {
        button.addEventListener('click', function () {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(520, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.05, audioCtx.currentTime);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.13);
        });
    });
    document.querySelectorAll('form#checkoutForm, form#loginForm, form#signupForm').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const invalid = [...form.querySelectorAll('input[required], textarea[required], select[required]')].some(function (field) {
                return !field.value.trim();
            });
            if (invalid) {
                event.preventDefault();
                const toastEl = document.createElement('div');
                toastEl.className = 'toast align-items-center text-bg-danger border-0';
                toastEl.innerHTML = '<div class="d-flex"><div class="toast-body">Please fill all required fields.</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast" aria-label="Close"></button></div>';
                document.body.appendChild(toastEl);
                new mdb.Toast(toastEl).show();
            }
        });
    });
});
