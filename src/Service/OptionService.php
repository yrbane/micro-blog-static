<?php

declare(strict_types=1);

namespace Blog\Service;

use PDO;

/**
 * Service de gestion des options du site.
 *
 * Permet de stocker et récupérer les paramètres du site
 * (nom, description, URL, logo, etc.) depuis la base de données.
 * Les options sont mises en cache pour éviter les requêtes répétées.
 */
class OptionService
{
    /** @var array<string, mixed> Cache des options */
    private array $cache = [];

    /** @var bool Indique si le cache est chargé */
    private bool $cacheLoaded = false;

    public function __construct(
        private PDO $pdo,
        private string $cachePath
    ) {}

    /**
     * Récupère une option par sa clé.
     *
     * @param string $key Clé de l'option
     * @param mixed $default Valeur par défaut si l'option n'existe pas
     * @return mixed Valeur de l'option
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->loadCache();

        if (!isset($this->cache[$key])) {
            return $default;
        }

        return $this->cache[$key]['value'];
    }

    /**
     * Définit une option.
     *
     * @param string $key Clé de l'option
     * @param mixed $value Valeur de l'option
     */
    public function set(string $key, mixed $value): void
    {
        $this->loadCache();

        $now = date('Y-m-d H:i:s');

        if (isset($this->cache[$key])) {
            // Mise à jour
            $stmt = $this->pdo->prepare(
                'UPDATE options SET value = ?, updated_at = ? WHERE key = ?'
            );
            $stmt->execute([$this->encodeValue($value), $now, $key]);
        } else {
            // Insertion
            $stmt = $this->pdo->prepare(
                'INSERT INTO options (key, value, updated_at) VALUES (?, ?, ?)'
            );
            $stmt->execute([$key, $this->encodeValue($value), $now]);
        }

        // Mettre à jour le cache
        $this->cache[$key] = [
            'value' => $value,
            'updated_at' => $now,
        ];

        $this->saveFileCache();
    }

    /**
     * Récupère toutes les options d'un groupe.
     *
     * @param string $group Nom du groupe
     * @return array<string, mixed> Options du groupe
     */
    public function getByGroup(string $group): array
    {
        $this->loadCache();

        $result = [];
        foreach ($this->cache as $key => $data) {
            if (($data['group_name'] ?? 'general') === $group) {
                $result[$key] = $data['value'];
            }
        }

        return $result;
    }

    /**
     * Récupère toutes les options.
     *
     * @return array<string, mixed> Toutes les options
     */
    public function all(): array
    {
        $this->loadCache();

        $result = [];
        foreach ($this->cache as $key => $data) {
            $result[$key] = $data['value'];
        }

        return $result;
    }

    /**
     * Récupère toutes les options avec leurs métadonnées.
     *
     * @return array<string, array> Options avec métadonnées
     */
    public function allWithMeta(): array
    {
        $this->loadCache();
        return $this->cache;
    }

    /**
     * Supprime une option.
     *
     * @param string $key Clé de l'option
     */
    public function delete(string $key): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM options WHERE key = ?');
        $stmt->execute([$key]);

        unset($this->cache[$key]);
        $this->saveFileCache();
    }

    /**
     * Vérifie si une option existe.
     *
     * @param string $key Clé de l'option
     * @return bool True si l'option existe
     */
    public function has(string $key): bool
    {
        $this->loadCache();
        return isset($this->cache[$key]);
    }

    /**
     * Charge le cache des options.
     */
    private function loadCache(): void
    {
        if ($this->cacheLoaded) {
            return;
        }

        // Essayer de charger depuis le cache fichier
        $cacheFile = $this->cachePath . '/options.cache.php';
        if (file_exists($cacheFile)) {
            $this->cache = include $cacheFile;
            $this->cacheLoaded = true;
            return;
        }

        // Charger depuis la base de données
        $this->loadFromDatabase();
    }

    /**
     * Charge les options depuis la base de données.
     */
    private function loadFromDatabase(): void
    {
        try {
            $stmt = $this->pdo->query('SELECT * FROM options ORDER BY group_name, sort_order');
            $rows = $stmt->fetchAll();

            $this->cache = [];
            foreach ($rows as $row) {
                $this->cache[$row['key']] = [
                    'value' => $this->decodeValue($row['value'], $row['type'] ?? 'string'),
                    'type' => $row['type'] ?? 'string',
                    'group_name' => $row['group_name'] ?? 'general',
                    'label' => $row['label'] ?? $row['key'],
                    'description' => $row['description'] ?? '',
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'updated_at' => $row['updated_at'] ?? null,
                ];
            }

            $this->cacheLoaded = true;
            $this->saveFileCache();
        } catch (\PDOException $e) {
            // Table n'existe pas encore (avant migration)
            $this->cache = [];
            $this->cacheLoaded = true;
        }
    }

    /**
     * Sauvegarde le cache dans un fichier.
     */
    private function saveFileCache(): void
    {
        $cacheFile = $this->cachePath . '/options.cache.php';
        $cacheDir = dirname($cacheFile);

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $content = '<?php return ' . var_export($this->cache, true) . ';';
        file_put_contents($cacheFile, $content, LOCK_EX);
    }

    /**
     * Vide le cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
        $this->cacheLoaded = false;

        $cacheFile = $this->cachePath . '/options.cache.php';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    /**
     * Encode une valeur pour stockage.
     */
    private function encodeValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        return (string) $value;
    }

    /**
     * Décode une valeur selon son type.
     */
    private function decodeValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value === '1' || $value === 'true',
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            'text', 'string', 'image' => $value,
            default => $value,
        };
    }
}
