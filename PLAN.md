# Plan de Développement - Micro Blog Statique

## Vue d'ensemble

Système de blog avec admin PHP générant des pages HTML statiques.
Stack : **PHP 8.3 + SQLite + Zero dépendances externes**

---

## Architecture Technique

```
blog/
├── admin/                 # Application admin (PHP)
│   ├── src/
│   │   ├── Core/          # Kernel, Router, Security
│   │   ├── Models/        # Entités (Post, Category, Tag)
│   │   ├── Services/      # Logique métier
│   │   ├── Controllers/   # Contrôleurs admin
│   │   ├── Views/         # Templates admin
│   │   └── Markdown/      # Parser + Sanitizer
│   ├── public/            # Point d'entrée admin
│   └── tests/             # Tests unitaires + e2e
├── public/                # Site statique généré
│   ├── index.html
│   ├── post/
│   ├── category/
│   ├── tag/
│   ├── assets/
│   ├── sitemap.xml
│   └── feed.xml
├── templates/             # Templates génération statique
├── data/
│   └── blog.sqlite        # Base de données
└── config/
    └── config.php         # Configuration
```

---

## Modèle de Données

### Tables SQLite

```sql
-- Utilisateurs admin
users (
    id INTEGER PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,      -- Argon2id
    created_at DATETIME,
    last_login DATETIME
)

-- Posts
posts (
    id INTEGER PRIMARY KEY,
    slug TEXT UNIQUE NOT NULL,        -- Immuable après création
    title TEXT NOT NULL,
    content_md TEXT NOT NULL,         -- Markdown source
    content_html TEXT NOT NULL,       -- HTML généré (cache)
    excerpt TEXT,
    status TEXT DEFAULT 'draft',      -- draft|published
    category_id INTEGER,
    seo_title TEXT,
    seo_description TEXT,
    is_featured BOOLEAN DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    published_at DATETIME,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)

-- Catégories (arbre)
categories (
    id INTEGER PRIMARY KEY,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    parent_id INTEGER,
    path TEXT NOT NULL,               -- ex: "tech/web/php"
    depth INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
)

-- Tags
tags (
    id INTEGER PRIMARY KEY,
    slug TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL
)

-- Relation Posts <-> Tags
post_tags (
    post_id INTEGER,
    tag_id INTEGER,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
)

-- Sessions admin
sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    data TEXT,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
)

-- Logs sécurité
security_logs (
    id INTEGER PRIMARY KEY,
    event_type TEXT,
    ip_address TEXT,
    user_agent TEXT,
    details TEXT,
    created_at DATETIME
)
```

---

## Stratégie de Génération

### Génération Incrémentale

1. **Détection des changements** : timestamp `updated_at` vs dernière génération
2. **Graphe de dépendances** :
   - Post modifié → régénère : post, index, catégorie parent, tags associés
   - Catégorie modifiée → régénère : catégorie, sous-catégories, posts liés
   - Tag modifié → régénère : tag, posts liés
3. **File de génération** : évite les doublons, traite par priorité
4. **Verrou fichier** : `flock()` pour éviter générations concurrentes

### Templates (sans lib)

```php
// Moteur simple avec extraction de variables
function render(string $template, array $data): string {
    extract($data, EXTR_SKIP);
    ob_start();
    include $template;
    return ob_get_clean();
}
```

---

## Sécurité

### Checklist

- [ ] Mots de passe : Argon2id avec paramètres forts
- [ ] Sessions : tokens cryptographiquement sûrs, HttpOnly, Secure, SameSite=Strict
- [ ] CSRF : token par formulaire, validé côté serveur
- [ ] Rate limiting : 5 tentatives/15min par IP
- [ ] Markdown : sanitisation whitelist stricte (pas de HTML brut)
- [ ] Chemins fichiers : validation anti path-traversal
- [ ] Headers : CSP, X-Frame-Options, X-Content-Type-Options
- [ ] Logs : tentatives de connexion, actions admin critiques

---

## Performance

### Budgets

- HTML page : < 50 KB
- CSS total : < 15 KB
- JS total : < 10 KB
- Time to First Byte : < 100ms (statique)
- Largest Contentful Paint : < 1.5s

### Optimisations

- Minification HTML/CSS/JS à la génération
- Lazy loading images natives (`loading="lazy"`)
- Prefetch liens navigation
- Cache headers agressifs (immutable pour assets versionnés)
