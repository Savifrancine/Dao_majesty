(function () {
    function setupPresence(options) {
        const { pingUrl, bannerId, intervalMs, typeDocumentId } = options;
        const banner = document.getElementById(bannerId);
        if (!banner) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        function ping() {
            const body = new URLSearchParams();
            if (typeDocumentId) {
                body.append('type_document_id', typeDocumentId);
            }

            fetch(pingUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token || '',
                    Accept: 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body.toString(),
            })
                .then((res) => (res.ok ? res.json() : null))
                .then((data) => {
                    if (!data) return;
                    const sameDocument = data.sameDocument || [];
                    const others = data.others || [];

                    if (sameDocument.length > 0) {
                        const noms = sameDocument.join(', ');
                        banner.style.background = '#fef2f2';
                        banner.style.borderColor = '#fca5a5';
                        banner.style.color = '#991b1b';
                        banner.textContent = `⚠️ ${noms} ${sameDocument.length > 1 ? 'modifient' : 'modifie'} actuellement cette même pièce — vos saisies pourraient s'écraser mutuellement.`;
                        banner.style.display = 'block';
                    } else if (others.length > 0) {
                        const noms = others.join(', ');
                        banner.style.background = '#fffbeb';
                        banner.style.borderColor = '#f59e0b';
                        banner.style.color = '#92400e';
                        banner.textContent = `ℹ️ ${noms} ${others.length > 1 ? 'consultent' : 'consulte'} aussi ce dossier en ce moment (autre pièce).`;
                        banner.style.display = 'block';
                    } else {
                        banner.style.display = 'none';
                        banner.textContent = '';
                    }
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
