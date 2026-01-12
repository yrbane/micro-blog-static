<?php

declare(strict_types=1);

/**
 * Seed: Données de test pour le développement.
 *
 * Ce seed crée des catégories, tags et articles de test.
 * Ne pas exécuter en production!
 */

return function (PDO $pdo, array $config): void {
    // Vérifier si des données existent déjà
    $stmt = $pdo->query('SELECT COUNT(*) FROM posts');
    if ($stmt->fetchColumn() > 0) {
        echo "    ℹ️  Données de test ignorées (articles existants)\n";
        return;
    }

    // Catégories
    $categories = [
        ['slug' => 'technologie', 'name' => 'Technologie', 'description' => 'Articles sur la tech et le développement', 'path' => '/technologie', 'depth' => 0],
        ['slug' => 'php', 'name' => 'PHP', 'description' => 'Tutoriels et astuces PHP', 'parent_slug' => 'technologie', 'path' => '/technologie/php', 'depth' => 1],
        ['slug' => 'javascript', 'name' => 'JavaScript', 'description' => 'Tout sur JavaScript', 'parent_slug' => 'technologie', 'path' => '/technologie/javascript', 'depth' => 1],
        ['slug' => 'lifestyle', 'name' => 'Lifestyle', 'description' => 'Articles lifestyle et conseils', 'path' => '/lifestyle', 'depth' => 0],
    ];

    $categoryIds = [];
    $stmt = $pdo->prepare('INSERT INTO categories (slug, name, description, parent_id, path, depth, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');

    $sortOrder = 0;
    foreach ($categories as $cat) {
        $parentId = null;
        if (isset($cat['parent_slug'])) {
            $parentId = $categoryIds[$cat['parent_slug']] ?? null;
        }

        $stmt->execute([
            $cat['slug'],
            $cat['name'],
            $cat['description'],
            $parentId,
            $cat['path'],
            $cat['depth'],
            $sortOrder++,
        ]);

        $categoryIds[$cat['slug']] = (int) $pdo->lastInsertId();
    }
    echo "    ✓ " . count($categories) . " catégories créées\n";

    // Tags
    $tags = [
        ['slug' => 'tutorial', 'name' => 'Tutorial'],
        ['slug' => 'tips', 'name' => 'Tips & Tricks'],
        ['slug' => 'beginner', 'name' => 'Débutant'],
        ['slug' => 'advanced', 'name' => 'Avancé'],
        ['slug' => 'performance', 'name' => 'Performance'],
    ];

    $tagIds = [];
    $stmt = $pdo->prepare('INSERT INTO tags (slug, name) VALUES (?, ?)');

    foreach ($tags as $tag) {
        $stmt->execute([$tag['slug'], $tag['name']]);
        $tagIds[$tag['slug']] = (int) $pdo->lastInsertId();
    }
    echo "    ✓ " . count($tags) . " tags créés\n";

    // Articles
    $posts = [
        [
            'slug' => 'bienvenue-sur-le-blog',
            'title' => 'Bienvenue sur le blog',
            'excerpt' => 'Premier article du blog, découvrez ce que nous allons partager.',
            'content' => "# Bienvenue sur le blog\n\nCeci est le premier article de notre blog. Nous allons partager ici des articles sur:\n\n- La technologie\n- Le développement web\n- Les bonnes pratiques\n\n## À venir\n\nRestez connectés pour plus de contenu!",
            'category' => 'technologie',
            'tags' => ['beginner'],
            'status' => 'published',
        ],
        [
            'slug' => 'introduction-a-php-8',
            'title' => 'Introduction à PHP 8',
            'excerpt' => 'Découvrez les nouvelles fonctionnalités de PHP 8 et comment les utiliser.',
            'content' => "# Introduction à PHP 8\n\nPHP 8 apporte de nombreuses nouvelles fonctionnalités:\n\n## Named Arguments\n\n```php\nfunction greet(string \$name, string \$greeting = 'Hello') {\n    return \"\$greeting, \$name!\";\n}\n\ngreet(name: 'World', greeting: 'Bonjour');\n```\n\n## Match Expression\n\n```php\n\$result = match(\$value) {\n    1 => 'Un',\n    2 => 'Deux',\n    default => 'Autre',\n};\n```\n\n## Constructor Property Promotion\n\n```php\nclass User {\n    public function __construct(\n        public string \$name,\n        public string \$email,\n    ) {}\n}\n```",
            'category' => 'php',
            'tags' => ['tutorial', 'beginner'],
            'status' => 'published',
        ],
        [
            'slug' => 'optimisation-performances-php',
            'title' => 'Optimisation des performances PHP',
            'excerpt' => 'Techniques avancées pour améliorer les performances de vos applications PHP.',
            'content' => "# Optimisation des performances PHP\n\n## Opcache\n\nActivez opcache pour de meilleures performances:\n\n```ini\nopcache.enable=1\nopcache.memory_consumption=256\nopcache.interned_strings_buffer=16\n```\n\n## Preloading\n\nPHP 7.4+ supporte le preloading pour charger les classes au démarrage.\n\n## Profiling\n\nUtilisez Xdebug ou Blackfire pour identifier les goulots d'étranglement.",
            'category' => 'php',
            'tags' => ['advanced', 'performance', 'tips'],
            'status' => 'published',
        ],
        [
            'slug' => 'article-brouillon',
            'title' => 'Article en brouillon',
            'excerpt' => 'Cet article est en cours de rédaction.',
            'content' => "# Brouillon\n\nContenu en cours...",
            'category' => 'lifestyle',
            'tags' => [],
            'status' => 'draft',
        ],
    ];

    $stmtPost = $pdo->prepare(
        'INSERT INTO posts (slug, title, excerpt, content, category_id, status, author_id, published_at, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmtPostTag = $pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)');

    $now = date('Y-m-d H:i:s');
    foreach ($posts as $post) {
        $categoryId = $categoryIds[$post['category']] ?? null;
        $publishedAt = $post['status'] === 'published' ? $now : null;

        $stmtPost->execute([
            $post['slug'],
            $post['title'],
            $post['excerpt'],
            $post['content'],
            $categoryId,
            $post['status'],
            1, // admin user
            $publishedAt,
            $now,
        ]);

        $postId = (int) $pdo->lastInsertId();

        // Associer les tags
        foreach ($post['tags'] as $tagSlug) {
            if (isset($tagIds[$tagSlug])) {
                $stmtPostTag->execute([$postId, $tagIds[$tagSlug]]);
            }
        }
    }
    echo "    ✓ " . count($posts) . " articles créés\n";
};
