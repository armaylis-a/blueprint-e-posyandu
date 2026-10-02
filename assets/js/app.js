document.addEventListener('click', function (event) {
    var target = event.target.closest('[data-print]');

    if (target) {
        window.print();
    }
});