

function liveSearch(inputId, tableBodyId, searchUrl) {
    const input = document.getElementById(inputId);
    const tbody = document.getElementById(tableBodyId);
    if (!input || !tbody) return;

    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const query = this.value.trim();
        timer = setTimeout(() => {
            tbody.style.opacity = '0.5';
            fetch(searchUrl + '?query=' + encodeURIComponent(query))
                .then(r => r.text())
                .then(html => {
                    tbody.innerHTML = html;
                    tbody.style.opacity = '1';
                })
                .catch(() => { tbody.style.opacity = '1'; });
        }, 300);
    });
}

document.addEventListener('click', function (e) {
    if (e.target.closest('.btn-confirm-delete')) {
        const btn = e.target.closest('.btn-confirm-delete');
        const msg = btn.dataset.message || 'Are you sure you want to delete this record?';
        if (!confirm(msg)) e.preventDefault();
    }
});

setTimeout(() => {
    document.querySelectorAll('.alert-box').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 3500);
