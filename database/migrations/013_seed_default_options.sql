-- Migration: 013_seed_default_options
-- Description: Insertion des options par défaut du site
-- Date: 2024-01-12

-- Groupe "general"
INSERT OR IGNORE INTO options (key, value, type, group_name, label, description, sort_order) VALUES
    ('site_name', 'Mon Blog', 'string', 'general', 'Nom du site', 'Le nom de votre blog', 1),
    ('site_description', 'Un blog personnel', 'text', 'general', 'Description', 'Description courte du site', 2),
    ('site_url', 'https://example.com', 'string', 'general', 'URL du site', 'URL de base du site', 3),
    ('site_logo', '', 'image', 'general', 'Logo', 'Logo du site', 4),
    ('site_favicon', '', 'image', 'general', 'Favicon', 'Icône du site (favicon)', 5),
    ('site_language', 'fr', 'string', 'general', 'Langue', 'Langue principale du site', 6);

-- Groupe "seo"
INSERT OR IGNORE INTO options (key, value, type, group_name, label, description, sort_order) VALUES
    ('meta_title_suffix', ' | Mon Blog', 'string', 'seo', 'Suffixe des titres', 'Texte ajouté après chaque titre de page', 1),
    ('meta_description_default', 'Bienvenue sur mon blog personnel', 'text', 'seo', 'Description par défaut', 'Meta description utilisée si aucune n''est définie', 2),
    ('robots_txt', 'User-agent: *\nAllow: /', 'text', 'seo', 'Robots.txt', 'Contenu du fichier robots.txt', 3);

-- Groupe "social"
INSERT OR IGNORE INTO options (key, value, type, group_name, label, description, sort_order) VALUES
    ('twitter_handle', '', 'string', 'social', 'Twitter', 'Votre identifiant Twitter (@username)', 1),
    ('facebook_url', '', 'string', 'social', 'Facebook', 'URL de votre page Facebook', 2),
    ('github_url', '', 'string', 'social', 'GitHub', 'URL de votre profil GitHub', 3),
    ('og_default_image', '', 'image', 'social', 'Image Open Graph', 'Image par défaut pour les partages sociaux', 4);

-- Groupe "appearance"
INSERT OR IGNORE INTO options (key, value, type, group_name, label, description, sort_order) VALUES
    ('posts_per_page', '10', 'integer', 'appearance', 'Articles par page', 'Nombre d''articles affichés par page', 1),
    ('excerpt_length', '200', 'integer', 'appearance', 'Longueur des extraits', 'Nombre de caractères pour les extraits', 2),
    ('date_format', 'd/m/Y', 'string', 'appearance', 'Format de date', 'Format d''affichage des dates', 3),
    ('theme_color', '#4f46e5', 'string', 'appearance', 'Couleur principale', 'Couleur principale du thème', 4);

-- Groupe "contact"
INSERT OR IGNORE INTO options (key, value, type, group_name, label, description, sort_order) VALUES
    ('admin_email', 'admin@example.com', 'string', 'contact', 'Email admin', 'Email de l''administrateur', 1),
    ('contact_email', 'contact@example.com', 'string', 'contact', 'Email contact', 'Email public de contact', 2);
