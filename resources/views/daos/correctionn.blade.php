<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
$dompdf = new Dompdf();

// Forcer police Unicode
$dompdf->set_option('defaultFont', 'DejaVu Sans');
$dompdf->set_option('isHtml5ParserEnabled', true);
$dompdf->set_option('isRemoteEnabled', true);

use Dompdf\Options;

function createDompdfUtf8(): Dompdf {
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    return new Dompdf($options);
}


function getPDOConnection() {
    $host = '127.0.0.1';
    $db   = 'majes2675692';
    $user = 'majes2675692';
    $pass = 'ycfqygaf0o';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $user, $pass, $options);
}

if (!function_exists('slugify')) {
  function slugify($s){
    $s = iconv('UTF-8','ASCII//TRANSLIT',$s);
    $s = preg_replace('~[^\pL\d]+~u','-',$s);
    $s = trim($s,'-');
    $s = strtolower($s);
    return preg_replace('~[^-a-z0-9]+~','', $s);
  }
}

function genRefEntrant(PDO $pdo): string {
    $y = date('Y');
    $prefix = "$y/MSE/DG";
    $table = 'courriers_entrants';

    $stmt = $pdo->prepare("
        SELECT CAST(SUBSTRING_INDEX(ref_interne, '/', -1) AS UNSIGNED) AS num
        FROM {$table}
        WHERE ref_interne LIKE :pattern
        ORDER BY num ASC
    ");
    $stmt->execute([':pattern' => "$prefix/%"]);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $n = 1;
    while (in_array($n, $existing)) {
        $n++;
    }

    return sprintf('%s/%04d', $prefix, $n);
}

function genRefSortant(PDO $pdo): string {
    $y = date('Y');
    $prefix = "$y/MSE/DG";
    $table = 'courriers_sortants';

    $stmt = $pdo->prepare("
        SELECT CAST(SUBSTRING_INDEX(ref_interne, '/', -1) AS UNSIGNED) AS num
        FROM {$table}
        WHERE ref_interne LIKE :pattern
        ORDER BY num ASC
    ");
    $stmt->execute([':pattern' => "$prefix/%"]);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $n = 1;
    while (in_array($n, $existing)) {
        $n++;
    }

    return sprintf('%s/%04d', $prefix, $n);
}






function secure_upload(array $file, string $destDir): string {
  if (!is_dir($destDir)) mkdir($destDir, 0775, true);
  if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload échoué');
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']); finfo_close($finfo);
  if (!isset($allowed[$mime])) throw new RuntimeException('Type de fichier non autorisé');
  if ($file['size'] > 8*1024*1024) throw new RuntimeException('Fichier trop volumineux');
  $name = time().'_'.bin2hex(random_bytes(4)).'.'.$allowed[$mime];
  $to = rtrim($destDir,'/').'/'.$name;
  if (!move_uploaded_file($file['tmp_name'], $to)) throw new RuntimeException('Échec du déplacement de fichier');
  if (str_contains($destDir, 'uploads/courriers')) {
    return 'uploads/courriers/'.$name;
  }
  return 'uploads/'.basename($destDir).'/'.$name;
}

function log_action(PDO $pdo, $user_id, $action, $details=''){
  $stmt = $pdo->prepare("INSERT INTO logs(user_id,action,details) VALUES (?,?,?)");
  $stmt->execute([$user_id,$action,$details]);
}

function merge_template(string $html, array $vars): string {
    function clean_html_content($content) {
    // Décoder récursivement les entités HTML jusqu'à ce qu'il n'y en ait plus
    $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    while ($decoded !== $content) {
        $content = $decoded;
        $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return $content;
}
  foreach ($vars as $k=>$v) {
    $html = str_replace('{{'.$k.'}}', htmlspecialchars($v, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'), $html);
  }
  return $html;
}

function html_to_plain($html){
  $text = strip_tags($html);
  $text = html_entity_decode($text, ENT_QUOTES|ENT_HTML5, 'UTF-8');
  return $text;
}

/**
 * Nouvelle version avec Dompdf (Option A)
 */

if (!function_exists('generate_pdf_from_text')) {
function generate_pdf_from_text($objet, $corps_html, $ref, $signataires = [], ?PDO $pdo = null, $inserer_cachet = false) {
    if (!class_exists(Dompdf::class)) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
   $dompdf = createDompdfUtf8();


    // Logo en base64
    $logoPath = __DIR__ . '/../public/logo.jpeg';
    $imgTag = '';
    if (is_file($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        // Garder la hauteur fixe et largeur auto => pas de logo aplati
        $imgTag = '<img src="'.$base64.'" style="height:100px; width:auto; display:block;" alt="Logo">';

    } else {
        $imgTag = '<div style="color:#888; font-size:10px; text-align:center; width:130px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO MANQUANT</div>';
    }

    $date_envoi = date('d/m/Y');
    
    // increase bottom margin so footer and page numbers do not overlap content
   $stylePage = '
<style>
@page { margin: 20mm 15mm 35mm 15mm; }

body {
    font-family: "DejaVu Sans", Arial, sans-serif;
    font-size: 12px;
}
</style>';

    

    // Construire le HTML principal
  $html = '
<div style="
    font-family: Calibri, Segoe UI, Arial, sans-serif;
    color:#1f78d1;
    width:100%;
">

    <table style="width:100%; border-collapse:collapse;">
        <tr>

            <!-- LOGO -->
            <td style="
                width:115px;
                vertical-align:top;
                padding-right:14px;
            ">
                '.$imgTag.'
            </td>

            <!-- TEXTE -->
            <td style="
                vertical-align:top;
                text-align:center;
            ">

                <!-- TITRE -->
                <div style="
                    font-size:19px;
                    font-weight:700;
                    line-height:1.15;
                    margin-bottom:2px;
                ">
                    MAJESTY SERVICES ET EQUIPEMENTS
                </div>

                <!-- DOMAINES -->
                <div style="
                    font-size:11px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire
                </div>

                <!-- SERVICES -->
                <div style="
                    font-size:11px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    Maintenance – Service Apres Vente
                </div>

                <!-- TRAIT FIN -->
                <div style="
                    width:85%;
                    margin:2px auto 4px auto;
                    border-top:1px solid #1f78d1;
                "></div>

                <!-- ADRESSE -->
                <div style="
                    font-size:10.5px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – Republique du Benin
                </div>

                <!-- INFOS -->
                <div style="
                    font-size:10.5px;
                    line-height:1;
                ">
                    IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 -
                    email : contact@majestyse.com / majestyse@gmail.com
                </div>

            </td>
        </tr>
    </table>

</div>






   
    <div style="text-align:left margin:40px 0; padding-left:450px;  font-size:15px;">
        Cotonou, le '.$date_envoi.'<br>
        <strong>Réf :</strong> '.$ref.'<br> <br> <br>
    </div>
    
    <h3 style="text-align:center; text-decoration:underline; font-size:17px; margin:0 0 10px 0;">'.html_entity_decode($objet, ENT_QUOTES | ENT_HTML5, 'UTF-8').'</h3>
    <div style="margin-top:10px; font-size:15px; line-height:1.5; text-align:justify; padding-left:25px; padding-right:15px">
        '.nl2br(html_entity_decode($corps_html, ENT_QUOTES | ENT_HTML5, 'UTF-8')).'
    </div>
    
    <div style="margin-top:10px; text-align:left; padding-left:450px;; font-size:15px;">
        Fait à Cotonou le '.$date_envoi.'<br>
    </div>
    ';

    foreach ($signataires as $s) {
        // si tu as seulement id_personne et poste, on récupère le nom via PDO
        if (isset($s['id_personne']) && $pdo) {
            $stmt = $pdo->prepare("SELECT nom, prenom FROM personnes WHERE id=?");
            $stmt->execute([$s['id_personne']]);
            $personne = $stmt->fetch();
            if ($personne) {
                $nomComplet = html_entity_decode($personne['nom'].' '.$personne['prenom'], ENT_QUOTES, 'UTF-8');
            } else {
                $nomComplet = "Nom inconnu";
            }
        } else {
            $nomComplet = html_entity_decode($s['nom'] ?? "Nom inconnu", ENT_QUOTES, 'UTF-8');
        }
        $poste = html_entity_decode($s['poste'] ?? '', ENT_QUOTES, 'UTF-8');

        // Ajout automatique du cachet
        /*$cachetPath = __DIR__ . '/../public/CACHET.png';
        $cachetTag = '';
        if (is_file($cachetPath)) {
            $data = file_get_contents($cachetPath);
            $base64 = 'data:CACHET/png;base64,' . base64_encode($data);
            $cachetTag = '<img src="'.$base64.'" style="width:120px; height:auto; margin:15px 0;" alt="Cachet"><br>';
        }*/

        
        // Signature — zone à hauteur fixe pour alignement cohérent
        // Signature group: keep poste + nom together on the same page and leave room above footer
        // Signature: keep both poste and nom together, with padding above footer (left aligned)
       $html .= '
<div style="
    page-break-inside:avoid;
    break-inside:avoid;
    text-align:center;
    padding-left:450px;
    margin-top:20px;
    margin-bottom:60px;
    font-size:15px;
">

    <!-- CACHET -->
';

if ($inserer_cachet) {
    $cachetPath = __DIR__ . '/../public/CACHET2.jpg';

    if (is_file($cachetPath)) {
        $type = pathinfo($cachetPath, PATHINFO_EXTENSION);
        $data = base64_encode(file_get_contents($cachetPath));

        $html .= '
        <div style="margin-bottom:0px; margin-left:-20px;">
            <img src="data:image/'.$type.';base64,'.$data.'" style="width:220px;">
        </div>';
    }
}

$html .= '

    <!-- POSTE -->
    <div style="margin-bottom:2px;">
        <strong>' . $poste . '</strong>
    </div>


    <!-- NOM -->
    <div style="margin-top:2px;">
        <strong>' . $nomComplet . '</strong>
    </div>

</div>';

} $html .= '</div>';



    // Pied de page
    $html .= '
    <div style="
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        font-family: Ebrima, Arial, sans-serif;
        font-size: 10px;
        color: #007BFF;
        line-height: 1.5;
        text-align: center;
        page-break-inside: avoid;
        background-color: #fff;
    ">
        
        <table style="margin: 0 auto; border-collapse: collapse;">
           <tr>
                <!-- Colonne gauche -->
                <td style="width:50%; text-align:center; padding-right:80px; vertical-align:middle; border-right:1px solid #007BFF;">
                    06 BP 358 – Tél : +229 01 97 77 25 04<br>
                    C/763 Kowegbo, Cotonou – République du Bénin
                </td>

                <!-- Colonne droite -->
                <td style="width:50%; text-align:center; padding-left:80px; vertical-align:middle;">
                    IFU : 3 2013 0035 5315 – RCCM : 13 B 9860<br>
                    email : contact@majestyse.com / majestyse@gmail.com
                </td>
            </tr>
        </table>
    </div>
    ';
    
    // Forcer une marge inférieure pour ne pas recouvrir le pied de page

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Ajouter la numérotation centrée sur chaque page (un peu au-dessus du bas)
    try {
        $canvas = $dompdf->get_canvas();
        $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
        $size = 9;
        $pageCount = $canvas->get_page_count();
        $sample = "Page {$pageCount} / {$pageCount}";
        $textWidth = $dompdf->getFontMetrics()->get_text_width($sample, $font, $size);
        $x = ($canvas->get_width() - $textWidth) / 2;
        $y = $canvas->get_height() - 10; // légèrement au-dessus du bas
        $canvas->page_text($x, $y, '{PAGE_NUM} / {PAGE_COUNT}', $font, $size, array(0,0,0));
    } catch (Exception $e) {
        // en cas d'erreur, on continue sans numérotation
    }

    $output = $dompdf->output();
    $pdfDir = __DIR__ . '/../uploads/pdfs/';
    if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
    // Remplacer les '/' dans $ref par '_' pour un nom de fichier valide
    $fileName = str_replace('/', '_', $ref) . '.pdf';
    $filePath = $pdfDir . $fileName;

    // Enregistrer le PDF
    file_put_contents($filePath, $output);

    // Retourner le chemin relatif accessible depuis le navigateur
    return 'uploads/pdfs/' . $fileName;
}}

//pour le type sortant standard

if (!function_exists('convertir_texte_tableau')) {
    function convertir_texte_tableau($texte) {
        $texte = trim($texte);
        $lignes = explode("\n", $texte);
        
        if (count($lignes) < 2) return $texte;
        
        // Nettoyer les lignes vides
        $lignes = array_filter(array_map('trim', $lignes), function($l) { return !empty($l); });
        
        if (count($lignes) < 2) return $texte;
        
        // Essayer avec tabulations d'abord
        $colonnes_liste = array();
        $nb_cols_attendu = null;
        $separateur = null;
        
        foreach ($lignes as $ligne) {
            // Essayer tabulation
            if (strpos($ligne, "\t") !== false) {
                $cols = explode("\t", $ligne);
                $cols = array_map('trim', $cols);
                $separateur = "\t";
            }
            // Sinon essayer espaces multiples
            else if (preg_match('/\s{2,}/', $ligne)) {
                $cols = preg_split('/\s{2,}/', $ligne);
                $cols = array_map('trim', $cols);
                $separateur = ' ';
            }
            // Sinon c'est pas un tableau
            else {
                return $texte;
            }
            
            $cols = array_filter($cols, function($c) { return !empty($c); });
            
            if (empty($cols)) continue;
            
            if ($nb_cols_attendu === null) {
                $nb_cols_attendu = count($cols);
            }
            
            $colonnes_liste[] = $cols;
        }
        
        // Vérifier qu'on a au moins 2 colonnes et 2 lignes
        if ($nb_cols_attendu === null || $nb_cols_attendu < 2 || count($colonnes_liste) < 2) {
            return $texte;
        }
        
        // Convertir en HTML tableau avec meilleure structure
        $html = '<table cellpadding="0" cellspacing="0" style="width:100%; margin: 20px 0; border-collapse: collapse; border: 2px solid #0052A3; font-family: Arial, sans-serif; font-size: 13px;">' . "\n";
        
        foreach ($colonnes_liste as $index => $colonnes) {
            $est_entete = ($index === 0);
            $tag = $est_entete ? 'th' : 'td';
            
            $html .= '  <tr style="height: 35px; ' . ($est_entete ? '' : 'border-bottom: 1px solid #ccc;') . '">' . "\n";
            
            foreach ($colonnes as $colonne) {
                if ($est_entete) {
                    $style = 'padding: 10px 12px; border-right: 1px solid #0052A3; background-color: #0052A3; color: white; font-weight: bold; text-align: center; vertical-align: middle;';
                } else {
                    $style = 'padding: 10px 12px; border-right: 1px solid #ccc; text-align: left; vertical-align: middle;';
                }
                $html .= '    <' . $tag . ' style="' . $style . '">' . htmlspecialchars($colonne, ENT_QUOTES, 'UTF-8') . '</' . $tag . '>' . "\n";
            }
            $html .= '  </tr>' . "\n";
        }
        
        $html .= '</table>';
        return $html;
    }
}



//pour le type sortant standard


if (!function_exists('generate_courrier_pdf')) {
    
function generate_courrier_pdf($objet, $corps_html, $ref, $reference_entrant, $concerne, $destinataire_nom, $destinataire_telephone, $destinataire_email, $destinataire_adresse, $signataire, $date_envoi = null, $inserer_cachet = false) {
    if (!class_exists(Dompdf::class)) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    $dompdf = new Dompdf();

    // Logo
    $logoPath = __DIR__ . '/../public/logo.jpeg';
    $imgTag = '';
    if (is_file($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        // Garder la hauteur fixe et largeur auto => pas de logo aplati
        $imgTag = '<img src="'.$base64.'" style="height:100px; width:auto; display:block;" alt="Logo">';

    } else {
        $imgTag = '<div style="color:#888; font-size:10px; text-align:center; width:130px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO MANQUANT</div>';
    }

    $date_envoi = date('d/m/Y');

    $stylePage = '<style>@page{margin:20mm 15mm 20mm 15mm;}</style>';
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $stylePage;

    // Construire le HTML principal
    $html = '
<div style="
    font-family: Calibri, Segoe UI, Arial, sans-serif;
    color:#1f78d1;
    width:100%;
">

    <table style="width:100%; border-collapse:collapse;">
        <tr>

            <!-- LOGO -->
            <td style="
                width:115px;
                vertical-align:top;
                padding-right:14px;
            ">
                '.$imgTag.'
            </td>

            <!-- TEXTE -->
            <td style="
                vertical-align:top;
                text-align:center;
            ">

                <!-- TITRE -->
                <div style="
                    font-size:19px;
                    font-weight:700;
                    line-height:1.15;
                    margin-bottom:2px;
                ">
                    MAJESTY SERVICES ET EQUIPEMENTS
                </div>

                <!-- DOMAINES -->
                <div style="
                    font-size:11px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire
                </div>

                <!-- SERVICES -->
                <div style="
                    font-size:11px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    Maintenance – Service Apres Vente
                </div>

                <!-- TRAIT FIN -->
                <div style="
                    width:85%;
                    margin:2px auto 4px auto;
                    border-top:1px solid #1f78d1;
                "></div>

                <!-- ADRESSE -->
                <div style="
                    font-size:10.5px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – Republique du Benin
                </div>

                <!-- INFOS -->
                <div style="
                    font-size:10.5px;
                    line-height:1;
                ">
                    IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 -
                    email : contact@majestyse.com / majestyse@gmail.com
                </div>

            </td>
        </tr>
    </table>

</div>


   
    <table style="width:100%; margin:10px 0; padding-right:0px; border-collapse:collapse;">
        <tr>
            <td style="width:75%;"></td>
            <td style="width:60%; text-align:left; vertical-align:top; font-size:15px;">
                Cotonou, le '.$date_envoi.'<br><br><br>
            
                A<br><br>
                '.htmlspecialchars($destinataire_nom).'<br>
                '.htmlspecialchars($destinataire_adresse).'<br>
                '.htmlspecialchars($destinataire_telephone).'
            </td>
        </tr>
    </table>

    <p style="font-size:15px; margin:5px 0; padding-left:25px;"><strong>N/Réf :</strong> ' . htmlspecialchars($ref ?? '', ENT_QUOTES, 'UTF-8') . '</p>
    <p style="font-size:15px; margin:5px 0; padding-left:25px;"><strong>V/Réf :</strong> ' . htmlspecialchars($reference_entrant ?? '', ENT_QUOTES, 'UTF-8') . '</p>
    <p style="font-size:15px; margin:5px 0; padding-left:25px;"><strong><span style="border-bottom:2px solid #000;">Objet</span> :</strong> ' . htmlspecialchars($objet ?? '', ENT_QUOTES, 'UTF-8') . '</p>
    <p style="font-size:15px; margin:5px 0; padding-left:25px;"><strong>Concerne :</strong> ' . htmlspecialchars($concerne ?? '', ENT_QUOTES, 'UTF-8') . '</p>


    
    <div style="margin-top:15px; font-size:15px; line-height:1.6; text-align:justify; padding-left:25px; padding-right:15px;">
        '.nl2br(htmlspecialchars(html_entity_decode($corps_html, ENT_QUOTES|ENT_HTML5, 'UTF-8'))).'
    </div>

      <!-- Signature: keep both poste and nom together, with padding above footer -->
    <div style="page-break-inside:avoid; break-inside:avoid; display:block; margin-top:20px; margin-bottom:80px; text-align:left; padding-left:450px; font-size:15px; vertical-align:top;">
        ';

if ($inserer_cachet) {
    $cachetPath = __DIR__ . '/../public/CACHET2.jpg';
    if (is_file($cachetPath)) {
        $type = pathinfo($cachetPath, PATHINFO_EXTENSION);
        $data = base64_encode(file_get_contents($cachetPath));
        $html .= '<div style="margin-bottom:10px; margin-left:-20px;"><img src="data:image/'.$type.';base64,'.$data.'" style="width:220px;"></div>';
    }
}

$html .= '
        <strong>'.html_entity_decode($signataire['poste'], ENT_QUOTES, 'UTF-8').'</strong>
        
        <div style="height:10px;"></div>
        <strong>'.html_entity_decode($signataire['nom'], ENT_QUOTES, 'UTF-8').'</strong>
        
    </div>
    ';

    $html .= '
    <div style="
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        font-family: Ebrima, Arial, sans-serif;
        font-size: 11px;
        color: #007BFF;
        line-height: 1.5;
        text-align: center;
        page-break-inside: avoid;
        background-color: #fff;
    ">
        
        <table style="margin: 0 auto; border-collapse: collapse;">
           <tr>
                <!-- Colonne gauche -->
                <td style="width:50%; text-align:center; padding-right:80px; vertical-align:middle; border-right:1px solid #007BFF;">
                    06 BP 358 – Tél : +229 01 97 77 25 04<br>
                    C/763 Kowegbo, Cotonou – République du Bénin
                </td>

                <!-- Colonne droite -->
                <td style="width:50%; text-align:center; padding-left:80px; vertical-align:middle;">
                    IFU : 3 2013 0035 5315 – RCCM : 13 B 9860<br>
                    email : contact@majestyse.com / majestyse@gmail.com
                </td>
            </tr>
        </table>
    </div>
';


    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

// Ajouter la numérotation centrée sur chaque page (un peu au-dessus du bas)
    try {
        $canvas = $dompdf->get_canvas();
        $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
        $size = 9;
        $pageCount = $canvas->get_page_count();
        $sample = "Page {$pageCount} / {$pageCount}";
        $textWidth = $dompdf->getFontMetrics()->get_text_width($sample, $font, $size);
        $x = ($canvas->get_width() - $textWidth) / 2;
        $y = $canvas->get_height() - 36; // légèrement au-dessus du bas
        $canvas->page_text($x, $y, '{PAGE_NUM} / {PAGE_COUNT}', $font, $size, array(0,0,0));
    } catch (Exception $e) {
        // en cas d'erreur, on continue sans numérotation
    }

    $output = $dompdf->output();
    $pdfDir = __DIR__ . '/../uploads/pdfs/';
    if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
    // Remplacer les '/' dans $ref par '_' pour un nom de fichier valide
    $fileName = str_replace('/', '_', $ref) . '.pdf';
    $filePath = $pdfDir . $fileName;

    // Enregistrer le PDF
    file_put_contents($filePath, $output);

    // Retourner le chemin relatif accessible depuis le navigateur
    return 'uploads/pdfs/' . $fileName;
}}







//ordre de dedouanement
if (!function_exists('generate_ordre_pdf')) {
function generate_ordre_pdf($objet, $corps_html, $ref, $destinataire_nom,$destinataire_email, $destinataire_adresse, $signataire, $reference_manuelle, $expediteur_marchandise, $nature_marchandise, $lieu_dedouanement, $date_envoi = null, $inserer_cachet = false) {
    if (!class_exists(Dompdf::class)) {
        require_once __DIR__ . '/../vendor/autoload.php';
    }
    $dompdf = createDompdfUtf8();

    // Logo
    $logoPath = __DIR__ . '/../public/logo.jpeg';
    $imgTag = '';
    if (is_file($logoPath)) {
        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
        $data = file_get_contents($logoPath);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        // Garder la hauteur fixe et largeur auto => pas de logo aplati
        $imgTag = '<img src="'.$base64.'" style="height:100px; width:auto; display:block;" alt="Logo">';

    } else {
        $imgTag = '<div style="color:#888; font-size:10px; text-align:center; width:130px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center;">LOGO MANQUANT</div>';
    }

    $date_envoi = date('d/m/Y');

    $stylePage = '<style>@page{margin:20mm 15mm 20mm 15mm;}</style>';
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $stylePage;
    
    // Construire le HTML principal
   $html = '
<div style="
    font-family: Calibri, Segoe UI, Arial, sans-serif;
    color:#1f78d1;
    width:100%;
">

    <table style="width:100%; border-collapse:collapse;">
        <tr>

            <!-- LOGO -->
            <td style="
                width:115px;
                vertical-align:top;
                padding-right:14px;
            ">
                '.$imgTag.'
            </td>

            <!-- TEXTE -->
            <td style="
                vertical-align:top;
                text-align:center;
            ">

                <!-- TITRE -->
                <div style="
                    font-size:19px;
                    font-weight:700;
                    line-height:1.15;
                    margin-bottom:2px;
                ">
                    MAJESTY SERVICES ET EQUIPEMENTS
                </div>

                <!-- DOMAINES -->
                <div style="
                    font-size:11px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    Médical et Laboratoires – Equipements Pétroliers – Environnement - Eaux - Agroalimentaire
                </div>

                <!-- SERVICES -->
                <div style="
                    font-size:11px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    Maintenance – Service Apres Vente
                </div>

                <!-- TRAIT FIN -->
                <div style="
                    width:85%;
                    margin:2px auto 4px auto;
                    border-top:1px solid #1f78d1;
                "></div>

                <!-- ADRESSE -->
                <div style="
                    font-size:10.5px;
                    line-height:1;
                    margin-bottom:2px;
                ">
                    06 BP 358 – Tel : +229 01 97 77 25 04 - C/763 Kowegbo, Cotonou – Republique du Benin
                </div>

                <!-- INFOS -->
                <div style="
                    font-size:10.5px;
                    line-height:1;
                ">
                    IFU : 3 2013 0035 5315 – RCCM : 13 B 9860 -
                    email : contact@majestyse.com / majestyse@gmail.com
                </div>

            </td>
        </tr>
    </table>

</div>



   
    <div style="text-align:left margin:40px 0; padding-left:450px;  font-size:15px;">
        Cotonou, le '.$date_envoi.'<br>
        <strong>Réf :</strong> '.$ref.'<br> <br> <br>
    </div>

    
    <h3 style="text-align:center; text-decoration:underline; margin:0 0 10px 0; font-size:17px;">'
    . htmlspecialchars($objet, ENT_QUOTES, "UTF-8") .
    '</h3>

     <p style="font-size: 15px; margin:5px 0; padding-left:25px;">
        <strong>A l&#39;attention de :</strong> '.htmlspecialchars($destinataire_nom, ENT_QUOTES, 'UTF-8').'
    </p>

    <p style="font-size: 15px; margin:5px 0; padding-left:25px;">
        <strong>Adresse :</strong> ' . $destinataire_adresse . '
    </p>

    <p style="font-size: 15px; margin:5px 0; padding-left:25px;">
        <strong>Référence facture commerciale/proforma :</strong> ' . $reference_manuelle . '
    </p>

    <p style="font-size: 15px; margin:5px 0; padding-left:25px;">
        <strong>Expéditeur marchandises :</strong> ' . $expediteur_marchandise . '
    </p>

    <p style="font-size: 15px; margin:5px 0; padding-left:25px;">
        <strong>Nature de marchandises :</strong> ' . $nature_marchandise . '
    </p>

    <p style="font-size: 15px; margin:5px 0; padding-left:25px;">
        <strong>Lieu de dédouanement :</strong> ' . $lieu_dedouanement . '
    </p>

    <div style="margin-top:14px; font-size:15px; line-height:1.6; text-align:justify; padding-left:25px; padding-right:15px;">
    '.nl2br(htmlspecialchars(html_entity_decode($corps_html, ENT_QUOTES|ENT_HTML5, 'UTF-8'))).'
    </div>
    


    
    
    <!-- Signature: keep both poste and nom together, with padding above footer -->
    <div style="page-break-inside:avoid; break-inside:avoid; display:block; margin-top:20px; margin-bottom:80px; text-align:left; padding-left:450px; font-size:15px; vertical-align:top;">
        ';

if ($inserer_cachet) {
    $cachetPath = __DIR__ . '/../public/CACHET2.jpg';
    if (is_file($cachetPath)) {
        $type = pathinfo($cachetPath, PATHINFO_EXTENSION);
        $data = base64_encode(file_get_contents($cachetPath));
        $html .= '<div style="margin-bottom:10px; margin-left:-20px;"><img src="data:image/'.$type.';base64,'.$data.'" style="width:220px;"></div>';
    }
}

$html .= '
        <strong>'.html_entity_decode($signataire['poste'], ENT_QUOTES, 'UTF-8').'</strong>
        
        <div style="height:10px;"></div>
        <strong>'.html_entity_decode($signataire['nom'], ENT_QUOTES, 'UTF-8').'</strong>
        
    </div>
    ';
    
    // Pied de page
    $html .= '
    <div style="
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        font-family: Ebrima, Arial, sans-serif;
        font-size: 11px;
        color: #007BFF;
        line-height: 1.5;
        text-align: center;
        page-break-inside: avoid;
        background-color: #fff;
    ">
        
        <table style="margin: 0 auto; border-collapse: collapse;">
           <tr>
                <!-- Colonne gauche -->
                <td style="width:50%; text-align:center; padding-right:80px; vertical-align:middle; border-right:1px solid #007BFF;">
                    06 BP 358 – Tél : +229 01 97 77 25 04<br>
                    C/763 Kowegbo, Cotonou – République du Bénin
                </td>

                <!-- Colonne droite -->
                <td style="width:50%; text-align:center; padding-left:80px; vertical-align:middle;">
                    IFU : 3 2013 0035 5315 – RCCM : 13 B 9860<br>
                    email : contact@majestyse.com / majestyse@gmail.com
                </td>
            </tr>
        </table>
    </div>
    ';

 
    
    // Forcer une marge inférieure pour ne pas recouvrir le pied de page
    
    $css = '<style>
    @page { margin: 25mm 15mm 30mm 15mm; }
    @page {
      @bottom-center {
        content: counter(page) " / " counter(pages);
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10pt;
        color: #000;
      }
    }
    </style>';
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

 // Ajouter la numérotation centrée sur chaque page (un peu au-dessus du bas)
    try {
        $canvas = $dompdf->get_canvas();
        $font = $dompdf->getFontMetrics()->get_font('Helvetica', 'normal');
        $size = 9;
        $pageCount = $canvas->get_page_count();
        $sample = "Page {$pageCount} / {$pageCount}";
        $textWidth = $dompdf->getFontMetrics()->get_text_width($sample, $font, $size);
        $x = ($canvas->get_width() - $textWidth) / 2;
        $y = $canvas->get_height() - 36; // légèrement au-dessus du bas
        $canvas->page_text($x, $y, 'Page {PAGE_NUM} / {PAGE_COUNT}', $font, $size, array(0,0,0));
    } catch (Exception $e) {
        // en cas d'erreur, on continue sans numérotation
    }

    $output = $dompdf->output();
    $pdfDir = __DIR__ . '/../uploads/pdfs/';
    if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);
    // Remplacer les '/' dans $ref par '_' pour un nom de fichier valide
    $fileName = str_replace('/', '_', $ref) . '.pdf';
    $filePath = $pdfDir . $fileName;

    // Enregistrer le PDF
    file_put_contents($filePath, $output);

    // Retourner le chemin relatif accessible depuis le navigateur
    return 'uploads/pdfs/' . $fileName;
}}

