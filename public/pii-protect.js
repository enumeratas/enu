(function() {
    document.addEventListener('dragstart', function(event) {
        if (event.target && event.target.classList && event.target.classList.contains('pii-text')) {
            event.preventDefault();
        }
    });
})();
