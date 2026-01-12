<?php
/**
 * Contrôleur d'administration des articles.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Entity\Post;
use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère le CRUD des articles.
 */
class AdminPostController extends BaseController
{
    #[Route('/posts', name: 'admin_posts', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $postService = ServiceContainer::getPostService();

        $posts = $postService->findAll();
        $counts = $postService->countByStatus();

        // Préparer les données pour le template
        $postsData = array_map(function($item) {
            $post = $item['post'];
            $data = $post->toArray();
            $data['author_name'] = $item['extra']['author_name'] ?? 'Inconnu';
            $data['category_name'] = $item['extra']['category_name'] ?? '';
            $data['status_class'] = $post->getStatusClass();
            return $data;
        }, $posts);

        $html = $this->render('admin/posts/index', [
            'page_title' => 'Articles',
            'user' => $currentUser->toArray(),
            'posts' => $postsData,
            'counts' => $counts,
        ]);

        return new Response($html);
    }

    #[Route('/posts/new', name: 'admin_posts_new', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        $html = $this->render('admin/posts/form', [
            'page_title' => 'Nouvel article',
            'user' => $currentUser->toArray(),
            'post' => $this->getEmptyPost(),
            'categories' => $this->getCategories(),
            'tags' => $this->getTags(),
            'statuses' => Post::STATUSES,
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/posts/new', name: 'admin_posts_store', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function store(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $postService = ServiceContainer::getPostService();

        $data = $this->getPostData();
        $errors = $this->validatePost($data, null);

        if (!empty($errors)) {
            $html = $this->render('admin/posts/form', [
                'page_title' => 'Nouvel article',
                'user' => $currentUser->toArray(),
                'post' => $data,
                'categories' => $this->getCategories(),
                'tags' => $this->getTags(),
                'statuses' => Post::STATUSES,
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $post = new Post(
                slug: $data['slug'],
                slugLocked: (bool) $data['slug_locked'],
                title: $data['title'],
                contentMd: $data['content_md'],
                contentHtml: $postService->parseMarkdown($data['content_md']),
                excerpt: $data['excerpt'] ?: null,
                status: $data['status'],
                categoryId: $data['category_id'] ?: null,
                authorId: $currentUser->id,
                seoTitle: $data['seo_title'] ?: null,
                seoDescription: $data['seo_description'] ?: null,
                ogImage: $data['og_image'] ?: null,
                isFeatured: (bool) $data['is_featured'],
                tagIds: $data['tag_ids'],
            );

            $postService->create($post);

            $_SESSION['flash_success'] = 'Article créé avec succès.';
            return new Response('', 302, ['Location: /admin/posts']);
        } catch (\Exception $e) {
            $html = $this->render('admin/posts/form', [
                'page_title' => 'Nouvel article',
                'user' => $currentUser->toArray(),
                'post' => $data,
                'categories' => $this->getCategories(),
                'tags' => $this->getTags(),
                'statuses' => Post::STATUSES,
                'errors' => ['general' => 'Erreur: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/posts/{id}/edit', name: 'admin_posts_edit', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function edit(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $postService = ServiceContainer::getPostService();

        $id = (int) $request->getRouteParam('id');
        $post = $postService->findById($id);

        if ($post === null) {
            $_SESSION['flash_error'] = 'Article introuvable.';
            return new Response('', 302, ['Location: /admin/posts']);
        }

        $html = $this->render('admin/posts/form', [
            'page_title' => 'Modifier l\'article',
            'user' => $currentUser->toArray(),
            'post' => $post->toArray(),
            'categories' => $this->getCategories(),
            'tags' => $this->getTags(),
            'statuses' => Post::STATUSES,
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/posts/{id}/edit', name: 'admin_posts_update', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function update(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $postService = ServiceContainer::getPostService();

        $id = (int) $request->getRouteParam('id');
        $post = $postService->findById($id);

        if ($post === null) {
            $_SESSION['flash_error'] = 'Article introuvable.';
            return new Response('', 302, ['Location: /admin/posts']);
        }

        $data = $this->getPostData();
        $data['id'] = $id;
        $errors = $this->validatePost($data, $id);

        if (!empty($errors)) {
            $html = $this->render('admin/posts/form', [
                'page_title' => 'Modifier l\'article',
                'user' => $currentUser->toArray(),
                'post' => $data,
                'categories' => $this->getCategories(),
                'tags' => $this->getTags(),
                'statuses' => Post::STATUSES,
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $post->slug = $data['slug'];
            $post->slugLocked = (bool) $data['slug_locked'];
            $post->title = $data['title'];
            $post->contentMd = $data['content_md'];
            $post->contentHtml = $postService->parseMarkdown($data['content_md']);
            $post->excerpt = $data['excerpt'] ?: null;
            $post->status = $data['status'];
            $post->categoryId = $data['category_id'] ?: null;
            $post->seoTitle = $data['seo_title'] ?: null;
            $post->seoDescription = $data['seo_description'] ?: null;
            $post->ogImage = $data['og_image'] ?: null;
            $post->isFeatured = (bool) $data['is_featured'];
            $post->tagIds = $data['tag_ids'];

            $postService->update($post);

            $_SESSION['flash_success'] = 'Article mis à jour avec succès.';
            return new Response('', 302, ['Location: /admin/posts']);
        } catch (\Exception $e) {
            $html = $this->render('admin/posts/form', [
                'page_title' => 'Modifier l\'article',
                'user' => $currentUser->toArray(),
                'post' => $data,
                'categories' => $this->getCategories(),
                'tags' => $this->getTags(),
                'statuses' => Post::STATUSES,
                'errors' => ['general' => 'Erreur: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/posts/{id}/delete', name: 'admin_posts_delete', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function delete(Request $request): Response
    {
        $postService = ServiceContainer::getPostService();

        $id = (int) $request->getRouteParam('id');

        if ($postService->delete($id)) {
            $_SESSION['flash_success'] = 'Article supprimé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        return new Response('', 302, ['Location: /admin/posts']);
    }

    /**
     * Récupère les données du formulaire.
     */
    private function getPostData(): array
    {
        return [
            'id' => null,
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'slug_locked' => isset($_POST['slug_locked']),
            'content_md' => $_POST['content_md'] ?? '',
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'status' => $_POST['status'] ?? Post::STATUS_DRAFT,
            'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'seo_title' => trim($_POST['seo_title'] ?? ''),
            'seo_description' => trim($_POST['seo_description'] ?? ''),
            'og_image' => trim($_POST['og_image'] ?? ''),
            'is_featured' => isset($_POST['is_featured']),
            'tag_ids' => isset($_POST['tag_ids']) ? array_map('intval', (array) $_POST['tag_ids']) : [],
        ];
    }

    /**
     * Retourne un article vide pour le formulaire.
     */
    private function getEmptyPost(): array
    {
        return [
            'id' => null,
            'title' => '',
            'slug' => '',
            'slug_locked' => false,
            'content_md' => '',
            'excerpt' => '',
            'status' => Post::STATUS_DRAFT,
            'category_id' => null,
            'seo_title' => '',
            'seo_description' => '',
            'og_image' => '',
            'is_featured' => false,
            'tag_ids' => [],
        ];
    }

    /**
     * Récupère les catégories pour le select.
     */
    private function getCategories(): array
    {
        $categoryService = ServiceContainer::getCategoryService();
        return $categoryService->findAllForSelect();
    }

    /**
     * Récupère les tags pour les checkboxes.
     */
    private function getTags(): array
    {
        $tagService = ServiceContainer::getTagService();
        $tags = $tagService->findAll();

        return array_map(fn($t) => ['id' => $t->id, 'name' => $t->name], $tags);
    }

    /**
     * Valide les données d'un article.
     */
    private function validatePost(array $data, ?int $postId): array
    {
        $errors = [];
        $postService = ServiceContainer::getPostService();

        if (empty($data['title'])) {
            $errors['title'] = 'Le titre est requis.';
        } elseif (strlen($data['title']) < 3) {
            $errors['title'] = 'Le titre doit faire au moins 3 caractères.';
        }

        if (empty($data['content_md'])) {
            $errors['content_md'] = 'Le contenu est requis.';
        }

        if (!empty($data['slug'])) {
            if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
                $errors['slug'] = 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.';
            } else {
                $existing = $postService->findBySlug($data['slug']);
                if ($existing !== null && $existing->id !== $postId) {
                    $errors['slug'] = 'Ce slug est déjà utilisé.';
                }
            }
        }

        if (!array_key_exists($data['status'], Post::STATUSES)) {
            $errors['status'] = 'Statut invalide.';
        }

        return $errors;
    }
}
