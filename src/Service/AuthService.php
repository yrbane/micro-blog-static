<?php
/**
 * Service d'authentification.
 */
declare(strict_types=1);

namespace Blog\Service;

use Blog\Entity\User;
use PDO;

/**
 * Gère l'authentification et les sessions utilisateur.
 */
class AuthService
{
    private const SESSION_KEY = 'auth_user_id';
    private const SESSION_ROLE = 'auth_user_role';

    private ?User $currentUser = null;

    public function __construct(
        private PDO $pdo,
    ) {
        $this->startSession();
    }

    /**
     * Démarre la session PHP si nécessaire.
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Tente de connecter un utilisateur.
     *
     * @return User|null L'utilisateur connecté ou null si échec
     */
    public function login(string $email, string $password): ?User
    {
        $user = $this->findUserByEmail($email);

        if ($user === null) {
            $this->logSecurityEvent('login_failed', ['email' => $email, 'reason' => 'user_not_found']);
            return null;
        }

        if (!$user->isActive) {
            $this->logSecurityEvent('login_failed', ['email' => $email, 'reason' => 'user_inactive']);
            return null;
        }

        if (!$user->verifyPassword($password)) {
            $this->logSecurityEvent('login_failed', ['email' => $email, 'reason' => 'invalid_password']);
            return null;
        }

        // Connexion réussie
        $this->setCurrentUser($user);
        $this->updateLastLogin($user);
        $this->logSecurityEvent('login_success', ['user_id' => $user->id, 'email' => $email]);

        return $user;
    }

    /**
     * Déconnecte l'utilisateur courant.
     */
    public function logout(): void
    {
        if ($this->currentUser !== null) {
            $this->logSecurityEvent('logout', ['user_id' => $this->currentUser->id]);
        }

        $this->currentUser = null;
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_ROLE]);

        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);
    }

    /**
     * Récupère l'utilisateur actuellement connecté.
     */
    public function getCurrentUser(): ?User
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        $userId = $_SESSION[self::SESSION_KEY] ?? null;
        if ($userId === null) {
            return null;
        }

        $this->currentUser = $this->findUserById((int) $userId);

        // Si l'utilisateur n'existe plus ou est inactif, déconnecter
        if ($this->currentUser === null || !$this->currentUser->isActive) {
            $this->logout();
            return null;
        }

        return $this->currentUser;
    }

    /**
     * Vérifie si un utilisateur est connecté.
     */
    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Vérifie si l'utilisateur courant a une permission.
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->hasPermission($permission);
    }

    /**
     * Vérifie si l'utilisateur courant peut accéder à l'admin.
     */
    public function canAccessAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->canAccessAdmin();
    }

    /**
     * Vérifie si l'utilisateur courant est admin.
     */
    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->isAdmin();
    }

    /**
     * Définit l'utilisateur courant en session.
     */
    private function setCurrentUser(User $user): void
    {
        $this->currentUser = $user;
        $_SESSION[self::SESSION_KEY] = $user->id;
        $_SESSION[self::SESSION_ROLE] = $user->role;

        // Régénérer l'ID de session pour la sécurité
        session_regenerate_id(true);
    }

    /**
     * Trouve un utilisateur par email.
     */
    public function findUserByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $data = $stmt->fetch();

        return $data ? User::fromArray($data) : null;
    }

    /**
     * Trouve un utilisateur par ID.
     */
    public function findUserById(int $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? User::fromArray($data) : null;
    }

    /**
     * Met à jour la date de dernière connexion.
     */
    private function updateLastLogin(User $user): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = ? WHERE id = ?');
        $stmt->execute([date('Y-m-d H:i:s'), $user->id]);
    }

    /**
     * Enregistre un événement de sécurité.
     */
    private function logSecurityEvent(string $event, array $data): void
    {
        $userId = $data['user_id'] ?? null;
        unset($data['user_id']);

        $stmt = $this->pdo->prepare(
            'INSERT INTO security_logs (event_type, details, ip_address, user_agent, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $event,
            json_encode($data),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $userId,
            date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Crée un nouvel utilisateur.
     */
    public function createUser(string $username, string $email, string $password, string $role = User::ROLE_USER): User
    {
        $user = new User(
            username: $username,
            email: $email,
            role: $role,
        );
        $user->setPassword($password);

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $user->username,
            $user->email,
            $user->password,
            $user->role,
            1,
            $user->createdAt->format('Y-m-d H:i:s'),
        ]);

        $user->id = (int) $this->pdo->lastInsertId();

        return $user;
    }

    /**
     * Met à jour un utilisateur.
     */
    public function updateUser(User $user): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET username = ?, email = ?, role = ?, is_active = ?, updated_at = ? WHERE id = ?'
        );

        $stmt->execute([
            $user->username,
            $user->email,
            $user->role,
            $user->isActive ? 1 : 0,
            date('Y-m-d H:i:s'),
            $user->id,
        ]);
    }

    /**
     * Change le mot de passe d'un utilisateur.
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->setPassword($newPassword);

        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$user->password, date('Y-m-d H:i:s'), $user->id]);
    }

    /**
     * Liste tous les utilisateurs.
     *
     * @return User[]
     */
    public function listUsers(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = [];

        while ($data = $stmt->fetch()) {
            $users[] = User::fromArray($data);
        }

        return $users;
    }

    /**
     * Supprime un utilisateur.
     */
    public function deleteUser(int $id): bool
    {
        // Ne pas permettre de supprimer son propre compte
        if ($this->getCurrentUser()?->id === $id) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
