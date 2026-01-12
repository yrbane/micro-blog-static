-- Migration: 004_create_tags_table
-- Description: Création de la table des tags
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS tags (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Index pour la recherche par slug
CREATE INDEX IF NOT EXISTS idx_tags_slug ON tags(slug);
