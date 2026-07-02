<div id="tableau-resume-bordereaux-step6">
    <input type="hidden" name="documents[]" value="{{ $docId }}">
    <div style="max-width:800px; margin:auto;">
        <h5>Tableau de résumé des bordereaux de prix</h5>
        <p>Remplissez les montants HTVA pour les fournitures et les services, les totaux et les taxes seront calculés automatiquement.</p>

        <table class="table" style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr>
                    <th style="width:60%; text-align:left;">Libellé</th>
                    <th style="width:40%; text-align:right;">Montant (F CFA)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Fournitures — Prix Total F CFA HTVA (A)</td>
                    <td><input type="text" name="tableau_resume[a]" class="form-control numeric-input" value="{{ old('tableau_resume.a', $values['a'] ?? '') }}" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td>TVA sur Fournitures (B = A * 18%)</td>
                    <td><input type="text" name="tableau_resume[b]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.b', $values['b'] ?? '') }}"></td>
                </tr>
                <tr>
                    <td>Prix total Fournitures TTC (C = A + B)</td>
                    <td><input type="text" name="tableau_resume[c]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.c', $values['c'] ?? '') }}"></td>
                </tr>

                <tr><td colspan="2" style="height:10px;"></td></tr>

                <tr>
                    <td>Services connexes — Prix total F CFA HTVA (D)</td>
                    <td><input type="text" name="tableau_resume[d]" class="form-control numeric-input" value="{{ old('tableau_resume.d', $values['d'] ?? '') }}" style="text-align:right;"></td>
                </tr>
                <tr>
                    <td>TVA sur Services (E = D * 18%)</td>
                    <td><input type="text" name="tableau_resume[e]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.e', $values['e'] ?? '') }}"></td>
                </tr>
                <tr>
                    <td>Prix total Services TTC (F = D + E)</td>
                    <td><input type="text" name="tableau_resume[f]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.f', $values['f'] ?? '') }}"></td>
                </tr>

                <tr><td colspan="2" style="height:10px;"></td></tr>

                <tr>
                    <td>Montant Total HTVA (G = A + D)</td>
                    <td><input type="text" name="tableau_resume[g]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.g', $values['g'] ?? '') }}"></td>
                </tr>
                <tr>
                    <td>TVA Total (H = G * 18%)</td>
                    <td><input type="text" name="tableau_resume[h]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.h', $values['h'] ?? '') }}"></td>
                </tr>
                <tr>
                    <td>Montant Total TTC (I = G + H)</td>
                    <td><input type="text" name="tableau_resume[i]" class="form-control computed-input" readonly style="text-align:right; background:#f7f7f7;" value="{{ old('tableau_resume.i', $values['i'] ?? '') }}"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        (function(){
            const parseDecimal = v => {
                if (!v) return 0;
                const s = String(v).replace(/\s+/g,'').replace(',', '.');
                return parseFloat(s) || 0;
            };

            function compute() {
                const a = parseDecimal(document.querySelector('input[name="tableau_resume[a]"]').value);
                const d = parseDecimal(document.querySelector('input[name="tableau_resume[d]"]').value);

                const b = a * 0.18;
                const c = a + b;
                const e = d * 0.18;
                const f = d + e;
                const g = a + d;
                const h = g * 0.18;
                const i = g + h;

                const set = (name, val) => {
                    const el = document.querySelector('input[name="tableau_resume['+name+']"]');
                    if (el) el.value = val > 0 ? val.toFixed(2) : '';
                };

                set('b', b);
                set('c', c);
                set('e', e);
                set('f', f);
                set('g', g);
                set('h', h);
                set('i', i);
            }

            Array.from(document.querySelectorAll('.numeric-input')).forEach(i => i.addEventListener('input', compute));
            compute();
        })();
    </script>
</div>
