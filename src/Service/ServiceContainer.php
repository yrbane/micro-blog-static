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
    private static ?CategoryService $categoryService = null;
    private static ?TagService $tagService = null;
    private static ?MediaService $mediaService = null;
    private static ?PostService $postService = null;
    private static ?StaticGenerator $staticGenerator = null;

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
     * Récupère le service des catégories.
     */
    public static function getCategoryService(): CategoryService
    {
        if (self::$categoryService === null) {
            self::$categoryService = new CategoryService(self::getPdo());
        }

        return self::$categoryService;
    }

    /**
     * Récupère le service des tags.
     */
    public static function getTagService(): TagService
    {
        if (self::$tagService === null) {
            self::$tagService = new TagService(self::getPdo());
        }

        return self::$tagService;
    }

    /**
     * Récupère le service des médias.
     */
    public static function getMediaService(): MediaService
    {
        if (self::$mediaService === null) {
            self::$mediaService = new MediaService(self::getPdo(), self::getProjectRoot());
        }

        return self::$mediaService;
    }

    /**
     * Récupère le service des articles.
     */
    public static function getPostService(): PostService
    {
        if (self::$postService === null) {
            self::$postService = new PostService(self::getPdo());
        }

        return self::$postService;
    }

    /**
     * Récupère le générateur statique.
     */
    public static function getStaticGenerator(): StaticGenerator
    {
        if (self::$staticGenerator === null) {
            self::$staticGenerator = new StaticGenerator(
                self::getPdo(),
                self::getPostService(),
                self::getCategoryService(),
                self::getTagService(),
                self::getOptionService(),
                self::getProjectRoot()
            );
        }

        return self::$staticGenerator;
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
