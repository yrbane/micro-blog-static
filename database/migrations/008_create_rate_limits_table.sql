-- Migration: 008_create_rate_limits_table
-- Description: Création de la table pour le rate limiting
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS rate_limits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_address TEXT NOT NULL,
    action TEXT NOT NULL,
    attempts INTEGER DEFAULT 1,
    first_attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    blocked_until DATETIME
);

-- Index pour la recherche par IP et action
CREATE UNIQUE INDEX IF NOT EXISTS idx_rate_limits_ip_action ON rate_limits(ip_address, action);
CREATE INDEX IF NOT EXISTS idx_rate_limits_blocked ON rate_limits(blocked_until);
