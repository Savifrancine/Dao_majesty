(function () {
    const PDFJS_VERSION = '4.10.38';
    const TESSERACT_VERSION = '6.0.1';
    const TESSERACT_CORE_VERSION = '6.0.0';
    const MAX_PDF_PAGES = 10;
    const MAX_IMAGE_DIMENSION = 2200;
    let pdfjsPromise;
    let tesseractPromise;
    let ocrWorkerPromise;

    function normalize(str) {
        return String(str || '')
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    }

    // En-tetes qui designent une colonne de numerotation de ligne (N°, Numero,
    // No...) ou une colonne sans intitule : jamais une vraie donnee, on les
    // exclut d'emblee pour qu'elles ne soient jamais happees par erreur par un
    // autre champ (ex: designation) a cause d'une correspondance partielle.
    const NUMERO_HEADER_PATTERNS = new Set([
        '', '#', 'numero', 'num', 'no', 'n', 'rang', 'ordre', 'index',
        'numero de ligne', 'numero ligne', 'n de ligne', 'no de ligne',
        'n ligne', 'row number', 'row no', 'item number', 'item no',
        'numero d ordre', 'numero ordre', 'n ordre', 'no ordre',
        'numero article', 'n article', 'no article', 'article numero',
    ]);

    function isNumberHeader(header) {
        return NUMERO_HEADER_PATTERNS.has(header)
            || /^(?:n|no|num|numero) (?:de )?(?:ligne|ordre|article)$/.test(header);
    }

    function resolveColumnMapping(headers, fieldSynonyms) {
        const normalizedHeaders = headers.map(normalize);
        const usedColumns = new Set();
        const mapping = {};

        normalizedHeaders.forEach((header, idx) => {
            if (isNumberHeader(header)) {
                usedColumns.add(idx);
            }
        });

        Object.keys(fieldSynonyms).forEach((fieldKey) => {
            const synonyms = fieldSynonyms[fieldKey].map(normalize).filter(Boolean);
            let bestCol = -1;

            if (fieldKey.includes('prix')) {
                bestCol = normalizedHeaders.findIndex((header, idx) =>
                    !usedColumns.has(idx) && /\b(en )?chiffres\b/.test(header)
                );
            }

            // 1) correspondance exacte en priorite.
            synonyms.forEach((syn) => {
                if (bestCol !== -1) return;
                normalizedHeaders.forEach((header, idx) => {
                    if (bestCol !== -1 || usedColumns.has(idx)) return;
                    if (header === syn) bestCol = idx;
                });
            });

            // 2) A defaut, accepte un synonyme comme phrase complete dans un
            // en-tete plus descriptif, sans faire correspondre un simple bout
            // de mot provenant d'une colonne sans rapport.
            if (bestCol === -1) {
                synonyms.forEach((syn) => {
                    if (bestCol !== -1) return;
                    normalizedHeaders.forEach((header, idx) => {
                        if (bestCol !== -1 || usedColumns.has(idx)) return;
                        if (` ${header} `.includes(` ${syn} `)) {
                            bestCol = idx;
                        }
                    });
                });
            }

            if (bestCol !== -1) {
                mapping[bestCol] = fieldKey;
                usedColumns.add(bestCol);
            }
        });

        return mapping;
    }

    function mapRows(headers, rows, fieldSynonyms, columnWidths = []) {
        const mapping = resolveColumnMapping(headers, fieldSynonyms);

        if (columnWidths.length === headers.length) {
            const mappedColumns = new Set(Object.keys(mapping).map(Number));
            const numberColumns = new Set(headers
                .map((header, index) => isNumberHeader(normalize(header)) ? index : -1)
                .filter((index) => index !== -1));
            const columnValues = headers.map((_, column) => rows
                .map((row) => String(row[column] ?? '').trim())
                .filter(Boolean));
            const numericColumns = new Set(columnValues
                .map((values, index) => {
                    if (values.length === 0) return -1;
                    const numericCount = values.filter((value) =>
                        /^[+-]?(?:\d[\d\s.,]*\d|\d)(?:\s*(?:fcfa|f cfa|%))?$/i.test(value)
                    ).length;
                    return numericCount / values.length >= 0.6 ? index : -1;
                })
                .filter((index) => index !== -1));
            const priceKey = Object.keys(fieldSynonyms).find((key) => key.includes('prix'));
            const explicitDigitPriceColumn = headers.findIndex((header) =>
                /\b(en )?chiffres\b/.test(normalize(header))
            );
            if (priceKey && explicitDigitPriceColumn !== -1) {
                Object.keys(mapping).forEach((column) => {
                    if (mapping[column] === priceKey) delete mapping[column];
                });
                mapping[explicitDigitPriceColumn] = priceKey;
            }

            const designationKey = Object.keys(fieldSynonyms).find((key) =>
                /designation|description/.test(normalize(key))
            );
            if (designationKey && !Object.values(mapping).includes(designationKey)) {
                const candidates = headers.map((_, index) => index).filter((index) =>
                    !mappedColumns.has(index)
                    && !numberColumns.has(index)
                    && !numericColumns.has(index)
                    && columnValues[index].length > 0
                );
                const designationColumn = candidates.sort((a, b) =>
                    columnWidths[b] - columnWidths[a]
                )[0];
                if (designationColumn !== undefined) mapping[designationColumn] = designationKey;
            }

            if (priceKey && !Object.values(mapping).includes(priceKey)) {
                const priceColumn = Array.from(numericColumns)
                    .filter((index) => !numberColumns.has(index) && !mappedColumns.has(index))
                    .sort((a, b) => b - a)[0];
                if (priceColumn !== undefined) mapping[priceColumn] = priceKey;
            }
        }

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

        const claimedRows = new Set();
        let rows = Array.from(tbody.querySelectorAll('tr'));

        mappedRows.forEach((data) => {
            const designation = normalize(data.designation);
            let targetRow = designation
                ? rows.find((row) => {
                    if (claimedRows.has(row)) return false;
                    const field = row.querySelector('[name*="[designation]"]');
                    return field && normalize(field.value) === designation;
                })
                : null;

            if (!targetRow) {
                targetRow = rows.find((row) => !claimedRows.has(row) && isRowEmpty(row));
            }

            if (!targetRow) {
                addLineButton.click();
                rows = Array.from(tbody.querySelectorAll('tr'));
                targetRow = rows[rows.length - 1];
            }

            if (!targetRow) return;
            claimedRows.add(targetRow);
            targetRow.dataset.importedFromFile = 'true';

            Object.keys(data).forEach((key) => {
                const field = targetRow.querySelector(`[name*="[${key}]"]`);
                if (!field || String(field.value || '').trim() !== '') return;
                field.value = data[key];
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        return mappedRows.length;
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

    function loadPdfJs() {
        if (!pdfjsPromise) {
            pdfjsPromise = import(`https://cdn.jsdelivr.net/npm/pdfjs-dist@${PDFJS_VERSION}/build/pdf.min.mjs`)
                .then((pdfjs) => {
                    pdfjs.GlobalWorkerOptions.workerSrc =
                        `https://cdn.jsdelivr.net/npm/pdfjs-dist@${PDFJS_VERSION}/build/pdf.worker.min.mjs`;
                    return pdfjs;
                });
        }
        return pdfjsPromise;
    }

    function loadTesseract() {
        if (!tesseractPromise) {
            tesseractPromise = import(`https://cdn.jsdelivr.net/npm/tesseract.js@${TESSERACT_VERSION}/+esm`);
        }
        return tesseractPromise;
    }

    function getOcrWorker(onProgress) {
        if (!ocrWorkerPromise) {
            ocrWorkerPromise = loadTesseract().then(({ createWorker }) => createWorker('fra+eng', 1, {
                workerPath: `https://cdn.jsdelivr.net/npm/tesseract.js@${TESSERACT_VERSION}/dist/worker.min.js`,
                corePath: `https://cdn.jsdelivr.net/npm/tesseract.js-core@${TESSERACT_CORE_VERSION}`,
                langPath: 'https://tessdata.projectnaptha.com/4.0.0',
                logger: (progress) => {
                    if (onProgress && progress.status) {
                        const percent = Number.isFinite(progress.progress)
                            ? ` (${Math.round(progress.progress * 100)} %)`
                            : '';
                        onProgress(`${progress.status}${percent}…`);
                    }
                },
            })).catch((error) => {
                ocrWorkerPromise = null;
                throw error;
            });
        }
        return ocrWorkerPromise;
    }

    function canvasFromImage(file) {
        return createImageBitmap(file).then((image) => {
            const scale = Math.min(1, MAX_IMAGE_DIMENSION / Math.max(image.width, image.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.width * scale));
            canvas.height = Math.max(1, Math.round(image.height * scale));
            canvas.getContext('2d', { alpha: false }).drawImage(image, 0, 0, canvas.width, canvas.height);
            image.close();
            return canvas;
        });
    }

    async function canvasesFromPdf(file, onProgress) {
        const pdfjs = await loadPdfJs();
        const pdf = await pdfjs.getDocument({ data: await file.arrayBuffer() }).promise;
        if (pdf.numPages > MAX_PDF_PAGES) {
            throw new Error(`Ce PDF contient ${pdf.numPages} pages. Importez au maximum ${MAX_PDF_PAGES} pages à la fois.`);
        }

        const canvases = [];
        for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
            if (onProgress) onProgress(`Préparation de la page ${pageNumber}/${pdf.numPages}…`);
            const page = await pdf.getPage(pageNumber);
            const initialViewport = page.getViewport({ scale: 1 });
            const scale = Math.min(2, MAX_IMAGE_DIMENSION / Math.max(initialViewport.width, initialViewport.height));
            const viewport = page.getViewport({ scale });
            const canvas = document.createElement('canvas');
            canvas.width = Math.ceil(viewport.width);
            canvas.height = Math.ceil(viewport.height);
            await page.render({ canvasContext: canvas.getContext('2d', { alpha: false }), viewport }).promise;
            canvases.push(canvas);
        }

        return canvases;
    }

    function parseTsvWords(tsv) {
        const records = String(tsv || '').split(/\r?\n/);
        if (!records[0]) return [];
        const words = [];
        const firstFields = records[0].split('\t');
        const hasHeader = firstFields[0] === 'level';
        const columnIndex = hasHeader
            ? Object.fromEntries(firstFields.map((name, index) => [name, index]))
            : { level: 0, left: 6, top: 7, width: 8, height: 9, text: 11 };
        if (hasHeader) records.shift();

        records.forEach((record) => {
            const fields = record.split('\t');
            if (fields[columnIndex.level] !== '5' || !fields[columnIndex.text]) return;

            const left = Number(fields[columnIndex.left]);
            const top = Number(fields[columnIndex.top]);
            const width = Number(fields[columnIndex.width]);
            const height = Number(fields[columnIndex.height]);
            if (![left, top, width, height].every(Number.isFinite) || width <= 0 || height <= 0) return;

            const text = fields.slice(columnIndex.text).join('\t').trim();
            if (!text || /^[|¦]+$/.test(text)) return;
            words.push({ left, right: left + width, top, bottom: top + height, centerX: left + width / 2, centerY: top + height / 2, height, text });
        });

        return words;
    }

    function findLineCenters(counts, threshold) {
        const centers = [];
        let start = -1;
        let sum = 0;
        let count = 0;

        counts.forEach((value, position) => {
            if (value >= threshold) {
                if (start === -1) start = position;
                sum += position;
                count++;
                return;
            }
            if (start !== -1) {
                centers.push(Math.round(sum / count));
                start = -1;
                sum = 0;
                count = 0;
            }
        });
        if (start !== -1) centers.push(Math.round(sum / count));

        return centers.reduce((merged, center) => {
            const previous = merged[merged.length - 1];
            if (previous !== undefined && center - previous <= 3) {
                merged[merged.length - 1] = Math.round((previous + center) / 2);
            } else {
                merged.push(center);
            }
            return merged;
        }, []);
    }

    function detectTableGrid(canvas, words) {
        if (!canvas || !canvas.width || !canvas.height || words.length < 6) return null;

        const minWordX = Math.min(...words.map((word) => word.left));
        const maxWordX = Math.max(...words.map((word) => word.right));
        const minWordY = Math.min(...words.map((word) => word.top));
        const maxWordY = Math.max(...words.map((word) => word.bottom));
        const xPadding = Math.max(8, canvas.width * 0.025);
        const yPadding = Math.max(12, canvas.height * 0.05);
        const scanLeft = Math.max(0, Math.floor(minWordX - xPadding));
        const scanRight = Math.min(canvas.width, Math.ceil(maxWordX + xPadding));
        const scanTop = Math.max(0, Math.floor(minWordY - yPadding));
        const scanBottom = Math.min(canvas.height, Math.ceil(maxWordY + yPadding));
        const scanWidth = scanRight - scanLeft;
        const scanHeight = scanBottom - scanTop;
        if (scanWidth < 100 || scanHeight < 60) return null;

        const pixels = canvas.getContext('2d', { willReadFrequently: true })
            .getImageData(scanLeft, scanTop, scanWidth, scanHeight).data;
        const xCounts = new Uint16Array(scanWidth);
        const yCounts = new Uint16Array(scanHeight);

        for (let y = 0; y < scanHeight; y++) {
            for (let x = 0; x < scanWidth; x++) {
                const offset = (y * scanWidth + x) * 4;
                if (pixels[offset] < 110 && pixels[offset + 1] < 110 && pixels[offset + 2] < 110) {
                    xCounts[x]++;
                    yCounts[y]++;
                }
            }
        }

        const vertical = findLineCenters(xCounts, scanHeight * 0.48).map((x) => x + scanLeft);
        const horizontal = findLineCenters(yCounts, scanWidth * 0.28).map((y) => y + scanTop);
        if (vertical.length < 3 || horizontal.length < 3) return null;
        return { vertical, horizontal };
    }

    function joinWordsInReadingOrder(words) {
        const sorted = words.slice().sort((a, b) => a.centerY - b.centerY || a.left - b.left);
        const lines = [];

        sorted.forEach((word) => {
            const tolerance = Math.max(5, word.height * 0.75);
            let line = lines.find((candidate) => Math.abs(candidate.centerY - word.centerY) <= tolerance);
            if (!line) {
                line = { centerY: word.centerY, words: [] };
                lines.push(line);
            }
            line.words.push(word);
            line.centerY = line.words.reduce((sum, item) => sum + item.centerY, 0) / line.words.length;
        });

        return lines
            .sort((a, b) => a.centerY - b.centerY)
            .map((line) => line.words.sort((a, b) => a.left - b.left).map((word) => word.text).join(' '))
            .join(' ')
            .trim();
    }

    function rowsFromGrid(words, canvas) {
        const grid = detectTableGrid(canvas, words);
        if (!grid) return null;

        const columnCount = grid.vertical.length - 1;
        const bands = [];
        for (let rowIndex = 0; rowIndex < grid.horizontal.length - 1; rowIndex++) {
            const top = grid.horizontal[rowIndex];
            const bottom = grid.horizontal[rowIndex + 1];
            const cells = Array.from({ length: columnCount }, () => []);
            words.forEach((word) => {
                if (word.centerY < top || word.centerY >= bottom) return;
                const column = grid.vertical.findIndex((x, index) =>
                    index < grid.vertical.length - 1 && word.centerX >= x && word.centerX < grid.vertical[index + 1]
                );
                if (column !== -1) cells[column].push(word);
            });

            const row = cells.map(joinWordsInReadingOrder);
            if (row.some(Boolean)) bands.push(row);
        }

        const dataStart = bands.findIndex((row) =>
            /^\d{1,4}[.)]?$/.test(row[0] || '') && row.slice(1).some(Boolean)
        );
        if (dataStart <= 0) return null;

        const headers = Array.from({ length: columnCount }, (_, column) =>
            bands.slice(0, dataStart).map((row) => row[column]).filter(Boolean).join(' ').trim()
        );
        const dataRows = bands.slice(dataStart).filter((row) => row.some(Boolean));
        return dataRows.length
            ? {
                rows: [headers, ...dataRows],
                columnWidths: grid.vertical.slice(1).map((right, index) => right - grid.vertical[index]),
            }
            : null;
    }

    function rowsFromTsv(tsv, canvas) {
        const words = parseTsvWords(tsv);
        const gridRows = rowsFromGrid(words, canvas);
        if (gridRows) return gridRows;

        const lines = new Map();
        words.forEach((word) => {
            const { left, right, centerY, height, text } = word;
            const tolerance = Math.max(8, height * 0.55);
            let line = Array.from(lines.values()).find((candidate) => Math.abs(candidate.centerY - centerY) <= tolerance);
            if (!line) {
                line = { centerY, words: [] };
                lines.set(centerY, line);
            }
            line.words.push({ left, right, height, text });
            line.centerY = line.words.reduce((sum, word) => sum + word.height, 0) > 0
                ? (line.centerY * (line.words.length - 1) + centerY) / line.words.length
                : centerY;
        });

        const rows = Array.from(lines.values())
            .sort((a, b) => a.centerY - b.centerY)
            .map((line) => {
                const words = line.words.sort((a, b) => a.left - b.left);
                const medianHeight = words.map((word) => word.height).sort((a, b) => a - b)[Math.floor(words.length / 2)] || 12;
                const cells = [];
                let cell = '';
                let previousRight = null;

                words.forEach((word) => {
                    if (previousRight !== null && word.left - previousRight > medianHeight * 1.35) {
                        cells.push(cell.trim());
                        cell = '';
                    }
                    cell = cell ? `${cell} ${word.text}` : word.text;
                    previousRight = word.right;
                });
                if (cell) cells.push(cell.trim());
                return cells;
            })
            .filter((row) => row.some((cell) => cell !== ''));
        return { rows, columnWidths: [] };
    }

    async function recognizeTableFile(file, onProgress, fieldSynonyms) {
        const extension = file.name.split('.').pop()?.toLowerCase();
        let canvases;
        if (extension === 'pdf' || file.type === 'application/pdf') {
            canvases = await canvasesFromPdf(file, onProgress);
        } else if (file.type.startsWith('image/') || ['png', 'jpg', 'jpe', 'jpeg', 'webp', 'bmp'].includes(extension)) {
            canvases = [await canvasFromImage(file)];
        } else {
            return null;
        }

        const worker = await getOcrWorker(onProgress);
        const rows = [];
        let columnWidths = [];
        for (let index = 0; index < canvases.length; index++) {
            if (onProgress) onProgress(`Reconnaissance de la page ${index + 1}/${canvases.length}…`);
            const { data } = await worker.recognize(canvases[index], {}, { tsv: true });
            const parsed = rowsFromTsv(data.tsv, canvases[index]);
            rows.push(...parsed.rows);
            if (!columnWidths.length) columnWidths = parsed.columnWidths;
            canvases[index].width = 0;
            canvases[index].height = 0;
        }

        const nonEmptyRows = rows.filter((row) => row.some((cell) => cell.trim() !== ''));
        if (nonEmptyRows.length < 2) {
            throw new Error("Aucun tableau lisible détecté. Essayez une image plus nette, bien cadrée et prise de face.");
        }
        const headers = nonEmptyRows.shift().map((cell) => cell.trim());
        const recognizedRows = nonEmptyRows.map((row) => row.map((cell) => cell.trim()));
        if (fieldSynonyms) {
            if (!Object.keys(resolveColumnMapping(headers, fieldSynonyms)).length && !columnWidths.length) {
                throw new Error("Les en-têtes du tableau n'ont pas pu être associés aux colonnes du formulaire.");
            }
        }
        return { headers, rows: recognizedRows, columnWidths };
    }

    function readFile(file, importUrl, onProgress, fieldSynonyms) {
        return recognizeTableFile(file, onProgress, fieldSynonyms).then((result) => (
            result || uploadFile(file, importUrl)
        ));
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

    // Applique a une ligne les champs encore vides connus pour une designation
    // donnee (venant d'un autre tableau du dossier deja rempli, ou d'un import
    // precedent) : utilise par la saisie manuelle (sur blur) comme par l'import
    // CSV/Excel juste apres avoir rempli les colonnes presentes dans le fichier.
    function applyKnownFieldsToRow(row, designationValue, index) {
        const match = index[normalize(designationValue)];
        if (!match || !row) return;

        Object.keys(match).forEach((field) => {
            const input = row.querySelector(`[name*="[${field}]"]`);
            if (input && !input.value) {
                input.value = match[field];
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });
    }

    function setupCrossTableFill(lines) {
        const index = buildPrefillIndex(lines);
        window.DaoTableImport._crossTableIndex = index;
        if (Object.keys(index).length === 0) return;

        document.addEventListener('blur', (event) => {
            const target = event.target;
            if (!target.matches || !target.matches('[name*="[designation]"]')) return;

            const row = target.closest('tr');
            if (row?.dataset.importedFromFile === 'true') return;
            applyKnownFieldsToRow(row, target.value, index);
        }, true);
    }

    window.DaoTableImport = {
        setupCrossTableFill,
        _crossTableIndex: null,
        // Un bouton d'import par tableau (plutôt qu'un seul bouton global ne
        // ciblant jamais que le premier) : un MutationObserver injecte
        // automatiquement un bouton dans chaque nouveau tableau ajouté via
        // "+ Ajouter un tableau", sans rien changer au JS propre à chaque
        // formulaire qui construit ces tableaux.
        setup(options) {
            const { buttonId, statusId, sectionsContainerId, fieldSynonyms, importUrl } = options;
            const selectors = {
                section: options.sectionSelector || '.bordereau-section',
                lines: options.linesSelector || '.bordereau-lines',
                addLineButton: options.addLineButtonSelector || '.addBordereauLine',
            };
            const sectionsContainer = document.getElementById(sectionsContainerId);
            if (!sectionsContainer) return;

            // L'ancien bouton global unique n'a plus de raison d'être : masqué
            // plutôt que supprimé du DOM, pour ne rien casser côté Blade.
            if (buttonId) {
                const legacyButton = document.getElementById(buttonId);
                if (legacyButton) legacyButton.style.display = 'none';
            }

            const statusEl = statusId ? document.getElementById(statusId) : null;
            const setStatus = (message, isError, targetStatus = statusEl) => {
                if (!targetStatus) return;
                targetStatus.textContent = message;
                targetStatus.style.color = isError ? '#dc2626' : '#059669';
            };

            function handleFile(file, targetSection, targetStatus) {
                setStatus('Lecture du fichier…', false, targetStatus);

                readFile(file, importUrl, (message) => setStatus(message, false, targetStatus), fieldSynonyms)
                    .then(({ headers, rows, columnWidths }) => {
                        if (!headers || !headers.length || !rows || !rows.length) {
                            setStatus('Aucune donnée trouvée dans ce fichier.', true, targetStatus);
                            return;
                        }

                        const mappedRows = mapRows(headers, rows, fieldSynonyms, columnWidths || []);
                        if (!mappedRows.length) {
                            setStatus("Colonnes non reconnues : vérifiez les intitulés (désignation, quantité, unité...).", true, targetStatus);
                            return;
                        }

                        const count = fillSection(targetSection, mappedRows, selectors);

                        setStatus(`${count} ligne(s) importée(s) — vérifiez et corrigez les champs si besoin avant d'enregistrer.`, false, targetStatus);
                    })
                    .catch((err) => {
                        setStatus(err.message || 'Erreur lors de la lecture du fichier.', true, targetStatus);
                    });
            }

            function injectButton(sectionEl) {
                if (!sectionEl.querySelector || sectionEl.querySelector(':scope > .import-tableau-wrapper')) {
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'import-tableau-wrapper';
                wrapper.style.marginBottom = '10px';

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary btn-sm import-tableau-btn';
                btn.textContent = 'Importer depuis un fichier (Excel/Word/PDF/image)';

                const input = document.createElement('input');
                input.type = 'file';
                input.accept = '.csv,.xlsx,.txt,.docx,.pdf,.png,.jpg,.jpe,.jpeg,.webp,.bmp,image/*,application/pdf';
                input.style.display = 'none';

                const sectionStatus = document.createElement('span');
                sectionStatus.className = 'import-tableau-status';
                sectionStatus.style.cssText = 'display:block;font-size:0.8rem;margin-top:5px;';
                btn.addEventListener('click', () => input.click());
                input.addEventListener('change', () => {
                    const file = input.files[0];
                    if (!file) return;
                    handleFile(file, sectionEl, sectionStatus);
                    input.value = '';
                });

                wrapper.appendChild(btn);
                wrapper.appendChild(input);
                wrapper.appendChild(sectionStatus);
                sectionEl.insertBefore(wrapper, sectionEl.firstChild);
            }

            function injectAll() {
                sectionsContainer.querySelectorAll(selectors.section).forEach(injectButton);
            }

            injectAll();

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType !== 1) return;
                        if (node.matches && node.matches(selectors.section)) {
                            injectButton(node);
                        }
                        if (node.querySelectorAll) {
                            node.querySelectorAll(selectors.section).forEach(injectButton);
                        }
                    });
                });
            });
            observer.observe(sectionsContainer, { childList: true });
        },
        readFile,
    };
})();
