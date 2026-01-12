<?php
/**
 * Entité User - Gestion des utilisateurs et rôles.
 */
declare(strict_types=1);

namespace Blog\Entity;

/**
 * Représente un utilisateur du système.
 *
 * Rôles disponibles:
 * - ADMIN: Accès complet à l'administration
 * - REDACTOR: Création et modification d'articles uniquement
 * - USER: Pas d'accès à l'administration
 */
class User
{
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_REDACTOR = 'REDACTOR';
    public const ROLE_USER = 'USER';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrateur',
        self::ROLE_REDACTOR => 'Rédacteur',
        self::ROLE_USER => 'Utilisateur',
    ];

    /**
     * Permissions par rôle.
     * Chaque rôle hérite des permissions des rôles inférieurs.
     */
    private const PERMISSIONS = [
        self::ROLE_USER => [],
        self::ROLE_REDACTOR => [
            'post.create',
            'post.edit',
            'post.view',
            'tag.view',
            'category.view',
            'media.upload',
            'media.view',
        ],
        self::ROLE_ADMIN => [
            'post.create',
            'post.edit',
            'post.delete',
            'post.publish',
            'post.view',
            'category.create',
            'category.edit',
            'category.delete',
            'category.view',
            'tag.create',
            'tag.edit',
            'tag.delete',
            'tag.view',
            'user.create',
            'user.edit',
            'user.delete',
            'user.view',
            'option.edit',
            'option.view',
            'media.upload',
            'media.delete',
            'media.view',
            'generate.run',
            'admin.access',
        ],
    ];

    public function __construct(
        public ?int $id = null,
        public string $username = '',
        public string $email = '',
        public string $password = '',
        public string $role = self::ROLE_USER,
        public bool $isActive = true,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?\DateTimeImmutable $lastLoginAt = null,
    ) {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    /**
     * Crée un User depuis un tableau (résultat DB).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            username: (string) ($data['username'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            password: (string) ($data['password_hash'] ?? $data['password'] ?? ''),
            role: strtoupper((string) ($data['role'] ?? self::ROLE_USER)),
            isActive: (bool) ($data['is_active'] ?? true),
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null,
            lastLoginAt: isset($data['last_login_at']) ? new \DateTimeImmutable($data['last_login_at']) : null,
        );
    }

    /**
     * Convertit en tableau pour insertion/mise à jour DB.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'role_label' => $this->getRoleLabel(),
            'is_active' => $this->isActive ? 1 : 0,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'last_login_at' => $this->lastLoginAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Vérifie si l'utilisateur est administrateur.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Vérifie si l'utilisateur est rédacteur.
     */
    public function isRedactor(): bool
    {
        return $this->role === self::ROLE_REDACTOR;
    }

    /**
     * Vérifie si l'utilisateur peut accéder à l'admin.
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_REDACTOR], true);
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = self::PERMISSIONS[$this->role] ?? [];
        return in_array($permission, $permissions, true);
    }

    /**
     * Récupère toutes les permissions de l'utilisateur.
     *
     * @return string[]
     */
    public function getPermissions(): array
    {
        return self::PERMISSIONS[$this->role] ?? [];
    }

    /**
     * Récupère le libellé du rôle.
     */
    public function getRoleLabel(): string
    {
        return self::ROLES[$this->role] ?? 'Inconnu';
    }

    /**
     * Définit un nouveau mot de passe (hashé).
     */
    public function setPassword(string $plainPassword): void
    {
        $this->password = password_hash($plainPassword, PASSWORD_ARGON2ID);
    }

    /**
     * Vérifie si le mot de passe correspond.
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

    /**
     * Met à jour la date de dernière connexion.
     */
    public function updateLastLogin(): void
    {
        $this->lastLoginAt = new \DateTimeImmutable();
    }
}
