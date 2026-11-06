<div id="recapitulatif-general-dqe-step6">
    @php
        // Entierement recalcule a partir des bordereaux de prix deja remplis
        // dans ce dossier (voir App\Support\BordereauTotals) - rien a saisir
        // ici, jamais de valeur figee.
        $dqeLignes = \App\Support\BordereauTotals::recapitulatifDqeLignes($dossier);
        $dqeTotalHt = $dqeLignes->sum('ht');
        $dqeTotalTva = $dqeLignes->sum('tva');
        $dqeTotalTtc = $dqeLignes->sum('ttc');
        $dqeTotalAib = $dqeLignes->sum('aib');
        $formatMontant = fn ($v) => number_format($v, 0, ',', ' ');
        $showAib = $showAib ?? true;
    @endphp
    <input type="hidden" name="documents[]" value="{{ $docId }}">
    <div style="max-width:950px; margin:auto;">
        <h5>Récapitulatif général des DQE</h5>
        <p>Reprend automatiquement, document par document, les montants des bordereaux de prix déjà calculés dans ce dossier (fournitures et services connexes) : rien à saisir ici.</p>

        <div class="wizard-field" style="margin-bottom:14px;">
            <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
                <input type="hidden" name="recap_dqe[show_aib]" value="0">
                <input type="checkbox" name="recap_dqe[show_aib]" value="1" @checked($showAib)>
                Inclure la colonne « AIB indiqué » (1% du montant HT)
            </label>
            <small style="color:#64748b;">Aucun document de ce dossier n'indique réellement l'AIB ; cette colonne n'est qu'une estimation (1% du HT). Décochez pour la retirer du formulaire et du PDF.</small>
        </div>

        <table class="table" style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr>
                    <th style="width:6%; text-align:center;">N°</th>
                    <th style="width:{{ $showAib ? '38' : '43' }}%; text-align:left;">Désignation</th>
                    <th style="width:{{ $showAib ? '16' : '18' }}%; text-align:right;">Montant HT (FCFA)</th>
                    <th style="width:{{ $showAib ? '13' : '15' }}%; text-align:right;">TVA (FCFA)</th>
                    <th style="width:{{ $showAib ? '16' : '18' }}%; text-align:right;">Montant TTC (FCFA)</th>
                    @if($showAib)
                        <th style="width:11%; text-align:right;">AIB indiqué (FCFA)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($dqeLignes as $dqeIndex => $dqeLigne)
                    <tr>
                        <td style="text-align:center;">{{ $dqeIndex + 1 }}</td>
                        <td>{{ $dqeLigne['designation'] }}</td>
                        <td style="text-align:right;">{{ $formatMontant($dqeLigne['ht']) }}</td>
                        <td style="text-align:right;">{{ $formatMontant($dqeLigne['tva']) }}</td>
                        <td style="text-align:right;">{{ $formatMontant($dqeLigne['ttc']) }}</td>
                        @if($showAib)
                            <td style="text-align:right;">{{ $formatMontant($dqeLigne['aib']) }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showAib ? 6 : 5 }}" style="text-align:center; color:#64748b; padding:12px;">Aucun document à calcul (bordereau de prix) rempli pour l'instant dans ce dossier.</td>
                    </tr>
                @endforelse
                <tr style="font-weight:700; background:#f3f4f6;">
                    <td colspan="2">TOTAL GÉNÉRAL</td>
                    <td style="text-align:right;">{{ $formatMontant($dqeTotalHt) }}</td>
                    <td style="text-align:right;">{{ $formatMontant($dqeTotalTva) }}</td>
                    <td style="text-align:right;">{{ $formatMontant($dqeTotalTtc) }}</td>
                    @if($showAib)
                        <td style="text-align:right;">{{ $formatMontant($dqeTotalAib) }}</td>
                    @endif
                </tr>
            </tbody>
        </table>
    </div>
</div>
