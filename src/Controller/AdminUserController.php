<?php
/**
 * Contrôleur d'administration des utilisateurs.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Entity\User;
use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère le CRUD des utilisateurs (admin uniquement).
 */
class AdminUserController extends BaseController
{
    #[Route('/users', name: 'admin_users', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        // Seuls les admins peuvent gérer les utilisateurs
        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $users = $auth->listUsers();
        $currentUserId = $currentUser->id;

        // Préparer les données pour le template
        $usersData = array_map(function(User $u) use ($currentUserId) {
            $data = $u->toArray();
            $data['initial'] = strtoupper(substr($u->username, 0, 1));
            $data['role_class'] = match($u->role) {
                User::ROLE_ADMIN => 'primary',
                User::ROLE_REDACTOR => 'info',
                default => 'secondary',
            };
            $data['can_delete'] = ($u->id !== $currentUserId);
            return $data;
        }, $users);

        $html = $this->render('admin/users/index', [
            'page_title' => 'Utilisateurs',
            'user' => $currentUser->toArray(),
            'users' => $usersData,
            'roles' => $this->getRolesArray(),
        ]);

        return new Response($html);
    }

    #[Route('/users/new', name: 'admin_users_new', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function create(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $html = $this->render('admin/users/form', [
            'page_title' => 'Nouvel utilisateur',
            'user' => $currentUser->toArray(),
            'edit_user' => [
                'id' => null,
                'username' => '',
                'email' => '',
                'role' => '',
                'is_active' => true,
            ],
            'roles' => $this->getRolesArray(),
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/users/new', name: 'admin_users_store', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function store(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? User::ROLE_USER;
        $isActive = isset($_POST['is_active']);

        $errors = $this->validateUser($username, $email, $password, $role, null);

        if (!empty($errors)) {
            $html = $this->render('admin/users/form', [
                'page_title' => 'Nouvel utilisateur',
                'user' => $currentUser->toArray(),
                'edit_user' => [
                    'id' => null,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'is_active' => $isActive,
                ],
                'roles' => $this->getRolesArray(),
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $newUser = $auth->createUser($username, $email, $password, $role);
            if (!$isActive) {
                $newUser->isActive = false;
                $auth->updateUser($newUser);
            }

            $_SESSION['flash_success'] = 'Utilisateur créé avec succès.';
            return new Response('', 302, ['Location: /admin/users']);
        } catch (\Exception $e) {
            $html = $this->render('admin/users/form', [
                'page_title' => 'Nouvel utilisateur',
                'user' => $currentUser->toArray(),
                'edit_user' => [
                    'id' => null,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'is_active' => $isActive,
                ],
                'roles' => $this->getRolesArray(),
                'errors' => ['general' => 'Erreur lors de la création: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/users/{id}/edit', name: 'admin_users_edit', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function edit(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $id = (int) $request->getRouteParam('id');
        $editUser = $auth->findUserById($id);

        if ($editUser === null) {
            $_SESSION['flash_error'] = 'Utilisateur introuvable.';
            return new Response('', 302, ['Location: /admin/users']);
        }

        $html = $this->render('admin/users/form', [
            'page_title' => 'Modifier ' . $editUser->username,
            'user' => $currentUser->toArray(),
            'edit_user' => $editUser->toArray(),
            'roles' => $this->getRolesArray(),
            'errors' => [],
        ]);

        return new Response($html);
    }

    #[Route('/users/{id}/edit', name: 'admin_users_update', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function update(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $id = (int) $request->getRouteParam('id');
        $editUser = $auth->findUserById($id);

        if ($editUser === null) {
            $_SESSION['flash_error'] = 'Utilisateur introuvable.';
            return new Response('', 302, ['Location: /admin/users']);
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? $editUser->role;
        $isActive = isset($_POST['is_active']);

        // Ne pas permettre de désactiver son propre compte ou de changer son propre rôle
        if ($editUser->id === $currentUser->id) {
            $role = $currentUser->role;
            $isActive = true;
        }

        $errors = $this->validateUser($username, $email, $password, $role, $editUser->id);

        if (!empty($errors)) {
            $html = $this->render('admin/users/form', [
                'page_title' => 'Modifier ' . $editUser->username,
                'user' => $currentUser->toArray(),
                'edit_user' => [
                    'id' => $editUser->id,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'is_active' => $isActive,
                    'created_at' => $editUser->createdAt?->format('Y-m-d H:i:s'),
                    'last_login_at' => $editUser->lastLoginAt?->format('Y-m-d H:i:s'),
                ],
                'roles' => $this->getRolesArray(),
                'errors' => $errors,
            ]);
            return new Response($html);
        }

        try {
            $editUser->username = $username;
            $editUser->email = $email;
            $editUser->role = $role;
            $editUser->isActive = $isActive;

            $auth->updateUser($editUser);

            // Changer le mot de passe si fourni
            if (!empty($password)) {
                $auth->changePassword($editUser, $password);
            }

            $_SESSION['flash_success'] = 'Utilisateur mis à jour avec succès.';
            return new Response('', 302, ['Location: /admin/users']);
        } catch (\Exception $e) {
            $html = $this->render('admin/users/form', [
                'page_title' => 'Modifier ' . $editUser->username,
                'user' => $currentUser->toArray(),
                'edit_user' => [
                    'id' => $editUser->id,
                    'username' => $username,
                    'email' => $email,
                    'role' => $role,
                    'is_active' => $isActive,
                ],
                'roles' => $this->getRolesArray(),
                'errors' => ['general' => 'Erreur lors de la mise à jour: ' . $e->getMessage()],
            ]);
            return new Response($html);
        }
    }

    #[Route('/users/{id}/delete', name: 'admin_users_delete', methods: ['POST'], middlewares: [AdminAuthMiddleware::class])]
    public function delete(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $currentUser = $auth->getCurrentUser();

        if (!$currentUser?->isAdmin()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $id = (int) $request->getRouteParam('id');

        // Ne pas permettre de supprimer son propre compte
        if ($id === $currentUser->id) {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            return new Response('', 302, ['Location: /admin/users']);
        }

        if ($auth->deleteUser($id)) {
            $_SESSION['flash_success'] = 'Utilisateur supprimé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la suppression.';
        }

        return new Response('', 302, ['Location: /admin/users']);
    }

    /**
     * Convertit les rôles en tableau pour le template.
     *
     * @return array<array{key: string, label: string}>
     */
    private function getRolesArray(): array
    {
        $roles = [];
        foreach (User::ROLES as $key => $label) {
            $roles[] = ['key' => $key, 'label' => $label];
        }
        return $roles;
    }

    /**
     * Valide les données d'un utilisateur.
     *
     * @return array<string, string> Tableau des erreurs
     */
    private function validateUser(string $username, string $email, string $password, string $role, ?int $userId): array
    {
        $errors = [];
        $auth = ServiceContainer::getAuthService();

        if (empty($username)) {
            $errors['username'] = 'Le nom d\'utilisateur est requis.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Le nom d\'utilisateur doit faire au moins 3 caractères.';
        }

        if (empty($email)) {
            $errors['email'] = 'L\'email est requis.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide.';
        } else {
            // Vérifier l'unicité de l'email
            $existingUser = $auth->findUserByEmail($email);
            if ($existingUser !== null && $existingUser->id !== $userId) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }
        }

        // Le mot de passe est requis uniquement pour les nouveaux utilisateurs
        if ($userId === null && empty($password)) {
            $errors['password'] = 'Le mot de passe est requis.';
        } elseif (!empty($password) && strlen($password) < 8) {
            $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères.';
        }

        if (!array_key_exists($role, User::ROLES)) {
            $errors['role'] = 'Le rôle sélectionné n\'est pas valide.';
        }

        return $errors;
    }
}
