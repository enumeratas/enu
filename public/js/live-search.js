(function () {
    function bindForm(form) {
        if (form.dataset.liveBound === '1') {
            return;
        }
        const input = form.querySelector('[data-live-query]');
        const resultsId = form.getAttribute('data-live-results');
        if (!input || !resultsId) {
            return;
        }
        form.dataset.liveBound = '1';

        let timer = null;
        let request = null;

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });

        input.addEventListener('input', function () {
            window.clearTimeout(timer);
            timer = window.setTimeout(run, 200);
        });

        async function run() {
            const params = new URLSearchParams(new FormData(form));
            params.delete('page');
            Array.from(params.keys()).forEach(function (key) {
                if (params.get(key) === '') {
                    params.delete(key);
                }
            });
            const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            if (request) {
                request.abort();
            }
            request = new AbortController();
            try {
                const response = await fetch(url, {
                    signal: request.signal,
                    cache: 'no-store',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                if (!response.ok) {
                    return;
                }
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const next = doc.getElementById(resultsId);
                const current = document.getElementById(resultsId);
                if (!next || !current) {
                    return;
                }
                current.replaceWith(next);
                history.replaceState(null, '', url);
                document.dispatchEvent(new CustomEvent('bis-live-results', { detail: { id: resultsId } }));
            } catch (error) {
                if (error && error.name !== 'AbortError') {
                    return;
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-live-results]').forEach(bindForm);
    });
})();
