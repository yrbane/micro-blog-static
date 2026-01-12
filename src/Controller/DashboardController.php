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
        $pdo = ServiceContainer::getPdo();

        // Récupérer les statistiques
        $stats = [
            'posts' => $this->getCount($pdo, 'posts'),
            'categories' => $this->getCount($pdo, 'categories'),
            'tags' => $this->getCount($pdo, 'tags'),
            'last_generation' => $this->getLastGeneration($pdo),
        ];

        $html = $this->render('admin/dashboard', [
            'page_title' => 'Tableau de bord',
            'user' => $user ? $user->toArray() : null,
            'stats' => $stats,
        ]);

        return new Response($html);
    }

    private function getCount(\PDO $pdo, string $table): int
    {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getLastGeneration(\PDO $pdo): string
    {
        try {
            $stmt = $pdo->query("SELECT value FROM options WHERE key = 'last_generation'");
            $result = $stmt->fetchColumn();
            if ($result) {
                $date = new \DateTime($result);
                return $date->format('d/m/Y H:i');
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return '-';
    }
}
