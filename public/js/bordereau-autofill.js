(function () {
    function fieldOf(row, key) {
        return row.querySelector('[name*="[' + key + ']"]');
    }

    function copyValue(source, target) {
        target.value = source.value;
        target.dataset.autofill = '1';
    }

    window.DaoBordereauAutofill = {
        attach: function (container, keys) {
            // Au chargement (dossier duplique, ou formulaire rouvert), si la
            // 1ere ligne d'un tableau est deja renseignee pour une colonne
            // mais que d'autres lignes du meme tableau sont encore vides sur
            // cette colonne, on les pre-remplit tout de suite avec la valeur
            // de la 1ere ligne - comme si l'utilisateur venait de la retaper.
            // Une ligne qui a deja sa propre valeur n'est jamais touchee.
            container.querySelectorAll('.bordereau-section').forEach(function (sectionEl) {
                var rows = sectionEl.querySelectorAll('.bordereau-lines tr');
                if (rows.length < 2) return;
                var first = rows[0];

                keys.forEach(function (key) {
                    var source = fieldOf(first, key);
                    if (!source || source.value.trim() === '') return;

                    for (var i = 1; i < rows.length; i++) {
                        var target = fieldOf(rows[i], key);
                        if (target && target.value.trim() === '') {
                            copyValue(source, target);
                        }
                    }
                });
            });

            container.addEventListener('input', function (event) {
                var field = event.target;
                var key = keys.find(function (k) {
                    return (field.name || '').indexOf('[' + k + ']') !== -1;
                });
                if (!key) return;

                field.dataset.autofill = '';

                var row = field.closest('tr');
                var sectionEl = field.closest('.bordereau-section');
                if (!row || !sectionEl) return;

                sectionEl.querySelectorAll('.bordereau-lines tr').forEach(function (other) {
                    if (other === row) return;
                    var target = fieldOf(other, key);
                    if (!target) return;
                    if (target.value.trim() === '' || target.dataset.autofill === '1') {
                        copyValue(field, target);
                    }
                });
            });
        },

        fillNewRow: function (sectionEl, newRow, keys) {
            var first = sectionEl.querySelector('.bordereau-lines tr');
            if (!first || first === newRow) return;

            keys.forEach(function (key) {
                var source = fieldOf(first, key);
                var target = fieldOf(newRow, key);
                if (!source || !target || source.value.trim() === '') return;
                if (target.value.trim() === '' || target.dataset.autofill === '1') {
                    copyValue(source, target);
                }
            });
        }
    };
})();
