<?php
/**
 * Point d'entrée Administration - Blog
 *
 * Utilise le FrontController de lunar-quanta.
 */
declare(strict_types=1);

// Définir la racine du projet
define('PROJECT_ROOT', dirname(__DIR__, 2));

// Autoloader
require PROJECT_ROOT . '/vendor/autoload.php';

// Lancer le FrontController
use Lunar\Service\Core\FrontController;

$frontController = new FrontController();
$frontController->run();
