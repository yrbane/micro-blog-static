<?php

declare(strict_types=1);

/**
 * Seed: Utilisateur admin par défaut.
 *
 * IMPORTANT: Changer le mot de passe après la première connexion!
 */

return function (PDO $pdo, array $config): void {
    // Vérifier si un admin existe déjà
    $stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"');
    if ($stmt->fetchColumn() > 0) {
        return; // Admin existe déjà
    }

    // Mot de passe par défaut: "admin123" (à changer!)
    $passwordHash = password_hash('admin123', PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3,
    ]);

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        'admin',
        'admin@example.com',
        $passwordHash,
        'admin',
        1,
    ]);

    echo "    ⚠️  Utilisateur admin créé (mot de passe: admin123 - À CHANGER!)\n";
};
