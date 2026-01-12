<?php
/**
 * Service de limitation du taux de requêtes.
 */
declare(strict_types=1);

namespace Blog\Service;

use PDO;

/**
 * Gère le rate limiting pour protéger contre les attaques brute-force.
 *
 * Configuration par défaut:
 * - 5 tentatives maximum
 * - Fenêtre de 15 minutes
 * - Blocage de 30 minutes après dépassement
 */
class RateLimitService
{
    private const DEFAULT_MAX_ATTEMPTS = 5;
    private const DEFAULT_WINDOW_MINUTES = 15;
    private const DEFAULT_BLOCK_MINUTES = 30;

    public function __construct(
        private PDO $pdo,
        private int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        private int $windowMinutes = self::DEFAULT_WINDOW_MINUTES,
        private int $blockMinutes = self::DEFAULT_BLOCK_MINUTES,
    ) {}

    /**
     * Vérifie si une action est autorisée pour une IP.
     *
     * @param string $ip Adresse IP
     * @param string $action Type d'action (ex: 'login', 'api')
     * @return bool True si l'action est autorisée
     */
    public function isAllowed(string $ip, string $action = 'login'): bool
    {
        $this->cleanup();

        // Vérifier si l'IP est bloquée
        if ($this->isBlocked($ip, $action)) {
            return false;
        }

        // Compter les tentatives récentes
        $attempts = $this->getAttempts($ip, $action);

        return $attempts < $this->maxAttempts;
    }

    /**
     * Enregistre une tentative (échec).
     *
     * @param string $ip Adresse IP
     * @param string $action Type d'action
     * @return int Nombre de tentatives restantes
     */
    public function recordAttempt(string $ip, string $action = 'login'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rate_limits (ip_address, action, attempts, last_attempt_at)
             VALUES (?, ?, 1, datetime("now"))
             ON CONFLICT(ip_address, action) DO UPDATE SET
             attempts = attempts + 1,
             last_attempt_at = datetime("now")'
        );

        $stmt->execute([$ip, $action]);

        $attempts = $this->getAttempts($ip, $action);
        $remaining = max(0, $this->maxAttempts - $attempts);

        // Bloquer si le maximum est atteint
        if ($attempts >= $this->maxAttempts) {
            $this->block($ip, $action);
        }

        return $remaining;
    }

    /**
     * Réinitialise les tentatives après un succès.
     *
     * @param string $ip Adresse IP
     * @param string $action Type d'action
     */
    public function reset(string $ip, string $action = 'login'): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM rate_limits WHERE ip_address = ? AND action = ?'
        );
        $stmt->execute([$ip, $action]);
    }

    /**
     * Vérifie si une IP est bloquée.
     */
    public function isBlocked(string $ip, string $action = 'login'): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT blocked_until FROM rate_limits
             WHERE ip_address = ? AND action = ?
             AND blocked_until > datetime("now")'
        );
        $stmt->execute([$ip, $action]);

        return $stmt->fetch() !== false;
    }

    /**
     * Récupère le temps restant de blocage en secondes.
     */
    public function getBlockTimeRemaining(string $ip, string $action = 'login'): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT blocked_until FROM rate_limits
             WHERE ip_address = ? AND action = ?
             AND blocked_until > datetime("now")'
        );
        $stmt->execute([$ip, $action]);

        $row = $stmt->fetch();
        if (!$row) {
            return 0;
        }

        $blockedUntil = new \DateTime($row['blocked_until']);
        $now = new \DateTime();

        return max(0, $blockedUntil->getTimestamp() - $now->getTimestamp());
    }

    /**
     * Récupère le nombre de tentatives récentes.
     */
    public function getAttempts(string $ip, string $action = 'login'): int
    {
        $windowStart = date('Y-m-d H:i:s', strtotime("-{$this->windowMinutes} minutes"));

        $stmt = $this->pdo->prepare(
            'SELECT attempts FROM rate_limits
             WHERE ip_address = ? AND action = ?
             AND last_attempt_at > ?'
        );
        $stmt->execute([$ip, $action, $windowStart]);

        $row = $stmt->fetch();
        return $row ? (int) $row['attempts'] : 0;
    }

    /**
     * Bloque une IP.
     */
    private function block(string $ip, string $action): void
    {
        $blockedUntil = date('Y-m-d H:i:s', strtotime("+{$this->blockMinutes} minutes"));

        $stmt = $this->pdo->prepare(
            'UPDATE rate_limits SET blocked_until = ? WHERE ip_address = ? AND action = ?'
        );
        $stmt->execute([$blockedUntil, $ip, $action]);

        // Log du blocage
        $this->logBlock($ip, $action);
    }

    /**
     * Enregistre un blocage dans les logs de sécurité.
     */
    private function logBlock(string $ip, string $action): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO security_logs (event_type, details, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, datetime("now"))'
        );

        $stmt->execute([
            'rate_limit_block',
            json_encode([
                'action' => $action,
                'max_attempts' => $this->maxAttempts,
                'block_duration' => $this->blockMinutes,
            ]),
            $ip,
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    }

    /**
     * Nettoie les anciennes entrées.
     */
    private function cleanup(): void
    {
        // Supprimer les entrées expirées (hors de la fenêtre et non bloquées)
        $windowStart = date('Y-m-d H:i:s', strtotime("-{$this->windowMinutes} minutes"));

        $stmt = $this->pdo->prepare(
            'DELETE FROM rate_limits
             WHERE last_attempt_at < ?
             AND (blocked_until IS NULL OR blocked_until < datetime("now"))'
        );
        $stmt->execute([$windowStart]);
    }
}
