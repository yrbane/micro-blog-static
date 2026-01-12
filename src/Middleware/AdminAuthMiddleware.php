<?php
/**
 * Middleware d'authentification admin.
 */
declare(strict_types=1);

namespace Blog\Middleware;

use Blog\Entity\User;
use Blog\Service\AuthService;
use Blog\Service\ServiceContainer;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;
use Lunar\Service\Core\Middleware\MiddlewareInterface;

/**
 * Protège les routes admin et vérifie les rôles.
 *
 * Ce middleware :
 * 1. Vérifie si l'utilisateur est connecté
 * 2. Vérifie si l'utilisateur a accès à l'admin (ADMIN ou REDACTOR)
 * 3. Optionnellement vérifie des permissions spécifiques
 */
class AdminAuthMiddleware implements MiddlewareInterface
{
    private AuthService $authService;
    private ?string $requiredPermission;
    private string $loginUrl;

    /**
     * @param string|null $requiredPermission Permission requise (optionnel)
     * @param string $loginUrl URL de redirection si non connecté
     */
    public function __construct(
        ?string $requiredPermission = null,
        string $loginUrl = '/admin/login',
    ) {
        $this->authService = ServiceContainer::getAuthService();
        $this->requiredPermission = $requiredPermission;
        $this->loginUrl = $loginUrl;
    }

    public function process(Request $request, callable $next): Response
    {
        $user = $this->authService->getCurrentUser();

        // Pas connecté -> redirection vers login
        if ($user === null) {
            // Sauvegarder l'URL demandée pour redirection après login
            $_SESSION['redirect_after_login'] = $request->getUri();

            return new Response('', 302, ['Location: ' . $this->loginUrl]);
        }

        // Pas d'accès admin (USER role) -> 403
        if (!$user->canAccessAdmin()) {
            return new Response(
                $this->renderForbiddenPage($user),
                403,
                ['Content-Type: text/html; charset=UTF-8']
            );
        }

        // Permission spécifique requise ?
        if ($this->requiredPermission !== null && !$user->hasPermission($this->requiredPermission)) {
            return new Response(
                $this->renderForbiddenPage($user, $this->requiredPermission),
                403,
                ['Content-Type: text/html; charset=UTF-8']
            );
        }

        // Attacher l'utilisateur à la requête pour les contrôleurs
        $request->setAttribute('user', $user);
        $request->setAttribute('auth', $this->authService);

        return $next($request);
    }

    /**
     * Génère une page d'erreur 403 simple.
     */
    private function renderForbiddenPage(User $user, ?string $permission = null): string
    {
        $message = $permission !== null
            ? "Vous n'avez pas la permission '{$permission}'."
            : "Votre rôle ({$user->getRoleLabel()}) ne permet pas d'accéder à cette section.";

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès refusé</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f3f4f6; }
        .container { text-align: center; padding: 2rem; }
        h1 { font-size: 4rem; color: #9ca3af; margin: 0; }
        p { color: #6b7280; margin: 1rem 0 2rem; }
        a { display: inline-block; padding: 0.5rem 1rem; background: #4f46e5; color: white; text-decoration: none; border-radius: 0.375rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>403</h1>
        <p>{$message}</p>
        <a href="/admin">Retour au tableau de bord</a>
    </div>
</body>
</html>
HTML;
    }
}
