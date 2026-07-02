<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tableau de résumé des bordereaux de prix</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #000; padding:6px; }
        .right { text-align:right; }
        .center { text-align:center; }
    </style>
    </head>
<body>
    <h3 style="text-align:center;">TABLEAU DE RESUME DES BORDEREAUX DE PRIX</h3>
    <br>
    <table>
        <tbody>
            <tr>
                <td>Fournitures — Prix Total F CFA HTVA (A)</td>
                <td class="right">{{ isset($data['a']) && is_numeric($data['a']) ? number_format(floatval($data['a']), 0, ',', ' ') : ($data['a'] ?? '') }}</td>
            </tr>
            <tr>
                <td>TVA sur Fournitures (B = A * 18%)</td>
                <td class="right">{{ isset($data['b']) && is_numeric($data['b']) ? number_format(floatval($data['b']), 0, ',', ' ') : ($data['b'] ?? '') }}</td>
            </tr>
            <tr>
                <td>Prix total Fournitures TTC (C = A + B)</td>
                <td class="right">{{ isset($data['c']) && is_numeric($data['c']) ? number_format(floatval($data['c']), 0, ',', ' ') : ($data['c'] ?? '') }}</td>
            </tr>

            <tr>
                <td>Services connexes — Prix total F CFA HTVA (D)</td>
                <td class="right">{{ isset($data['d']) && is_numeric($data['d']) ? number_format(floatval($data['d']), 0, ',', ' ') : ($data['d'] ?? '') }}</td>
            </tr>
            <tr>
                <td>TVA sur Services (E = D * 18%)</td>
                <td class="right">{{ isset($data['e']) && is_numeric($data['e']) ? number_format(floatval($data['e']), 0, ',', ' ') : ($data['e'] ?? '') }}</td>
            </tr>
            <tr>
                <td>Prix total Services TTC (F = D + E)</td>
                <td class="right">{{ isset($data['f']) && is_numeric($data['f']) ? number_format(floatval($data['f']), 0, ',', ' ') : ($data['f'] ?? '') }}</td>
            </tr>

            <tr>
                <td>Montant Total HTVA (G = A + D)</td>
                <td class="right">{{ isset($data['g']) && is_numeric($data['g']) ? number_format(floatval($data['g']), 0, ',', ' ') : ($data['g'] ?? '') }}</td>
            </tr>
            <tr>
                <td>TVA Total (H = G * 18%)</td>
                <td class="right">{{ isset($data['h']) && is_numeric($data['h']) ? number_format(floatval($data['h']), 0, ',', ' ') : ($data['h'] ?? '') }}</td>
            </tr>
            <tr>
                <td>Montant Total TTC (I = G + H)</td>
                <td class="right">{{ isset($data['i']) && is_numeric($data['i']) ? number_format(floatval($data['i']), 0, ',', ' ') : ($data['i'] ?? '') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
