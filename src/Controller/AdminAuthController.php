<?php
/**
 * Contrôleur d'authentification admin.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

/**
 * Gère l'authentification admin (login/logout).
 */
class AdminAuthController extends BaseController
{
    #[Route('/login', name: 'admin_login', methods: ['GET'])]
    public function loginForm(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();

        // Déjà connecté -> redirection vers dashboard
        if ($auth->isAuthenticated()) {
            return new Response('', 302, ['Location: /admin']);
        }

        $error = $_GET['error'] ?? null;
        $html = $this->render('admin/auth/login', [
            'page_title' => 'Connexion',
            'error' => $error,
        ]);

        return new Response($html);
    }

    #[Route('/login', name: 'admin_login_post', methods: ['POST'])]
    public function login(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validation basique
        if (empty($email) || empty($password)) {
            return new Response('', 302, ['Location: /admin/login?error=empty']);
        }

        // Tentative de connexion
        $user = $auth->login($email, $password);

        if ($user === null) {
            return new Response('', 302, ['Location: /admin/login?error=invalid']);
        }

        // Vérifier l'accès admin
        if (!$user->canAccessAdmin()) {
            $auth->logout();
            return new Response('', 302, ['Location: /admin/login?error=noaccess']);
        }

        // Redirection après login
        $redirect = $_SESSION['redirect_after_login'] ?? '/admin';
        unset($_SESSION['redirect_after_login']);

        return new Response('', 302, ['Location: ' . $redirect]);
    }

    #[Route('/logout', name: 'admin_logout', methods: ['GET', 'POST'])]
    public function logout(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $auth->logout();

        return new Response('', 302, ['Location: /admin/login']);
    }
}
