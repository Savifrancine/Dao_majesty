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
