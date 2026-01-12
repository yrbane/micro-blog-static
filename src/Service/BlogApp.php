<?php

declare(strict_types=1);

namespace Blog\Service;

use Lunar\Service\Core\Container;
use Lunar\Service\Core\Router;
use Lunar\Service\Core\BaseController;
use Lunar\Service\Core\Middleware\MiddlewareStack;
use Lunar\Service\Session\SessionMiddleware;
use Lunar\Service\Security\Csrf\CsrfMiddleware;
use Lunar\Template\AdvancedTemplateEngine;
use Blog\Middleware\SecurityHeadersMiddleware;

/**
 * Classe principale de l'application Blog.
 *
 * Responsable du bootstrap de l'application :
 * - Configuration des services
 * - Enregistrement des middlewares
 * - Configuration du moteur de templates
 * - Scan des contrôleurs
 */
class BlogApp
{
    public function __construct(
        private Container $container,
        private array $config
    ) {}

    /**
     * Initialise l'application.
     */
    public function boot(): void
    {
        $this->registerServices();
        $this->registerMiddlewares();
        $this->configureTemplateEngine();
        $this->configureRouter();
    }

    /**
     * Enregistre les services dans le conteneur.
     */
    private function registerServices(): void
    {
        // Base de données
        $this->container->set(\PDO::class, function () {
            $dbPath = $this->config['database']['path'];
            $pdo = new \PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            // Activer les clés étrangères SQLite
            $pdo->exec('PRAGMA foreign_keys = ON');
            return $pdo;
        });

        // Service des options
        $this->container->set(OptionService::class, function () {
            return new OptionService(
                $this->container->get(\PDO::class),
                $this->config['paths']['cache']
            );
        });

        // Moteur de templates
        $this->container->set(AdvancedTemplateEngine::class, function () {
            $engine = new AdvancedTemplateEngine(
                $this->config['template']['path'],
                $this->config['template']['cache']
            );

            // Enregistrer les variables globales
            $optionService = $this->container->get(OptionService::class);
            $engine->setDefaultVariables([
                'config' => $this->config,
                'option' => fn(string $key, mixed $default = null) => $optionService->get($key, $default),
            ]);

            return $engine;
        });
    }

    /**
     * Enregistre les middlewares.
     */
    private function registerMiddlewares(): void
    {
        $this->container->set(MiddlewareStack::class, function () {
            $stack = new MiddlewareStack();

            // Middlewares globaux
            $stack->add(new SecurityHeadersMiddleware());
            $stack->add(new SessionMiddleware());
            $stack->add(new CsrfMiddleware());

            return $stack;
        });
    }

    /**
     * Configure le moteur de templates.
     */
    private function configureTemplateEngine(): void
    {
        $engine = $this->container->get(AdvancedTemplateEngine::class);

        // Macro pour les options
        $optionService = $this->container->get(OptionService::class);
        $engine->registerMacro('option', function (string $key, mixed $default = null) use ($optionService) {
            return $optionService->get($key, $default);
        });

        // Macro pour les URLs
        $engine->registerMacro('url', function (string $path = '') {
            $baseUrl = rtrim($this->config['site']['url'], '/');
            return $baseUrl . '/' . ltrim($path, '/');
        });

        // Macro pour les assets
        $engine->registerMacro('asset', function (string $path) {
            return '/assets/' . ltrim($path, '/');
        });
    }

    /**
     * Configure le router et scanne les contrôleurs.
     */
    private function configureRouter(): void
    {
        $router = new Router($this->container);

        // Scanner les contrôleurs admin
        $controllerPath = dirname(__DIR__, 2) . '/admin/src/Controller';
        if (is_dir($controllerPath)) {
            $router->scanControllers($controllerPath, 'Blog\\Admin\\Controller');
        }

        $this->container->set(Router::class, fn() => $router);
    }
}
