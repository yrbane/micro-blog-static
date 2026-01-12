<?php
/**
 * Middleware pour ajouter les headers de sécurité HTTP.
 */
declare(strict_types=1);

namespace Blog\Middleware;

use Lunar\Service\Core\Http\Request;
use Lunar\Service\Core\Http\Response;
use Lunar\Service\Core\Middleware\MiddlewareInterface;

/**
 * Ajoute les headers de sécurité HTTP à toutes les réponses.
 *
 * Headers configurés :
 * - Content-Security-Policy (CSP)
 * - X-Frame-Options
 * - X-Content-Type-Options
 * - X-XSS-Protection
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

        // Récupérer les headers existants
        $headers = $response->getHeaders();

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
        $headers[] = 'Content-Security-Policy: ' . $csp;

        // Empêcher le clickjacking
        $headers[] = 'X-Frame-Options: DENY';

        // Empêcher le MIME sniffing
        $headers[] = 'X-Content-Type-Options: nosniff';

        // Contrôler les informations envoyées via Referer
        $headers[] = 'Referrer-Policy: strict-origin-when-cross-origin';

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
        $headers[] = 'Permissions-Policy: ' . $permissions;

        // Protection XSS (obsolète mais encore supporté par certains navigateurs)
        $headers[] = 'X-XSS-Protection: 1; mode=block';

        // Créer une nouvelle réponse avec les headers de sécurité
        return new Response(
            $response->getBody(),
            $response->getStatusCode(),
            $headers
        );
    }
}
