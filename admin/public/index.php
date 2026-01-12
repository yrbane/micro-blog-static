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

// Headers de sécurité HTTP (appliqués à toutes les réponses)
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'");
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
header('X-XSS-Protection: 1; mode=block');

// Lancer le FrontController
use Lunar\Service\Core\FrontController;

$frontController = new FrontController();
$frontController->run();
