-- Migration: 010_create_generation_log_table
-- Description: Création de la table de tracking des générations statiques
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS generation_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    entity_type TEXT NOT NULL,
    entity_id INTEGER,
    file_path TEXT NOT NULL,
    checksum TEXT,
    generated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Index pour la génération incrémentale
CREATE INDEX IF NOT EXISTS idx_generation_entity ON generation_log(entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_generation_path ON generation_log(file_path);
CREATE INDEX IF NOT EXISTS idx_generation_date ON generation_log(generated_at);
