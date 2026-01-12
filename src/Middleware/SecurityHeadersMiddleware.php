<?php

declare(strict_types=1);

namespace Blog\Middleware;

use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;
use Lunar\Service\Core\Middleware\MiddlewareInterface;

/**
 * Middleware pour ajouter les headers de sécurité HTTP.
 *
 * Ajoute les headers recommandés pour la sécurité :
 * - Content-Security-Policy
 * - X-Frame-Options
 * - X-Content-Type-Options
 * - Referrer-Policy
 * - Permissions-Policy
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    /**
     * Traite la requête et ajoute les headers de sécurité à la réponse.
     */
    public function process(Request $request, callable $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Content-Security-Policy
        // Politique stricte mais permissive pour l'admin (inline styles/scripts pour l'éditeur)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",  // unsafe-inline pour l'éditeur markdown
            "style-src 'self' 'unsafe-inline'",   // unsafe-inline pour les styles dynamiques
            "img-src 'self' data: https:",        // data: pour les previews base64
            "font-src 'self'",
            "connect-src 'self'",                 // Pour les requêtes AJAX
            "frame-ancestors 'none'",             // Pas d'iframe
            "form-action 'self'",
            "base-uri 'self'",
        ]);
        $response = $response->withHeader('Content-Security-Policy', $csp);

        // Empêcher le clickjacking
        $response = $response->withHeader('X-Frame-Options', 'DENY');

        // Empêcher le MIME sniffing
        $response = $response->withHeader('X-Content-Type-Options', 'nosniff');

        // Contrôler les informations envoyées via Referer
        $response = $response->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Désactiver certaines fonctionnalités du navigateur
        $permissions = implode(', ', [
            'accelerometer=()',
            'camera=()',
            'geolocation=()',
            'gyroscope=()',
            'magnetometer=()',
            'microphone=()',
            'payment=()',
            'usb=()',
        ]);
        $response = $response->withHeader('Permissions-Policy', $permissions);

        // Protection XSS (obsolète mais encore supporté par certains navigateurs)
        $response = $response->withHeader('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
