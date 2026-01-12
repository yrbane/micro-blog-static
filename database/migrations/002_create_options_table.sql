-- Migration: 002_create_options_table
-- Description: Création de la table des options du site
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS options (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT UNIQUE NOT NULL,
    value TEXT,
    type TEXT DEFAULT 'string',
    group_name TEXT DEFAULT 'general',
    label TEXT,
    description TEXT,
    sort_order INTEGER DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Index pour la recherche par groupe
CREATE INDEX IF NOT EXISTS idx_options_group ON options(group_name);
