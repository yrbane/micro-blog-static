<?php
/**
 * Entité Tag - Gestion des tags d'articles.
 */
declare(strict_types=1);

namespace Blog\Entity;

/**
 * Représente un tag pour les articles.
 */
class Tag
{
    public function __construct(
        public ?int $id = null,
        public string $slug = '',
        public string $name = '',
        public ?string $description = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    /**
     * Crée un Tag depuis un tableau (résultat DB).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            slug: (string) ($data['slug'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            description: $data['description'] ?? null,
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
        return $slug ?: 'tag';
    }
}
