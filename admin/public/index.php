<?php

declare(strict_types=1);

/**
 * Point d'entrée de l'administration du blog.
 *
 * Ce fichier initialise l'application admin et dispatche les requêtes
 * vers les contrôleurs appropriés via le router de lunar-quanta.
 */

// Autoloader Composer
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Configuration
$config = require dirname(__DIR__, 2) . '/config/config.php';

// Mode debug
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Timezone
date_default_timezone_set($config['app']['timezone']);

use Lunar\Service\Core\Container;
use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;
use Lunar\Service\Core\Router;
use Blog\Service\BlogApp;

try {
    // Créer le conteneur de services
    $container = new Container();

    // Enregistrer la configuration
    $container->set('config', fn() => $config);

    // Initialiser l'application Blog
    $app = new BlogApp($container, $config);
    $app->boot();

    // Créer la requête à partir des globales
    $request = Request::createFromGlobals();

    // Router et dispatch
    $router = $container->get(Router::class);
    $response = $router->dispatch($request);

    // Envoyer la réponse
    $response->send();

} catch (Throwable $e) {
    // Gestion des erreurs
    if ($config['app']['debug']) {
        echo '<h1>Erreur</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        // Log l'erreur
        error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        // Page d'erreur générique
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Erreur</title></head>';
        echo '<body><h1>Une erreur est survenue</h1></body></html>';
    }
}
