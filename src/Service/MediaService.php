<?php
/**
 * Service de gestion des médias.
 */
declare(strict_types=1);

namespace Blog\Service;

use Blog\Entity\Media;
use PDO;

/**
 * Gère les opérations sur les médias (upload, suppression, etc.).
 */
class MediaService
{
    private string $uploadDir;

    public function __construct(
        private PDO $pdo,
        string $projectRoot
    ) {
        $this->uploadDir = $projectRoot . '/public/uploads';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Récupère tous les médias.
     *
     * @return Media[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM media ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => Media::fromArray($row), $rows);
    }

    /**
     * Récupère uniquement les images.
     *
     * @return Media[]
     */
    public function findImages(): array
    {
        $placeholders = implode(',', array_fill(0, count(Media::ALLOWED_IMAGES), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT * FROM media WHERE mime_type IN ($placeholders) ORDER BY created_at DESC"
        );
        $stmt->execute(Media::ALLOWED_IMAGES);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => Media::fromArray($row), $rows);
    }

    /**
     * Récupère un média par son ID.
     */
    public function findById(int $id): ?Media
    {
        $stmt = $this->pdo->prepare('SELECT * FROM media WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? Media::fromArray($row) : null;
    }

    /**
     * Upload un fichier.
     *
     * @param array $file Tableau $_FILES
     * @param int|null $userId ID de l'utilisateur qui upload
     * @return Media Le média créé
     * @throws \RuntimeException En cas d'erreur
     */
    public function upload(array $file, ?int $userId = null): Media
    {
        // Vérifications
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Fichier invalide.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException($this->getUploadErrorMessage($file['error']));
        }

        if ($file['size'] > Media::MAX_SIZE) {
            throw new \RuntimeException('Le fichier est trop volumineux (max 10 Mo).');
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if (!Media::isAllowedMimeType($mimeType)) {
            throw new \RuntimeException('Type de fichier non autorisé.');
        }

        // Génère un nom unique
        $filename = Media::generateFilename($file['name']);

        // Crée le sous-dossier par année/mois
        $subDir = date('Y/m');
        $fullDir = $this->uploadDir . '/' . $subDir;
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        // Déplace le fichier
        $path = $subDir . '/' . $filename;
        $fullPath = $this->uploadDir . '/' . $path;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \RuntimeException('Erreur lors du déplacement du fichier.');
        }

        // Crée l'entrée en base
        $media = new Media(
            filename: $filename,
            originalName: $file['name'],
            mimeType: $mimeType,
            size: $file['size'],
            path: $path,
            uploadedBy: $userId,
        );

        $stmt = $this->pdo->prepare(
            'INSERT INTO media (filename, original_name, mime_type, size, path, uploaded_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $media->filename,
            $media->originalName,
            $media->mimeType,
            $media->size,
            $media->path,
            $media->uploadedBy,
            date('Y-m-d H:i:s'),
        ]);

        $media->id = (int) $this->pdo->lastInsertId();

        return $media;
    }

    /**
     * Met à jour les métadonnées d'un média.
     */
    public function update(Media $media): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE media SET alt_text = ?, title = ? WHERE id = ?'
        );

        return $stmt->execute([
            $media->altText,
            $media->title,
            $media->id,
        ]);
    }

    /**
     * Supprime un média.
     */
    public function delete(int $id): bool
    {
        $media = $this->findById($id);
        if ($media === null) {
            return false;
        }

        // Supprime le fichier physique
        $fullPath = $this->uploadDir . '/' . $media->path;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Supprime l'entrée en base
        $stmt = $this->pdo->prepare('DELETE FROM media WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Récupère le message d'erreur pour un code d'erreur upload.
     */
    private function getUploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la limite du serveur.',
            UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la limite du formulaire.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement uploadé.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été uploadé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqué par une extension.',
            default => 'Erreur inconnue lors de l\'upload.',
        };
    }
}
