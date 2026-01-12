-- Migration: 003_create_categories_table
-- Description: Création de la table des catégories (arbre hiérarchique)
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    description TEXT,
    parent_id INTEGER,
    path TEXT NOT NULL DEFAULT '',
    depth INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Index pour la navigation hiérarchique
CREATE INDEX IF NOT EXISTS idx_categories_parent ON categories(parent_id);
CREATE INDEX IF NOT EXISTS idx_categories_path ON categories(path);
CREATE INDEX IF NOT EXISTS idx_categories_slug ON categories(slug);
