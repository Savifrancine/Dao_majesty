(function () {
    function normalize(str) {
        return String(str || '')
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    }

    function resolveColumnMapping(headers, fieldSynonyms) {
        const normalizedHeaders = headers.map(normalize);
        const usedColumns = new Set();
        const mapping = {};

        Object.keys(fieldSynonyms).forEach((fieldKey) => {
            const synonyms = fieldSynonyms[fieldKey].map(normalize);
            let bestCol = -1;
            synonyms.forEach((syn) => {
                if (bestCol !== -1) return;
                normalizedHeaders.forEach((header, idx) => {
                    if (bestCol !== -1 || usedColumns.has(idx)) return;
                    if (header === syn || header.includes(syn) || syn.includes(header)) {
                        bestCol = idx;
                    }
                });
            });
            if (bestCol !== -1) {
                mapping[bestCol] = fieldKey;
                usedColumns.add(bestCol);
            }
        });

        return mapping;
    }

    function mapRows(headers, rows, fieldSynonyms) {
        const mapping = resolveColumnMapping(headers, fieldSynonyms);
        return rows
            .map((row) => {
                const obj = {};
                Object.keys(mapping).forEach((colIndex) => {
                    obj[mapping[colIndex]] = (row[colIndex] ?? '').toString().trim();
                });
                return obj;
            })
            .filter((obj) => Object.keys(obj).length > 0 && Object.values(obj).some((v) => v !== ''));
    }

    function isRowEmpty(row) {
        return Array.from(row.querySelectorAll('input, textarea')).every((f) => !f.value || !f.value.trim());
    }

    function fillSection(sectionEl, mappedRows, selectors) {
        const addLineButton = sectionEl.querySelector(selectors.addLineButton);
        const tbody = sectionEl.querySelector(selectors.lines);
        if (!addLineButton || !tbody || mappedRows.length === 0) {
            return 0;
        }

        const existingRows = Array.from(tbody.querySelectorAll('tr'));
        const reuseFirstRow = existingRows.length === 1 && isRowEmpty(existingRows[0]);
        const clicksNeeded = reuseFirstRow ? mappedRows.length - 1 : mappedRows.length;

        for (let i = 0; i < clicksNeeded; i++) {
            addLineButton.click();
        }

        const allRows = Array.from(tbody.querySelectorAll('tr'));
        const targetRows = reuseFirstRow ? allRows : allRows.slice(allRows.length - mappedRows.length);

        targetRows.forEach((row, i) => {
            const data = mappedRows[i];
            Object.keys(data).forEach((key) => {
                const field = row.querySelector(`[name*="[${key}]"]`);
                if (!field) return;
                field.value = data[key];
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        return targetRows.length;
    }

    function uploadFile(file, importUrl) {
        const formData = new FormData();
        formData.append('fichier', file);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        return fetch(importUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token || '', Accept: 'application/json' },
            body: formData,
        }).then(async (res) => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || "Erreur lors de la lecture du fichier.");
            }
            return data;
        });
    }

    // Remplissage automatique inter-tableaux : quand une désignation saisie
    // dans un tableau correspond à une déjà connue ailleurs dans le dossier
    // (import initial ou autre document déjà rempli), les champs vides de la
    // ligne (quantité, prix unitaire, unité, site, date...) sont complétés.
    function buildPrefillIndex(lines) {
        const index = {};
        (lines || []).forEach((line) => {
            const key = normalize(line.designation);
            if (!key) return;
            const known = index[key] || {};
            Object.keys(line).forEach((field) => {
                if (field === 'designation') return;
                const value = line[field];
                if (value !== null && value !== undefined && value !== '' && known[field] === undefined) {
                    known[field] = value;
                }
            });
            index[key] = known;
        });
        return index;
    }

    function setupCrossTableFill(lines) {
        const index = buildPrefillIndex(lines);
        if (Object.keys(index).length === 0) return;

        document.addEventListener('blur', (event) => {
            const target = event.target;
            if (!target.matches || !target.matches('[name*="[designation]"]')) return;

            const match = index[normalize(target.value)];
            const row = target.closest('tr');
            if (!match || !row) return;

            Object.keys(match).forEach((field) => {
                const input = row.querySelector(`[name*="[${field}]"]`);
                if (input && !input.value) {
                    input.value = match[field];
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        }, true);
    }

    window.DaoTableImport = {
        setupCrossTableFill,
        setup(options) {
            const { buttonId, statusId, sectionsContainerId, fieldSynonyms, importUrl } = options;
            const selectors = {
                section: options.sectionSelector || '.bordereau-section',
                lines: options.linesSelector || '.bordereau-lines',
                addLineButton: options.addLineButtonSelector || '.addBordereauLine',
            };
            const button = document.getElementById(buttonId);
            const sectionsContainer = document.getElementById(sectionsContainerId);
            if (!button || !sectionsContainer) return;

            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.csv,.xlsx,.txt';
            input.style.display = 'none';
            button.insertAdjacentElement('afterend', input);

            const statusEl = statusId ? document.getElementById(statusId) : null;
            const setStatus = (message, isError) => {
                if (!statusEl) return;
                statusEl.textContent = message;
                statusEl.style.color = isError ? '#dc2626' : '#059669';
            };

            button.addEventListener('click', () => input.click());

            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) return;

                setStatus('Lecture du fichier…', false);

                uploadFile(file, importUrl)
                    .then(({ headers, rows }) => {
                        if (!headers || !headers.length || !rows || !rows.length) {
                            setStatus('Aucune donnée trouvée dans ce fichier.', true);
                            return;
                        }

                        const mappedRows = mapRows(headers, rows, fieldSynonyms);
                        if (!mappedRows.length) {
                            setStatus("Colonnes non reconnues : vérifiez les intitulés (désignation, quantité, unité...).", true);
                            return;
                        }

                        const targetSection = sectionsContainer.querySelector(selectors.section);
                        if (!targetSection) {
                            setStatus("Ajoutez d'abord un tableau avant d'importer.", true);
                            return;
                        }

                        const count = fillSection(targetSection, mappedRows, selectors);
                        setStatus(`${count} ligne(s) importée(s) — vérifiez et corrigez les champs si besoin avant d'enregistrer.`, false);
                    })
                    .catch((err) => {
                        setStatus(err.message || 'Erreur lors de la lecture du fichier.', true);
                    })
                    .finally(() => {
                        input.value = '';
                    });
            });
        },
    };
})();
