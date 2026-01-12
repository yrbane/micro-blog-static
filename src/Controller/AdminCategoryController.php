<?php
/**
 * Contrôleur d'administration des catégories.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Entity\Category;
use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère le CRUD des catégories.
 */
class AdminCategoryController extends BaseController
{
    #[Route('/categories', name: 'admin_categories', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $categoryService = ServiceContainer::getCategoryService();

        $categories = $categoryService->findAll();

        // Préparer les données pour le template
        $categoriesData = array_map(function(Category $c) use ($categoryService) {
            $data = $c->toArray();
            $data['indented_name'] = $c->getIndentedName();
            $data['post_count'] = $categoryService->countPosts($c->id);
            return $data;
        }, $categories);

        $html = $this->render('admin/categories/index', [
            'page_title' => 'Catégories',
            'user' => $currentUser->toArray(),
            'categories' => $categoriesData,
        ]);

        return new Response($html);
    }

    #[Route('/categories/new', name: 'admin_categories_new', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $categoryService = ServiceContainer::getCategoryService();

        $html = $this->render('admin/categories/form', [
            'page_title' => 'Nouvelle catégorie',
            'user' => $currentUser->toArray(),
            'category' => [
                'id' => null,
                'name' => '',
                'slug' => '',
                'description' => '',
                'parent_id' => null,
                'sort_order' => 0,
            ],
            'parents' => $categoryService->findAllForSelect(),
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/categories/new', name: 'admin_categories_store', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function store(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $categoryService = ServiceContainer::getCategoryService();

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        $errors = $this->validateCategory($name, $slug, null);

        if (!empty($errors)) {
            $html = $this->render('admin/categories/form', [
                'page_title' => 'Nouvelle catégorie',
                'user' => $currentUser->toArray(),
                'category' => [
                    'id' => null,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                ],
                'parents' => $categoryService->findAllForSelect(),
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $category = new Category(
                name: $name,
                slug: $slug,
                description: $description ?: null,
                parentId: $parentId,
                sortOrder: $sortOrder,
            );

            $categoryService->create($category);

            $_SESSION['flash_success'] = 'Catégorie créée avec succès.';
            return new Response('', 302, ['Location: /admin/categories']);
        } catch (\Exception $e) {
            $html = $this->render('admin/categories/form', [
                'page_title' => 'Nouvelle catégorie',
                'user' => $currentUser->toArray(),
                'category' => [
                    'id' => null,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                ],
                'parents' => $categoryService->findAllForSelect(),
                'errors' => ['general' => 'Erreur: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/categories/{id}/edit', name: 'admin_categories_edit', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function edit(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $categoryService = ServiceContainer::getCategoryService();

        $id = (int) $request->getRouteParam('id');
        $category = $categoryService->findById($id);

        if ($category === null) {
            $_SESSION['flash_error'] = 'Catégorie introuvable.';
            return new Response('', 302, ['Location: /admin/categories']);
        }

        // Exclure la catégorie courante et ses enfants des parents possibles
        $allCategories = $categoryService->findAllForSelect();
        $parents = array_filter($allCategories, function($c) use ($category) {
            return $c['id'] !== $category->id;
        });

        $html = $this->render('admin/categories/form', [
            'page_title' => 'Modifier ' . $category->name,
            'user' => $currentUser->toArray(),
            'category' => $category->toArray(),
            'parents' => array_values($parents),
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/categories/{id}/edit', name: 'admin_categories_update', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function update(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();
        $categoryService = ServiceContainer::getCategoryService();

        $id = (int) $request->getRouteParam('id');
        $category = $categoryService->findById($id);

        if ($category === null) {
            $_SESSION['flash_error'] = 'Catégorie introuvable.';
            return new Response('', 302, ['Location: /admin/categories']);
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        // Empêcher de se mettre soi-même comme parent
        if ($parentId === $id) {
            $parentId = $category->parentId;
        }

        $errors = $this->validateCategory($name, $slug, $id);

        if (!empty($errors)) {
            $allCategories = $categoryService->findAllForSelect();
            $parents = array_filter($allCategories, fn($c) => $c['id'] !== $id);

            $html = $this->render('admin/categories/form', [
                'page_title' => 'Modifier ' . $category->name,
                'user' => $currentUser->toArray(),
                'category' => [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                ],
                'parents' => array_values($parents),
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $category->name = $name;
            $category->slug = $slug;
            $category->description = $description ?: null;
            $category->parentId = $parentId;
            $category->sortOrder = $sortOrder;

            $categoryService->update($category);

            $_SESSION['flash_success'] = 'Catégorie mise à jour avec succès.';
            return new Response('', 302, ['Location: /admin/categories']);
        } catch (\Exception $e) {
            $allCategories = $categoryService->findAllForSelect();
            $parents = array_filter($allCategories, fn($c) => $c['id'] !== $id);

            $html = $this->render('admin/categories/form', [
                'page_title' => 'Modifier ' . $category->name,
                'user' => $currentUser->toArray(),
                'category' => [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                    'parent_id' => $parentId,
                    'sort_order' => $sortOrder,
                ],
                'parents' => array_values($parents),
                'errors' => ['general' => 'Erreur: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/categories/{id}/delete', name: 'admin_categories_delete', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function delete(Request $request): Response
    {
        $categoryService = ServiceContainer::getCategoryService();

        $id = (int) $request->getRouteParam('id');

        if ($categoryService->delete($id)) {
            $_SESSION['flash_success'] = 'Catégorie supprimée avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        return new Response('', 302, ['Location: /admin/categories']);
    }

    /**
     * Valide les données d'une catégorie.
     *
     * @return array<string, string> Tableau des erreurs
     */
    private function validateCategory(string $name, string $slug, ?int $categoryId): array
    {
        $errors = [];
        $categoryService = ServiceContainer::getCategoryService();

        if (empty($name)) {
            $errors['name'] = 'Le nom est requis.';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Le nom doit faire au moins 2 caractères.';
        }

        if (!empty($slug)) {
            // Vérifie le format du slug
            if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                $errors['slug'] = 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.';
            } else {
                // Vérifie l'unicité
                $existing = $categoryService->findBySlug($slug);
                if ($existing !== null && $existing->id !== $categoryId) {
                    $errors['slug'] = 'Ce slug est déjà utilisé.';
                }
            }
        }

        return $errors;
    }
}
