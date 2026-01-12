<?php
/**
 * Générateur de site statique.
 */
declare(strict_types=1);

namespace Blog\Service;

use Blog\Entity\Post;
use PDO;

/**
 * Génère les pages statiques du blog.
 */
class StaticGenerator
{
    private string $outputDir;
    private string $lockFile;
    private $lockHandle = null;

    public function __construct(
        private PDO $pdo,
        private PostService $postService,
        private CategoryService $categoryService,
        private TagService $tagService,
        private OptionService $optionService,
        private string $projectRoot
    ) {
        $this->outputDir = $projectRoot . '/public/static';
        $this->lockFile = $projectRoot . '/cache/generator.lock';
    }

    /**
     * Génère le site complet.
     */
    public function generateAll(): array
    {
        $log = [];

        if (!$this->acquireLock()) {
            return ['error' => 'Une génération est déjà en cours'];
        }

        try {
            $this->ensureOutputDir();

            // Génération des pages
            $log['index'] = $this->generateIndex();
            $log['posts'] = $this->generatePosts();
            $log['categories'] = $this->generateCategories();
            $log['tags'] = $this->generateTags();
            $log['search_index'] = $this->generateSearchIndex();

            $log['success'] = true;
            $log['generated_at'] = date('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            $log['error'] = $e->getMessage();
            $log['success'] = false;
        } finally {
            $this->releaseLock();
        }

        // Enregistrer le log
        $this->saveLog($log);

        return $log;
    }

    /**
     * Génère la page d'accueil et les pages de pagination.
     */
    public function generateIndex(): array
    {
        $log = ['pages' => 0];
        $postsPerPage = (int) $this->optionService->get('posts_per_page', 10);
        $posts = $this->postService->findPublished();
        $total = count($posts);
        $totalPages = max(1, (int) ceil($total / $postsPerPage));

        for ($page = 1; $page <= $totalPages; $page++) {
            $offset = ($page - 1) * $postsPerPage;
            $pagePosts = array_slice($posts, $offset, $postsPerPage);

            $data = $this->prepareIndexData($pagePosts, $page, $totalPages);

            if ($page === 1) {
                $this->writeFile('/index.html', $this->renderTemplate('public/index', $data));
            }
            $this->writeFile("/page/{$page}/index.html", $this->renderTemplate('public/index', $data));

            $log['pages']++;
        }

        return $log;
    }

    /**
     * Génère les pages d'articles.
     */
    public function generatePosts(): array
    {
        $log = ['count' => 0];
        $posts = $this->postService->findPublished();

        foreach ($posts as $postData) {
            $post = $postData['post'];
            $data = $this->preparePostData($post, $posts);

            $this->writeFile("/post/{$post->slug}/index.html", $this->renderTemplate('public/post', $data));
            $log['count']++;
        }

        return $log;
    }

    /**
     * Génère les pages de catégories.
     */
    public function generateCategories(): array
    {
        $log = ['count' => 0];
        $categories = $this->categoryService->findAll();

        foreach ($categories as $category) {
            $posts = $this->postService->findByCategory($category->id);
            $data = [
                'category' => $category->toArray(),
                'posts' => $this->formatPostsForTemplate($posts),
                'options' => $this->optionService->all(),
            ];

            $path = $category->path ?: $category->slug;
            $this->writeFile("/category/{$path}/index.html", $this->renderTemplate('public/index', $data));
            $log['count']++;
        }

        // Page liste des catégories
        $this->writeFile('/categories/index.html', $this->renderTemplate('public/categories', [
            'categories' => array_map(fn($c) => $c->toArray(), $categories),
            'options' => $this->optionService->all(),
        ]));

        return $log;
    }

    /**
     * Génère les pages de tags.
     */
    public function generateTags(): array
    {
        $log = ['count' => 0];
        $tags = $this->tagService->findAll();

        foreach ($tags as $tag) {
            $posts = $this->postService->findByTag($tag->id);
            $data = [
                'tag' => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug],
                'posts' => $this->formatPostsForTemplate($posts),
                'options' => $this->optionService->all(),
            ];

            $this->writeFile("/tag/{$tag->slug}/index.html", $this->renderTemplate('public/index', $data));
            $log['count']++;
        }

        // Page liste des tags
        $this->writeFile('/tags/index.html', $this->renderTemplate('public/tags', [
            'tags' => array_map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug], $tags),
            'options' => $this->optionService->all(),
        ]));

        return $log;
    }

    /**
     * Génère l'index JSON pour la recherche.
     */
    public function generateSearchIndex(): array
    {
        $posts = $this->postService->findPublished();
        $index = [];

        foreach ($posts as $postData) {
            $post = $postData['post'];
            $index[] = [
                'title' => $post->title,
                'url' => "/post/{$post->slug}/",
                'excerpt' => $post->excerpt ?: $this->generateExcerpt($post->contentMd, 150),
                'content' => strip_tags($post->contentHtml ?? ''),
                'tags' => $postData['extra']['tags'] ?? [],
            ];
        }

        $json = json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->writeFile('/search-index.json', $json);

        return ['posts' => count($index), 'size' => strlen($json)];
    }

    /**
     * Prépare les données pour la page d'index.
     */
    private function prepareIndexData(array $posts, int $page, int $totalPages): array
    {
        $pagination = null;
        if ($totalPages > 1) {
            $pagination = [
                'current' => $page,
                'total' => $totalPages,
                'prev' => $page > 1 ? ($page === 2 ? '/' : "/page/" . ($page - 1) . "/") : null,
                'next' => $page < $totalPages ? "/page/" . ($page + 1) . "/" : null,
            ];
        }

        // Séparer les posts featured
        $featuredPosts = [];
        $regularPosts = [];

        foreach ($posts as $postData) {
            $post = $postData['post'];
            $formatted = $this->formatPostForTemplate($postData);

            if ($post->isFeatured && $page === 1) {
                $featuredPosts[] = $formatted;
            } else {
                $regularPosts[] = $formatted;
            }
        }

        return [
            'featured_posts' => $featuredPosts,
            'posts' => $regularPosts,
            'pagination' => $pagination,
            'options' => $this->optionService->all(),
        ];
    }

    /**
     * Prépare les données pour une page d'article.
     */
    private function preparePostData(Post $post, array $allPosts): array
    {
        // Trouver prev/next
        $prevPost = null;
        $nextPost = null;
        $foundCurrent = false;

        foreach ($allPosts as $postData) {
            $p = $postData['post'];
            if ($p->id === $post->id) {
                $foundCurrent = true;
            } elseif (!$foundCurrent) {
                $prevPost = ['title' => $p->title, 'slug' => $p->slug];
            } elseif ($foundCurrent && $nextPost === null) {
                $nextPost = ['title' => $p->title, 'slug' => $p->slug];
                break;
            }
        }

        // Récupérer les données complètes du post
        $postArray = $post->toArray();

        // Ajouter catégorie et tags
        if ($post->categoryId) {
            $category = $this->categoryService->findById($post->categoryId);
            if ($category) {
                $postArray['category'] = $category->toArray();
            }
        }

        $postArray['tags'] = $this->tagService->findByPost($post->id);

        // Calculer le temps de lecture
        $wordCount = str_word_count(strip_tags($post->contentHtml ?? $post->contentMd));
        $postArray['reading_time'] = max(1, (int) ceil($wordCount / 200));

        return [
            'post' => $postArray,
            'prev_post' => $prevPost,
            'next_post' => $nextPost,
            'options' => $this->optionService->all(),
        ];
    }

    /**
     * Formate les posts pour le template.
     */
    private function formatPostsForTemplate(array $posts): array
    {
        return array_map(fn($p) => $this->formatPostForTemplate($p), $posts);
    }

    /**
     * Formate un post pour le template.
     */
    private function formatPostForTemplate(array $postData): array
    {
        $post = $postData['post'];
        $data = $post->toArray();

        if (isset($postData['extra']['category_name'])) {
            $data['category'] = [
                'name' => $postData['extra']['category_name'],
                'path' => $postData['extra']['category_path'] ?? $post->categoryId,
            ];
        }

        if (isset($postData['extra']['tags'])) {
            $data['tags'] = $postData['extra']['tags'];
        }

        // Temps de lecture
        $wordCount = str_word_count(strip_tags($post->contentHtml ?? $post->contentMd));
        $data['reading_time'] = max(1, (int) ceil($wordCount / 200));

        return $data;
    }

    /**
     * Génère un extrait à partir du contenu.
     */
    private function generateExcerpt(string $content, int $length = 200): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        $text = substr($text, 0, $length);
        $lastSpace = strrpos($text, ' ');
        if ($lastSpace !== false) {
            $text = substr($text, 0, $lastSpace);
        }

        return $text . '...';
    }

    /**
     * Rend un template avec les données.
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Utiliser le moteur de template lunar-template
        $templatePath = $this->projectRoot . '/templates/' . $template . '.tpl';

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        // Simple extraction des variables pour le rendu
        extract($data);

        ob_start();
        // Le rendu réel se fera via lunar-template
        // Pour l'instant, retourner un placeholder
        $html = "<!-- Generated: {$template} -->\n";
        $html .= "<!-- Data: " . json_encode(array_keys($data)) . " -->\n";
        return ob_get_clean() . $html;
    }

    /**
     * Écrit un fichier dans le répertoire de sortie.
     */
    private function writeFile(string $path, string $content): void
    {
        $fullPath = $this->outputDir . $path;
        $dir = dirname($fullPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($fullPath, $content);
    }

    /**
     * Assure que le répertoire de sortie existe.
     */
    private function ensureOutputDir(): void
    {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
    }

    /**
     * Acquiert le verrou de génération.
     */
    private function acquireLock(): bool
    {
        $dir = dirname($this->lockFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->lockHandle = fopen($this->lockFile, 'c');
        if (!$this->lockHandle) {
            return false;
        }

        // Timeout de 5 secondes
        $timeout = 5;
        $start = time();

        while (!flock($this->lockHandle, LOCK_EX | LOCK_NB)) {
            if (time() - $start > $timeout) {
                fclose($this->lockHandle);
                $this->lockHandle = null;
                return false;
            }
            usleep(100000); // 100ms
        }

        return true;
    }

    /**
     * Libère le verrou de génération.
     */
    private function releaseLock(): void
    {
        if ($this->lockHandle) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->lockHandle = null;
        }
    }

    /**
     * Sauvegarde le log de génération.
     */
    private function saveLog(array $log): void
    {
        $logFile = $this->projectRoot . '/cache/generator.log';
        $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($log, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
