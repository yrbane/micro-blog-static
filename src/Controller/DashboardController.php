<?php
/**
 * Contrôleur du tableau de bord admin.
 */
declare(strict_types=1);

namespace Lunar\Controller;

use Blog\Middleware\AdminAuthMiddleware;
use Blog\Service\ServiceContainer;
use Lunar\Attribute\Route;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;

class DashboardController extends BaseController
{
    #[Route('/', name: 'dashboard', methods: ['GET'], middlewares: [AdminAuthMiddleware::class])]
    public function index(Request $request): Response
    {
        $auth = ServiceContainer::getAuthService();
        $user = $auth->getCurrentUser();

        $html = $this->render('admin/dashboard', [
            'page_title' => 'Tableau de bord',
            'user' => $user ? $user->toArray() : null,
        ]);

        return new Response($html);
    }
}
