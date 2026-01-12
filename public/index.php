<?php
/**
 * Point d'entrée principal - Blog
 *
 * Route vers l'admin ou sert les fichiers statiques générés.
 */
declare(strict_types=1);

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

// Routes admin
if (str_starts_with($path, '/admin')) {
    // Réécrit l'URI sans le préfixe /admin
    $_SERVER['REQUEST_URI'] = substr($uri, 6) ?: '/';
    $_SERVER['SCRIPT_NAME'] = '/index.php';

    require __DIR__ . '/../admin/public/index.php';
    exit;
}

// Fichiers statiques (assets)
if (str_starts_with($path, '/assets/')) {
    return false; // Laisse PHP servir le fichier directement
}

// Site statique généré - cherche le fichier HTML correspondant
$staticFile = __DIR__ . $path;

// Si c'est un dossier, cherche index.html
if (is_dir($staticFile)) {
    $staticFile = rtrim($staticFile, '/') . '/index.html';
}

// Ajoute .html si pas d'extension
if (!pathinfo($staticFile, PATHINFO_EXTENSION)) {
    $staticFile .= '.html';
}

if (file_exists($staticFile) && is_file($staticFile)) {
    header('Content-Type: text/html; charset=UTF-8');
    readfile($staticFile);
    exit;
}

// Page d'accueil par défaut si aucun contenu généré
if ($path === '/') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blog - Installation</title>
        <link rel="stylesheet" href="/assets/css/main.css">
    </head>
    <body>
        <main class="container" style="padding: 4rem 2rem; text-align: center;">
            <h1>Blog installé avec succès</h1>
            <p style="margin: 2rem 0; color: var(--color-text-muted);">
                Le site statique n'a pas encore été généré.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="/admin" class="btn btn-primary">Accéder à l'administration</a>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>Page non trouvée</h1></body></html>';
