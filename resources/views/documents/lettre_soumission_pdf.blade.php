<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Lettre de soumission</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#111; margin:28px; line-height:1.5; }
        .center { text-align:center; }
        .title { font-size:16px; font-weight:bold; margin-bottom:8px; text-transform:uppercase; }
        .section { margin-top:12px; }
        .section p { margin:0 0 10px 0; }
        .label { font-weight:bold; }
        .detail-line { margin:4px 0; }
        .small { font-size:11px; color:#444; }
    </style>
</head>
<body>
    @include('documents.partials.majesty_header', ['entreprise' => $entreprise ?? null])

    @include('documents.partials.lettre_soumission_pdf_content')
</body>
</html>
