<?php

declare(strict_types=1);

/**
 * Configuration principale du blog.
 *
 * Ce fichier charge les variables d'environnement et définit
 * la configuration globale de l'application.
 */

// Charger les variables d'environnement
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

/**
 * Récupère une variable d'environnement avec valeur par défaut.
 */
function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key) ?: $default;

    if ($value === 'true') return true;
    if ($value === 'false') return false;
    if ($value === 'null') return null;
    if (is_numeric($value)) return str_contains($value, '.') ? (float) $value : (int) $value;

    return $value;
}

return [
    /*
    |--------------------------------------------------------------------------
    | Application
    |--------------------------------------------------------------------------
    */
    'app' => [
        'env' => env('APP_ENV', 'prod'),
        'debug' => env('APP_DEBUG', false),
        'key' => env('APP_KEY'),
        'timezone' => 'Europe/Paris',
        'locale' => 'fr',
    ],

    /*
    |--------------------------------------------------------------------------
    | Chemins
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'root' => dirname(__DIR__),
        'data' => dirname(__DIR__) . '/' . ltrim(env('DATA_PATH', './data'), './'),
        'cache' => dirname(__DIR__) . '/' . ltrim(env('CACHE_PATH', './cache'), './'),
        'logs' => dirname(__DIR__) . '/' . ltrim(env('LOGS_PATH', './logs'), './'),
        'templates' => dirname(__DIR__) . '/templates',
        'public' => dirname(__DIR__) . '/public',
        'admin_public' => dirname(__DIR__) . '/admin/public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Base de données
    |--------------------------------------------------------------------------
    */
    'database' => [
        'driver' => 'sqlite',
        'path' => dirname(__DIR__) . '/' . ltrim(env('DB_PATH', './data/blog.sqlite'), './'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates (lunar-template)
    |--------------------------------------------------------------------------
    */
    'template' => [
        'path' => dirname(__DIR__) . '/templates',
        'cache' => dirname(__DIR__) . '/cache/templates',
        'extension' => '.html.tpl',
    ],

    /*
    |--------------------------------------------------------------------------
    | Site (pour génération statique)
    |--------------------------------------------------------------------------
    */
    'site' => [
        'url' => env('SITE_URL', 'http://localhost'),
        'output_path' => dirname(__DIR__) . '/public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'path' => env('ADMIN_PATH', '/admin'),
        'session_lifetime' => 3600, // 1 heure
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité
    |--------------------------------------------------------------------------
    */
    'security' => [
        'rate_limit' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'password' => [
            'algo' => PASSWORD_ARGON2ID,
            'options' => [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3,
            ],
        ],
        'csrf' => [
            'token_length' => 32,
            'lifetime' => 3600,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog
    |--------------------------------------------------------------------------
    */
    'blog' => [
        'posts_per_page' => 10,
        'excerpt_length' => 200,
        'date_format' => 'd/m/Y',
        'datetime_format' => 'd/m/Y H:i',
    ],
];
