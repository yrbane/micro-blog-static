-- Migration: Ajouter le rôle aux utilisateurs
-- Rôles: ADMIN (tous droits), REDACTOR (articles), USER (pas d'accès admin)

ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT 'USER' CHECK(role IN ('ADMIN', 'REDACTOR', 'USER'));

-- Index pour les recherches par rôle
CREATE INDEX idx_users_role ON users(role);

-- Mettre à jour l'utilisateur admin existant
UPDATE users SET role = 'ADMIN' WHERE email = 'admin@blog.local';
