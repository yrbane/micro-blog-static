<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[% block title %]Blog[% endblock %]</title>
    [% block styles %]
    <link rel="stylesheet" href="/assets/css/main.css">
    [% endblock %]
    [% block head_scripts %][% endblock %]
</head>
<body class="[% block body_class %][% endblock %]">
    [% block body %]
    [% endblock %]

    [% block scripts %]
    <script type="module" src="/assets/js/main.js"></script>
    [% endblock %]
</body>
</html>
