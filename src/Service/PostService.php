<?php
/**
 * Service de gestion des articles.
 */
declare(strict_types=1);

namespace Blog\Service;

use Blog\Entity\Post;
use PDO;

/**
 * Gère les opérations CRUD sur les articles.
 */
class PostService
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * Récupère tous les articles.
     *
     * @return Post[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT p.*, u.username as author_name, c.name as category_name
             FROM posts p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN categories c ON p.category_id = c.id
             ORDER BY p.created_at DESC'
        );
        $rows = $stmt->fetchAll();

        return array_map(function($row) {
            $post = Post::fromArray($row);
            // Ajouter les infos supplémentaires
            $row['author_name'] = $row['author_name'] ?? null;
            $row['category_name'] = $row['category_name'] ?? null;
            return ['post' => $post, 'extra' => $row];
        }, $rows);
    }

    /**
     * Récupère un article par son ID.
     */
    public function findById(int $id): ?Post
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $post = Post::fromArray($row);
        $post->tagIds = $this->getTagIds($id);

        return $post;
    }

    /**
     * Récupère un article par son slug.
     */
    public function findBySlug(string $slug): ?Post
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $post = Post::fromArray($row);
        $post->tagIds = $this->getTagIds($post->id);

        return $post;
    }

    /**
     * Crée un nouvel article.
     */
    public function create(Post $post): Post
    {
        if (empty($post->slug)) {
            $post->slug = $this->generateUniqueSlug($post->title);
        }

        $now = date('Y-m-d H:i:s');
        $publishedAt = $post->status === Post::STATUS_PUBLISHED ? $now : null;

        $stmt = $this->pdo->prepare(
            'INSERT INTO posts (slug, slug_locked, title, content_md, content_html, excerpt, status,
             category_id, author_id, seo_title, seo_description, og_image, is_featured,
             created_at, updated_at, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $post->slug,
            $post->slugLocked ? 1 : 0,
            $post->title,
            $post->contentMd,
            $post->contentHtml,
            $post->excerpt,
            $post->status,
            $post->categoryId,
            $post->authorId,
            $post->seoTitle,
            $post->seoDescription,
            $post->ogImage,
            $post->isFeatured ? 1 : 0,
            $now,
            $now,
            $publishedAt,
        ]);

        $post->id = (int) $this->pdo->lastInsertId();

        // Associer les tags
        $this->syncTags($post->id, $post->tagIds);

        return $post;
    }

    /**
     * Met à jour un article existant.
     */
    public function update(Post $post): bool
    {
        $existing = $this->findById($post->id);
        $now = date('Y-m-d H:i:s');

        // Mettre à jour published_at si on publie pour la première fois
        $publishedAt = $existing->publishedAt?->format('Y-m-d H:i:s');
        if ($post->status === Post::STATUS_PUBLISHED && $existing->status !== Post::STATUS_PUBLISHED) {
            $publishedAt = $now;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE posts SET slug = ?, slug_locked = ?, title = ?, content_md = ?, content_html = ?,
             excerpt = ?, status = ?, category_id = ?, seo_title = ?, seo_description = ?,
             og_image = ?, is_featured = ?, updated_at = ?, published_at = ?
             WHERE id = ?'
        );

        $result = $stmt->execute([
            $post->slug,
            $post->slugLocked ? 1 : 0,
            $post->title,
            $post->contentMd,
            $post->contentHtml,
            $post->excerpt,
            $post->status,
            $post->categoryId,
            $post->seoTitle,
            $post->seoDescription,
            $post->ogImage,
            $post->isFeatured ? 1 : 0,
            $now,
            $publishedAt,
            $post->id,
        ]);

        // Synchroniser les tags
        $this->syncTags($post->id, $post->tagIds);

        return $result;
    }

    /**
     * Supprime un article.
     */
    public function delete(int $id): bool
    {
        // Supprime les associations avec les tags
        $stmt = $this->pdo->prepare('DELETE FROM post_tags WHERE post_id = ?');
        $stmt->execute([$id]);

        // Supprime l'article
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Compte les articles par statut.
     *
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $stmt = $this->pdo->query(
            'SELECT status, COUNT(*) as count FROM posts GROUP BY status'
        );
        $rows = $stmt->fetchAll();

        $result = [
            Post::STATUS_DRAFT => 0,
            Post::STATUS_PUBLISHED => 0,
            Post::STATUS_ARCHIVED => 0,
        ];

        foreach ($rows as $row) {
            $result[$row['status']] = (int) $row['count'];
        }

        return $result;
    }

    /**
     * Récupère les IDs des tags d'un article.
     *
     * @return int[]
     */
    private function getTagIds(int $postId): array
    {
        $stmt = $this->pdo->prepare('SELECT tag_id FROM post_tags WHERE post_id = ?');
        $stmt->execute([$postId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Synchronise les tags d'un article.
     *
     * @param int[] $tagIds
     */
    private function syncTags(int $postId, array $tagIds): void
    {
        // Supprime les anciennes associations
        $stmt = $this->pdo->prepare('DELETE FROM post_tags WHERE post_id = ?');
        $stmt->execute([$postId]);

        // Ajoute les nouvelles associations
        if (!empty($tagIds)) {
            $stmt = $this->pdo->prepare('INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)');
            foreach ($tagIds as $tagId) {
                $stmt->execute([$postId, $tagId]);
            }
        }
    }

    /**
     * Génère un slug unique.
     */
    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Post::generateSlug($title);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Vérifie si un slug existe déjà.
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM posts WHERE slug = ?';
        $params = [$slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Convertit le Markdown en HTML.
     */
    public function parseMarkdown(string $markdown): string
    {
        // Implémentation simple - on peut utiliser une librairie comme Parsedown
        $html = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');

        // Titres
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

        // Bold et italic
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);

        // Liens
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html);

        // Images
        $html = preg_replace('/!\[(.+?)\]\((.+?)\)/', '<img src="$2" alt="$1">', $html);

        // Listes
        $html = preg_replace('/^\- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.+<\/li>\n?)+/', '<ul>$0</ul>', $html);

        // Paragraphes
        $html = '<p>' . preg_replace('/\n\n+/', '</p><p>', $html) . '</p>';
        $html = preg_replace('/<p><(h[1-3]|ul|li)/', '<$1', $html);
        $html = preg_replace('/<\/(h[1-3]|ul|li)><\/p>/', '</$1>', $html);

        return $html;
    }
}
