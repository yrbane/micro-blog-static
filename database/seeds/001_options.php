<?php

declare(strict_types=1);

/**
 * Seed: Options par défaut du site.
 */

return function (PDO $pdo, array $config): void {
    $options = [
        // Groupe: general
        ['site_name', 'Mon Blog', 'string', 'general', 'Nom du site', 'Le nom de votre blog', 1],
        ['site_description', 'Un blog propulsé par micro-blog-static', 'text', 'general', 'Description du site', 'Description courte pour le SEO', 2],
        ['site_url', $config['site']['url'] ?? 'http://localhost', 'string', 'general', 'URL du site', 'URL de base du site (sans slash final)', 3],
        ['site_logo', '', 'image', 'general', 'Logo', 'Logo du site (chemin relatif)', 4],
        ['site_favicon', '', 'image', 'general', 'Favicon', 'Favicon du site', 5],
        ['site_language', 'fr', 'string', 'general', 'Langue', 'Code langue (fr, en, etc.)', 6],

        // Groupe: seo
        ['meta_title_suffix', ' | Mon Blog', 'string', 'seo', 'Suffixe des titres', 'Ajouté à la fin des titres de page', 1],
        ['meta_description_default', 'Bienvenue sur mon blog', 'text', 'seo', 'Description par défaut', 'Utilisée si aucune description spécifique', 2],
        ['robots_txt', "User-agent: *\nAllow: /", 'text', 'seo', 'Contenu robots.txt', 'Règles pour les robots de recherche', 3],

        // Groupe: social
        ['twitter_handle', '', 'string', 'social', 'Twitter', 'Votre @username Twitter', 1],
        ['facebook_url', '', 'string', 'social', 'Facebook', 'URL de votre page Facebook', 2],
        ['github_url', '', 'string', 'social', 'GitHub', 'URL de votre profil GitHub', 3],
        ['og_default_image', '', 'image', 'social', 'Image Open Graph par défaut', 'Image utilisée pour le partage social', 4],

        // Groupe: appearance
        ['posts_per_page', '10', 'integer', 'appearance', 'Articles par page', 'Nombre d\'articles affichés par page', 1],
        ['excerpt_length', '200', 'integer', 'appearance', 'Longueur des extraits', 'Nombre de caractères pour les extraits', 2],
        ['date_format', 'd/m/Y', 'string', 'appearance', 'Format de date', 'Format PHP pour les dates (ex: d/m/Y)', 3],
        ['theme_color', '#4f46e5', 'string', 'appearance', 'Couleur principale', 'Couleur du thème (hex)', 4],

        // Groupe: contact
        ['admin_email', 'admin@example.com', 'string', 'contact', 'Email admin', 'Email de l\'administrateur', 1],
        ['contact_email', 'contact@example.com', 'string', 'contact', 'Email contact', 'Email affiché pour le contact', 2],
    ];

    $stmt = $pdo->prepare('INSERT OR IGNORE INTO options (key, value, type, group_name, label, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');

    foreach ($options as $option) {
        $stmt->execute($option);
    }
};
