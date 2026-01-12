<?php
/**
 * Service de gestion des catégories.
 */
declare(strict_types=1);

namespace Blog\Service;

use Blog\Entity\Category;
use PDO;

/**
 * Gère les opérations CRUD sur les catégories.
 */
class CategoryService
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * Récupère toutes les catégories triées hiérarchiquement.
     *
     * @return Category[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM categories ORDER BY path, sort_order, name'
        );
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => Category::fromArray($row), $rows);
    }

    /**
     * Récupère les catégories pour un select (avec indentation).
     *
     * @return array<int, array{id: int, name: string, depth: int}>
     */
    public function findAllForSelect(): array
    {
        $categories = $this->findAll();
        $result = [];

        foreach ($categories as $cat) {
            $result[] = [
                'id' => $cat->id,
                'name' => $cat->getIndentedName(),
                'depth' => $cat->depth,
            ];
        }

        return $result;
    }

    /**
     * Récupère une catégorie par son ID.
     */
    public function findById(int $id): ?Category
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? Category::fromArray($row) : null;
    }

    /**
     * Récupère une catégorie par son slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row ? Category::fromArray($row) : null;
    }

    /**
     * Crée une nouvelle catégorie.
     */
    public function create(Category $category): Category
    {
        // Génère le slug si vide
        if (empty($category->slug)) {
            $category->slug = $this->generateUniqueSlug($category->name);
        }

        // Calcule le path et la profondeur
        $this->calculateHierarchy($category);

        $stmt = $this->pdo->prepare(
            'INSERT INTO categories (slug, name, description, parent_id, path, depth, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $category->slug,
            $category->name,
            $category->description,
            $category->parentId,
            $category->path,
            $category->depth,
            $category->sortOrder,
            $now,
            $now,
        ]);

        $category->id = (int) $this->pdo->lastInsertId();

        // Met à jour le path avec l'ID
        $this->updatePath($category);

        return $category;
    }

    /**
     * Met à jour une catégorie existante.
     */
    public function update(Category $category): bool
    {
        // Recalcule le path et la profondeur si le parent a changé
        $this->calculateHierarchy($category);

        $stmt = $this->pdo->prepare(
            'UPDATE categories SET slug = ?, name = ?, description = ?, parent_id = ?,
             path = ?, depth = ?, sort_order = ?, updated_at = ? WHERE id = ?'
        );

        $result = $stmt->execute([
            $category->slug,
            $category->name,
            $category->description,
            $category->parentId,
            $category->path,
            $category->depth,
            $category->sortOrder,
            date('Y-m-d H:i:s'),
            $category->id,
        ]);

        // Met à jour les enfants si nécessaire
        if ($result) {
            $this->updateChildrenPaths($category);
        }

        return $result;
    }

    /**
     * Supprime une catégorie.
     */
    public function delete(int $id): bool
    {
        // Les enfants seront orphelins (parent_id = NULL) grâce à ON DELETE SET NULL
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Compte le nombre d'articles dans une catégorie.
     */
    public function countPosts(int $categoryId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM posts WHERE category_id = ?'
        );
        $stmt->execute([$categoryId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Génère un slug unique.
     */
    private function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $baseSlug = Category::generateSlug($name);
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
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = ?';
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
     * Calcule le path et la profondeur d'une catégorie.
     */
    private function calculateHierarchy(Category $category): void
    {
        if ($category->parentId === null) {
            $category->path = '';
            $category->depth = 0;
        } else {
            $parent = $this->findById($category->parentId);
            if ($parent) {
                $category->depth = $parent->depth + 1;
                // Le path sera mis à jour après l'insertion avec l'ID
            }
        }
    }

    /**
     * Met à jour le path d'une catégorie avec son ID.
     */
    private function updatePath(Category $category): void
    {
        if ($category->parentId === null) {
            $category->path = (string) $category->id;
        } else {
            $parent = $this->findById($category->parentId);
            if ($parent) {
                $category->path = $parent->path . '/' . $category->id;
            } else {
                $category->path = (string) $category->id;
            }
        }

        $stmt = $this->pdo->prepare('UPDATE categories SET path = ? WHERE id = ?');
        $stmt->execute([$category->path, $category->id]);
    }

    /**
     * Met à jour les paths des enfants d'une catégorie.
     */
    private function updateChildrenPaths(Category $parent): void
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE parent_id = ?');
        $stmt->execute([$parent->id]);
        $children = $stmt->fetchAll();

        foreach ($children as $childRow) {
            $child = Category::fromArray($childRow);
            $child->path = $parent->path . '/' . $child->id;
            $child->depth = $parent->depth + 1;

            $updateStmt = $this->pdo->prepare(
                'UPDATE categories SET path = ?, depth = ? WHERE id = ?'
            );
            $updateStmt->execute([$child->path, $child->depth, $child->id]);

            // Récursif pour les sous-enfants
            $this->updateChildrenPaths($child);
        }
    }
}
