(function () {
    function setupPresence(options) {
        const { pingUrl, bannerId, intervalMs } = options;
        const banner = document.getElementById(bannerId);
        if (!banner) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        function ping() {
            fetch(pingUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token || '', Accept: 'application/json' },
            })
                .then((res) => (res.ok ? res.json() : null))
                .then((data) => {
                    const others = data?.others || [];
                    if (others.length === 0) {
                        banner.style.display = 'none';
                        banner.textContent = '';
                        return;
                    }
                    const noms = others.join(', ');
                    banner.textContent = `⚠️ ${noms} ${others.length > 1 ? 'consultent' : 'consulte'} aussi ce dossier en ce moment.`;
                    banner.style.display = 'block';
                })
                .catch(() => {
                    // Silencieux : un echec de sondage ne doit pas gener l'utilisateur.
                });
        }

        ping();
        setInterval(ping, intervalMs || 20000);
    }

    window.DaoDossierPresence = { setup: setupPresence };
})();
