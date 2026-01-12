-- Migration: 009_create_security_logs_table
-- Description: Création de la table des logs de sécurité
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS security_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    event_type TEXT NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    user_id INTEGER,
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Index pour les requêtes d'audit
CREATE INDEX IF NOT EXISTS idx_security_logs_type ON security_logs(event_type);
CREATE INDEX IF NOT EXISTS idx_security_logs_user ON security_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_security_logs_date ON security_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_security_logs_ip ON security_logs(ip_address);
