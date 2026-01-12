<?php
/**
 * Container de services simplifié pour le blog.
 */
declare(strict_types=1);

namespace Blog\Service;

use PDO;

/**
 * Fournit un accès aux services de l'application.
 *
 * Pattern Singleton pour les services partagés.
 */
class ServiceContainer
{
    private static ?PDO $pdo = null;
    private static ?AuthService $authService = null;
    private static ?OptionService $optionService = null;
    private static ?RateLimitService $rateLimitService = null;

    /**
     * Récupère l'instance PDO.
     */
    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = self::getProjectRoot() . '/data/blog.sqlite';
            self::$pdo = new PDO('sqlite:' . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        }

        return self::$pdo;
    }

    /**
     * Récupère le service d'authentification.
     */
    public static function getAuthService(): AuthService
    {
        if (self::$authService === null) {
            self::$authService = new AuthService(self::getPdo());
        }

        return self::$authService;
    }

    /**
     * Récupère le service des options.
     */
    public static function getOptionService(): OptionService
    {
        if (self::$optionService === null) {
            $cachePath = self::getProjectRoot() . '/cache';
            self::$optionService = new OptionService(self::getPdo(), $cachePath);
        }

        return self::$optionService;
    }

    /**
     * Récupère le service de rate limiting.
     */
    public static function getRateLimitService(): RateLimitService
    {
        if (self::$rateLimitService === null) {
            self::$rateLimitService = new RateLimitService(self::getPdo());
        }

        return self::$rateLimitService;
    }

    /**
     * Récupère la racine du projet.
     */
    public static function getProjectRoot(): string
    {
        if (defined('PROJECT_ROOT')) {
            return PROJECT_ROOT;
        }

        // Fallback: cherche composer.json
        $dir = __DIR__;
        while ($dir !== '/') {
            if (file_exists($dir . '/composer.json')) {
                return $dir;
            }
            $dir = dirname($dir);
        }

        return getcwd() ?: '.';
    }
}
