<?php
/**
 * Entité Post - Gestion des articles.
 */
declare(strict_types=1);

namespace Blog\Entity;

/**
 * Représente un article de blog.
 */
class Post
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Brouillon',
        self::STATUS_PUBLISHED => 'Publié',
        self::STATUS_ARCHIVED => 'Archivé',
    ];

    public function __construct(
        public ?int $id = null,
        public string $slug = '',
        public bool $slugLocked = false,
        public string $title = '',
        public string $contentMd = '',
        public ?string $contentHtml = null,
        public ?string $excerpt = null,
        public string $status = self::STATUS_DRAFT,
        public ?int $categoryId = null,
        public ?int $authorId = null,
        public ?string $seoTitle = null,
        public ?string $seoDescription = null,
        public ?string $ogImage = null,
        public bool $isFeatured = false,
        public int $viewCount = 0,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?\DateTimeImmutable $publishedAt = null,
        /** @var int[] */
        public array $tagIds = [],
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    /**
     * Crée un Post depuis un tableau (résultat DB).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            slug: (string) ($data['slug'] ?? ''),
            slugLocked: (bool) ($data['slug_locked'] ?? false),
            title: (string) ($data['title'] ?? ''),
            contentMd: (string) ($data['content_md'] ?? ''),
            contentHtml: $data['content_html'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            status: (string) ($data['status'] ?? self::STATUS_DRAFT),
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            authorId: isset($data['author_id']) ? (int) $data['author_id'] : null,
            seoTitle: $data['seo_title'] ?? null,
            seoDescription: $data['seo_description'] ?? null,
            ogImage: $data['og_image'] ?? null,
            isFeatured: (bool) ($data['is_featured'] ?? false),
            viewCount: (int) ($data['view_count'] ?? 0),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            publishedAt: isset($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null,
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
            'slug_locked' => $this->slugLocked ? 1 : 0,
            'title' => $this->title,
            'content_md' => $this->contentMd,
            'content_html' => $this->contentHtml,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'category_id' => $this->categoryId,
            'author_id' => $this->authorId,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'og_image' => $this->ogImage,
            'is_featured' => $this->isFeatured ? 1 : 0,
            'view_count' => $this->viewCount,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'tag_ids' => $this->tagIds,
        ];
    }

    /**
     * Génère un slug à partir du titre.
     */
    public static function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[àáâãäå]/u', 'a', $slug);
        $slug = preg_replace('/[èéêë]/u', 'e', $slug);
        $slug = preg_replace('/[ìíîï]/u', 'i', $slug);
        $slug = preg_replace('/[òóôõö]/u', 'o', $slug);
        $slug = preg_replace('/[ùúûü]/u', 'u', $slug);
        $slug = preg_replace('/[ç]/u', 'c', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug ?: 'article';
    }

    /**
     * Vérifie si l'article est publié.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Vérifie si l'article est un brouillon.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Récupère le libellé du statut.
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? 'Inconnu';
    }

    /**
     * Récupère la classe CSS du statut.
     */
    public function getStatusClass(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_DRAFT => 'warning',
            self::STATUS_ARCHIVED => 'secondary',
            default => 'secondary',
        };
    }
}
