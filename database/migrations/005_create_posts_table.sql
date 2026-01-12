-- Migration: 005_create_posts_table
-- Description: Création de la table des posts (articles)
-- Date: 2024-01-12

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT UNIQUE NOT NULL,
    slug_locked INTEGER DEFAULT 0,
    title TEXT NOT NULL,
    content_md TEXT NOT NULL,
    content_html TEXT,
    excerpt TEXT,
    status TEXT DEFAULT 'draft',
    category_id INTEGER,
    author_id INTEGER,
    seo_title TEXT,
    seo_description TEXT,
    og_image TEXT,
    is_featured INTEGER DEFAULT 0,
    view_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    published_at DATETIME,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Index pour les requêtes courantes
CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);
CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status);
CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category_id);
CREATE INDEX IF NOT EXISTS idx_posts_published ON posts(published_at);
CREATE INDEX IF NOT EXISTS idx_posts_featured ON posts(is_featured);
