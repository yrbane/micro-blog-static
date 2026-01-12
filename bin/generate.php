#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Script de génération du site statique.
 *
 * Usage:
 *   php bin/generate.php              # Génération complète
 *   php bin/generate.php --incremental # Génération incrémentale
 *   php bin/generate.php --help       # Aide
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';

use Blog\Service\StaticGenerator;
use Lunar\Service\Core\Container;

// Couleurs pour la console
const COLOR_GREEN = "\033[32m";
const COLOR_RED = "\033[31m";
const COLOR_YELLOW = "\033[33m";
const COLOR_RESET = "\033[0m";

function info(string $message): void
{
    echo COLOR_GREEN . "✓ " . COLOR_RESET . $message . PHP_EOL;
}

function warning(string $message): void
{
    echo COLOR_YELLOW . "⚠ " . COLOR_RESET . $message . PHP_EOL;
}

function error(string $message): void
{
    echo COLOR_RED . "✗ " . COLOR_RESET . $message . PHP_EOL;
}

// Aide
if (in_array('--help', $argv) || in_array('-h', $argv)) {
    echo <<<HELP

Génération du site statique

Usage:
  php bin/generate.php [options]

Options:
  --incremental, -i   Génération incrémentale (seulement ce qui a changé)
  --force, -f         Forcer la régénération complète
  --verbose, -v       Afficher plus de détails
  --help, -h          Afficher cette aide

Exemples:
  php bin/generate.php                # Génération complète
  php bin/generate.php --incremental  # Seulement les modifications
  php bin/generate.php -f -v          # Forcer + verbose

HELP;
    exit(0);
}

// Options
$incremental = in_array('--incremental', $argv) || in_array('-i', $argv);
$force = in_array('--force', $argv) || in_array('-f', $argv);
$verbose = in_array('--verbose', $argv) || in_array('-v', $argv);

echo PHP_EOL;
echo "╔══════════════════════════════════════════╗" . PHP_EOL;
echo "║     Génération du site statique          ║" . PHP_EOL;
echo "╚══════════════════════════════════════════╝" . PHP_EOL;
echo PHP_EOL;

$startTime = microtime(true);

try {
    // Initialiser le conteneur
    $container = new Container();
    $container->set('config', fn() => $config);

    // Connexion base de données
    $dbPath = $config['database']['path'];
    if (!file_exists($dbPath)) {
        error("Base de données introuvable: $dbPath");
        error("Exécutez d'abord les migrations.");
        exit(1);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $container->set(PDO::class, fn() => $pdo);

    // Générateur statique
    // TODO: Implémenter StaticGenerator
    // $generator = new StaticGenerator($container, $config);

    info("Mode: " . ($incremental ? 'Incrémental' : 'Complet'));
    info("Sortie: " . $config['site']['output_path']);

    // TODO: Appeler la génération
    // $stats = $generator->generate($incremental, $force);

    warning("Le générateur statique n'est pas encore implémenté.");
    warning("Voir issue #33-40 pour l'implémentation.");

    $duration = round(microtime(true) - $startTime, 2);
    echo PHP_EOL;
    info("Terminé en {$duration}s");

} catch (Throwable $e) {
    error($e->getMessage());
    if ($verbose) {
        echo $e->getTraceAsString() . PHP_EOL;
    }
    exit(1);
}
