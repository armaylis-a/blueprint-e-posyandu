document.addEventListener('click', function (event) {
    var target = event.target.closest('[data-print]');

    if (target) {
        window.print();
    }
});

(function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    var meta = document.querySelector('meta[name="sw-path"]');
    var serviceWorkerPath = meta ? meta.getAttribute('content') : 'sw.js';

    window.addEventListener('load', function () {
        navigator.serviceWorker.register(serviceWorkerPath).catch(function () {
            // PWA bersifat tambahan; kegagalan registrasi tidak memblokir aplikasi.
        });
    });
}());