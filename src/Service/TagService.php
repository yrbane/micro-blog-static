<?php
/**
 * Service de gestion des tags.
 */
declare(strict_types=1);

namespace Blog\Service;

use Blog\Entity\Tag;
use PDO;

/**
 * Gère les opérations CRUD sur les tags.
 */
class TagService
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * Récupère tous les tags triés par nom.
     *
     * @return Tag[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM tags ORDER BY name');
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => Tag::fromArray($row), $rows);
    }

    /**
     * Récupère un tag par son ID.
     */
    public function findById(int $id): ?Tag
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? Tag::fromArray($row) : null;
    }

    /**
     * Récupère un tag par son slug.
     */
    public function findBySlug(string $slug): ?Tag
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tags WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row ? Tag::fromArray($row) : null;
    }

    /**
     * Récupère les tags d'un article.
     *
     * @return Tag[]
     */
    public function findByPostId(int $postId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.* FROM tags t
             INNER JOIN post_tags pt ON t.id = pt.tag_id
             WHERE pt.post_id = ?
             ORDER BY t.name'
        );
        $stmt->execute([$postId]);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => Tag::fromArray($row), $rows);
    }

    /**
     * Récupère les tags d'un article (alias simplifié).
     *
     * @return array<array{id: int, name: string, slug: string}>
     */
    public function findByPost(int $postId): array
    {
        $tags = $this->findByPostId($postId);
        return array_map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug], $tags);
    }

    /**
     * Crée un nouveau tag.
     */
    public function create(Tag $tag): Tag
    {
        if (empty($tag->slug)) {
            $tag->slug = $this->generateUniqueSlug($tag->name);
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO tags (slug, name, description, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?)'
        );

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $tag->slug,
            $tag->name,
            $tag->description,
            $now,
            $now,
        ]);

        $tag->id = (int) $this->pdo->lastInsertId();

        return $tag;
    }

    /**
     * Met à jour un tag existant.
     */
    public function update(Tag $tag): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE tags SET slug = ?, name = ?, description = ?, updated_at = ? WHERE id = ?'
        );

        return $stmt->execute([
            $tag->slug,
            $tag->name,
            $tag->description,
            date('Y-m-d H:i:s'),
            $tag->id,
        ]);
    }

    /**
     * Supprime un tag.
     */
    public function delete(int $id): bool
    {
        // Supprime d'abord les associations avec les articles
        $stmt = $this->pdo->prepare('DELETE FROM post_tags WHERE tag_id = ?');
        $stmt->execute([$id]);

        // Puis supprime le tag
        $stmt = $this->pdo->prepare('DELETE FROM tags WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Compte le nombre d'articles avec ce tag.
     */
    public function countPosts(int $tagId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM post_tags WHERE tag_id = ?'
        );
        $stmt->execute([$tagId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Génère un slug unique.
     */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Tag::generateSlug($name);
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
        $sql = 'SELECT COUNT(*) FROM tags WHERE slug = ?';
        $params = [$slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }
}
