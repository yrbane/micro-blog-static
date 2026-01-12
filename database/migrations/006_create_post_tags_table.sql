-- Migration: 006_create_post_tags_table
-- Description: Création de la table pivot posts <-> tags
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS post_tags (
    post_id INTEGER NOT NULL,
    tag_id INTEGER NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Index pour les requêtes inverses
CREATE INDEX IF NOT EXISTS idx_post_tags_tag ON post_tags(tag_id);
