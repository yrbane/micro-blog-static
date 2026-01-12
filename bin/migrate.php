#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Script d'exécution des migrations SQLite.
 *
 * Usage:
 *   php bin/migrate.php           # Exécute toutes les migrations
 *   php bin/migrate.php --seed    # Exécute migrations + seeds
 *   php bin/migrate.php --fresh   # Supprime et recrée la base
 *   php bin/migrate.php --status  # Affiche le statut des migrations
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';

// Couleurs pour la console
const COLOR_GREEN = "\033[32m";
const COLOR_RED = "\033[31m";
const COLOR_YELLOW = "\033[33m";
const COLOR_CYAN = "\033[36m";
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

function title(string $message): void
{
    echo PHP_EOL . COLOR_CYAN . "► " . $message . COLOR_RESET . PHP_EOL;
}

// Options
$seed = in_array('--seed', $argv) || in_array('-s', $argv);
$fresh = in_array('--fresh', $argv) || in_array('-f', $argv);
$status = in_array('--status', $argv);
$help = in_array('--help', $argv) || in_array('-h', $argv);

if ($help) {
    echo <<<HELP

Gestion des migrations SQLite

Usage:
  php bin/migrate.php [options]

Options:
  --seed, -s     Exécuter les seeds après les migrations
  --fresh, -f    Supprimer la base et la recréer (ATTENTION: perte de données)
  --status       Afficher le statut des migrations
  --help, -h     Afficher cette aide

HELP;
    exit(0);
}

$dbPath = $config['database']['path'];
$migrationsPath = dirname(__DIR__) . '/database/migrations';
$seedsPath = dirname(__DIR__) . '/database/seeds';

echo PHP_EOL;
echo "╔══════════════════════════════════════════╗" . PHP_EOL;
echo "║         Migrations SQLite                ║" . PHP_EOL;
echo "╚══════════════════════════════════════════╝" . PHP_EOL;

// Mode fresh: supprimer la base
if ($fresh) {
    title("Mode FRESH: Suppression de la base de données");
    if (file_exists($dbPath)) {
        unlink($dbPath);
        info("Base de données supprimée: $dbPath");
    }
}

// Créer le dossier data si nécessaire
$dataDir = dirname($dbPath);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    info("Dossier créé: $dataDir");
}

// Connexion à la base de données
try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    info("Connexion à la base: $dbPath");
} catch (PDOException $e) {
    error("Impossible de se connecter à la base: " . $e->getMessage());
    exit(1);
}

// Créer la table des migrations si elle n'existe pas
$pdo->exec('CREATE TABLE IF NOT EXISTS migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL,
    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

// Récupérer les migrations déjà exécutées
$executed = $pdo->query('SELECT name FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

// Mode status: afficher l'état
if ($status) {
    title("Statut des migrations");
    $files = glob($migrationsPath . '/*.sql');
    sort($files);

    foreach ($files as $file) {
        $name = basename($file);
        if (in_array($name, $executed)) {
            info("[Exécutée] $name");
        } else {
            warning("[En attente] $name");
        }
    }
    exit(0);
}

// Exécuter les migrations
title("Exécution des migrations");

$files = glob($migrationsPath . '/*.sql');
sort($files);
$count = 0;

foreach ($files as $file) {
    $name = basename($file);

    if (in_array($name, $executed)) {
        continue; // Déjà exécutée
    }

    echo "  Migration: $name ... ";

    try {
        $sql = file_get_contents($file);
        $pdo->exec($sql);

        // Enregistrer la migration
        $stmt = $pdo->prepare('INSERT INTO migrations (name) VALUES (?)');
        $stmt->execute([$name]);

        echo COLOR_GREEN . "OK" . COLOR_RESET . PHP_EOL;
        $count++;
    } catch (PDOException $e) {
        echo COLOR_RED . "ERREUR" . COLOR_RESET . PHP_EOL;
        error("  " . $e->getMessage());
        exit(1);
    }
}

if ($count === 0) {
    info("Aucune nouvelle migration à exécuter.");
} else {
    info("$count migration(s) exécutée(s).");
}

// Exécuter les seeds si demandé
if ($seed) {
    title("Exécution des seeds");

    $seedFiles = glob($seedsPath . '/*.php');
    sort($seedFiles);

    foreach ($seedFiles as $file) {
        $name = basename($file);
        echo "  Seed: $name ... ";

        try {
            $seeder = require $file;
            if (is_callable($seeder)) {
                $seeder($pdo, $config);
            }
            echo COLOR_GREEN . "OK" . COLOR_RESET . PHP_EOL;
        } catch (Exception $e) {
            echo COLOR_RED . "ERREUR" . COLOR_RESET . PHP_EOL;
            error("  " . $e->getMessage());
        }
    }
}

echo PHP_EOL;
info("Migrations terminées.");
