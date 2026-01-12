<?php
/**
 * Contrôleur d'administration des tags.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Entity\Tag;
use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère le CRUD des tags.
 */
class AdminTagController extends BaseController
{
    #[Route('/tags', name: 'admin_tags', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $tagService = ServiceContainer::getTagService();

        $tags = $tagService->findAll();

        // Préparer les données pour le template
        $tagsData = array_map(function(Tag $t) use ($tagService) {
            $data = $t->toArray();
            $data['post_count'] = $tagService->countPosts($t->id);
            return $data;
        }, $tags);

        $html = $this->render('admin/tags/index', [
            'page_title' => 'Tags',
            'user' => $currentUser->toArray(),
            'tags' => $tagsData,
        ]);

        return new Response($html);
    }

    #[Route('/tags/new', name: 'admin_tags_new', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        $html = $this->render('admin/tags/form', [
            'page_title' => 'Nouveau tag',
            'user' => $currentUser->toArray(),
            'tag' => [
                'id' => null,
                'name' => '',
                'slug' => '',
                'description' => '',
            ],
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/tags/new', name: 'admin_tags_store', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function store(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $tagService = ServiceContainer::getTagService();

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = $this->validateTag($name, $slug, null);

        if (!empty($errors)) {
            $html = $this->render('admin/tags/form', [
                'page_title' => 'Nouveau tag',
                'user' => $currentUser->toArray(),
                'tag' => [
                    'id' => null,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ],
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $tag = new Tag(
                name: $name,
                slug: $slug,
                description: $description ?: null,
            );

            $tagService->create($tag);

            $_SESSION['flash_success'] = 'Tag créé avec succès.';
            return new Response('', 302, ['Location: /admin/tags']);
        } catch (\Exception $e) {
            $html = $this->render('admin/tags/form', [
                'page_title' => 'Nouveau tag',
                'user' => $currentUser->toArray(),
                'tag' => [
                    'id' => null,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ],
                'errors' => ['general' => 'Erreur: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/tags/{id}/edit', name: 'admin_tags_edit', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function edit(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $tagService = ServiceContainer::getTagService();

        $id = (int) $request->getRouteParam('id');
        $tag = $tagService->findById($id);

        if ($tag === null) {
            $_SESSION['flash_error'] = 'Tag introuvable.';
            return new Response('', 302, ['Location: /admin/tags']);
        }

        $html = $this->render('admin/tags/form', [
            'page_title' => 'Modifier ' . $tag->name,
            'user' => $currentUser->toArray(),
            'tag' => $tag->toArray(),
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/tags/{id}/edit', name: 'admin_tags_update', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function update(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $tagService = ServiceContainer::getTagService();

        $id = (int) $request->getRouteParam('id');
        $tag = $tagService->findById($id);

        if ($tag === null) {
            $_SESSION['flash_error'] = 'Tag introuvable.';
            return new Response('', 302, ['Location: /admin/tags']);
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = $this->validateTag($name, $slug, $id);

        if (!empty($errors)) {
            $html = $this->render('admin/tags/form', [
                'page_title' => 'Modifier ' . $tag->name,
                'user' => $currentUser->toArray(),
                'tag' => [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ],
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $tag->name = $name;
            $tag->slug = $slug;
            $tag->description = $description ?: null;

            $tagService->update($tag);

            $_SESSION['flash_success'] = 'Tag mis à jour avec succès.';
            return new Response('', 302, ['Location: /admin/tags']);
        } catch (\Exception $e) {
            $html = $this->render('admin/tags/form', [
                'page_title' => 'Modifier ' . $tag->name,
                'user' => $currentUser->toArray(),
                'tag' => [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ],
                'errors' => ['general' => 'Erreur: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/tags/{id}/delete', name: 'admin_tags_delete', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function delete(Request $request): Response
    {
        $tagService = ServiceContainer::getTagService();

        $id = (int) $request->getRouteParam('id');

        if ($tagService->delete($id)) {
            $_SESSION['flash_success'] = 'Tag supprimé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        return new Response('', 302, ['Location: /admin/tags']);
    }

    /**
     * Valide les données d'un tag.
     *
     * @return array<string, string> Tableau des erreurs
     */
    private function validateTag(string $name, string $slug, ?int $tagId): array
    {
        $errors = [];
        $tagService = ServiceContainer::getTagService();

        if (empty($name)) {
            $errors['name'] = 'Le nom est requis.';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Le nom doit faire au moins 2 caractères.';
        }

        if (!empty($slug)) {
            if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                $errors['slug'] = 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.';
            } else {
                $existing = $tagService->findBySlug($slug);
                if ($existing !== null && $existing->id !== $tagId) {
                    $errors['slug'] = 'Ce slug est déjà utilisé.';
                }
            }
        }

        return $errors;
    }
}
