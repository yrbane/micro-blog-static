<?php
/**
 * Entité Category - Gestion des catégories hiérarchiques.
 */
declare(strict_types=1);

namespace Blog\Entity;

/**
 * Représente une catégorie d'articles.
 */
class Category
{
    public function __construct(
        public ?int $id = null,
        public string $slug = '',
        public string $name = '',
        public ?string $description = null,
        public ?int $parentId = null,
        public string $path = '',
        public int $depth = 0,
        public int $sortOrder = 0,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    /**
     * Crée une Category depuis un tableau (résultat DB).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            slug: (string) ($data['slug'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            description: $data['description'] ?? null,
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            path: (string) ($data['path'] ?? ''),
            depth: (int) ($data['depth'] ?? 0),
            sortOrder: (int) ($data['sort_order'] ?? 0),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
        );
    }

    /**
     * Convertit en tableau pour insertion/mise à jour DB.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'path' => $this->path,
            'depth' => $this->depth,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Génère un slug à partir du nom.
     */
    public static function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[àáâãäå]/u', 'a', $slug);
        $slug = preg_replace('/[èéêë]/u', 'e', $slug);
        $slug = preg_replace('/[ìíîï]/u', 'i', $slug);
        $slug = preg_replace('/[òóôõö]/u', 'o', $slug);
        $slug = preg_replace('/[ùúûü]/u', 'u', $slug);
        $slug = preg_replace('/[ç]/u', 'c', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: 'category';
    }

    /**
     * Vérifie si c'est une catégorie racine.
     */
    public function isRoot(): bool
    {
        return $this->parentId === null;
    }

    /**
     * Récupère le nom indenté pour les selects.
     */
    public function getIndentedName(): string
    {
        return str_repeat('— ', $this->depth) . $this->name;
    }
}
