<?php
/**
 * Entité Media - Gestion des fichiers médias.
 */
declare(strict_types=1);

namespace Blog\Entity;

/**
 * Représente un fichier média (image, document, etc.).
 */
class Media
{
    public const ALLOWED_IMAGES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
    public const ALLOWED_DOCUMENTS = ['application/pdf'];
    public const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

    public function __construct(
        public ?int $id = null,
        public string $filename = '',
        public string $originalName = '',
        public string $mimeType = '',
        public int $size = 0,
        public string $path = '',
        public ?string $altText = null,
        public ?string $title = null,
        public ?int $uploadedBy = null,
        public ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    /**
     * Crée un Media depuis un tableau (résultat DB).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            filename: (string) ($data['filename'] ?? ''),
            originalName: (string) ($data['original_name'] ?? ''),
            mimeType: (string) ($data['mime_type'] ?? ''),
            size: (int) ($data['size'] ?? 0),
            path: (string) ($data['path'] ?? ''),
            altText: $data['alt_text'] ?? null,
            title: $data['title'] ?? null,
            uploadedBy: isset($data['uploaded_by']) ? (int) $data['uploaded_by'] : null,
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
        );
    }

    /**
     * Convertit en tableau pour insertion/mise à jour DB.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'original_name' => $this->originalName,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
            'path' => $this->path,
            'alt_text' => $this->altText,
            'title' => $this->title,
            'uploaded_by' => $this->uploadedBy,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Vérifie si c'est une image.
     */
    public function isImage(): bool
    {
        return in_array($this->mimeType, self::ALLOWED_IMAGES, true);
    }

    /**
     * Récupère l'URL publique du média.
     */
    public function getUrl(): string
    {
        return '/uploads/' . $this->path;
    }

    /**
     * Récupère la taille formatée.
     */
    public function getFormattedSize(): string
    {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $size = $this->size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Génère un nom de fichier unique.
     */
    public static function generateFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-z0-9-]/i', '-', $baseName);
        $safeName = trim(preg_replace('/-+/', '-', $safeName), '-');
        $uniqueId = substr(uniqid(), -6);

        return strtolower($safeName) . '-' . $uniqueId . '.' . strtolower($extension);
    }

    /**
     * Vérifie si un type MIME est autorisé.
     */
    public static function isAllowedMimeType(string $mimeType): bool
    {
        return in_array($mimeType, array_merge(self::ALLOWED_IMAGES, self::ALLOWED_DOCUMENTS), true);
    }
}
