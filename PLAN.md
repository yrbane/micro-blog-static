# Plan de Développement - Micro Blog Statique

## Vue d'ensemble

Système de blog avec admin PHP générant des pages HTML statiques.

### Stack technique
- **Framework** : [lunar-quanta](file:///home/seb/Dev/lunar-quanta) (PHP 8.3+)
- **Templates** : [lunar-template](file:///home/seb/Dev/lunar-template)
- **Base de données** : SQLite via PDO
- **Dépendances externes** : Aucune (frameworks maison)

---

## Fonctionnalités fournies par Lunar Quanta

Le framework fournit déjà :

### Core (prêt à l'emploi)
| Composant | Service | Statut |
|-----------|---------|--------|
| Routing | `Router` avec attributs `#[Route]` | ✅ Inclus |
| DI Container | `Container` avec auto-wiring | ✅ Inclus |
| HTTP | `Request`, `Response` | ✅ Inclus |
| Sessions | `SessionMiddleware` | ✅ Inclus |
| Auth | `Authenticator`, `PasswordHasher` (bcrypt) | ✅ Inclus |
| CSRF | `CsrfMiddleware`, `CsrfTokenManager` | ✅ Inclus |
| Middlewares | `MiddlewareStack`, `AuthMiddleware`, `GuestMiddleware` | ✅ Inclus |

### Blog (prêt à l'emploi)
| Composant | Service | Statut |
|-----------|---------|--------|
| Posts | `PostService` | ✅ Inclus |
| Tags | `TagService` | ✅ Inclus |
| Catégories | `CategoryService` | ✅ Inclus |
| Slugs | `SlugGenerator` | ✅ Inclus |
| Markdown | `MarkdownParser` | ✅ Inclus |
| Sanitizer | `HtmlSanitizer` | ✅ Inclus |

### Génération statique (prêt à l'emploi)
| Composant | Service | Statut |
|-----------|---------|--------|
| Générateur | `StaticGenerator` | ✅ Inclus |
| RSS | `RssGenerator` | ✅ Inclus |
| Sitemap | `SitemapGenerator` | ✅ Inclus |

### Sécurité (prêt à l'emploi)
| Composant | Service | Statut |
|-----------|---------|--------|
| Chiffrement | `EncryptionService` (AES-256) | ✅ Inclus |
| OAuth | `GoogleProvider`, `GitHubProvider` | ✅ Inclus |
| 2FA | Support TOTP | ✅ Inclus |

---

## Fonctionnalités fournies par Lunar Template

| Fonctionnalité | Syntaxe | Statut |
|----------------|---------|--------|
| Variables | `[[ variable ]]` | ✅ Inclus |
| Conditions | `[% if %] [% endif %]` | ✅ Inclus |
| Boucles | `[% for item in items %]` | ✅ Inclus |
| Héritage | `[% extends %]`, `[% block %]` | ✅ Inclus |
| Includes | `[% include 'partial.tpl' %]` | ✅ Inclus |
| Filtres | `[[ text \| upper \| truncate(50) ]]` | ✅ 50+ filtres |
| Macros | `##csrf()##`, `##url()##` | ✅ 40+ macros |
| Échappement XSS | Automatique | ✅ Inclus |
| Raw output | `[[! content !]]` | ✅ Inclus |

---

## Ce qui reste à développer

### 1. Configuration & Intégration
- [ ] Configurer le projet pour utiliser lunar-quanta
- [ ] Intégrer lunar-template comme moteur de rendu
- [ ] Configurer SQLite et créer les migrations spécifiques
- [ ] Adapter les entités existantes (Post, Category, Tag) si besoin

### 2. Fonctionnalités spécifiques au projet
- [ ] **Slug immuable** : Verrouiller le slug après création du post
- [ ] **Catégories en arbre** : Adapter/étendre CategoryService pour hiérarchie parent/enfants avec path
- [ ] **Rate limiting** : Ajouter protection brute-force sur login
- [ ] **Headers de sécurité** : Middleware CSP, X-Frame-Options, etc.

### 3. Interface Admin
- [ ] Layout admin (CSS moderne, responsive)
- [ ] Dashboard avec statistiques
- [ ] CRUD Posts (formulaires, liste, filtres)
- [ ] CRUD Catégories (tree view, drag & drop)
- [ ] CRUD Tags (liste, édition inline)
- [ ] **Éditeur Markdown avec preview live**
- [ ] **Autocomplétion liens internes** (`[[` trigger → menu dropdown)

### 4. Templates du site public
- [ ] Template de base (`base.html.tpl`)
- [ ] Page post
- [ ] Page index avec pagination
- [ ] Pages catégories (hiérarchiques)
- [ ] Pages tags
- [ ] Composants (header, footer, cards, pagination)

### 5. Frontend public (CSS/JS)
- [ ] Architecture CSS (layers, custom properties, container queries)
- [ ] Design responsive et accessible
- [ ] JavaScript minimal (prefetch, View Transitions)
- [ ] Recherche côté client (index JSON)

### 6. Génération statique (adaptation)
- [ ] Adapter `StaticGenerator` pour notre structure d'URLs
- [ ] Génération incrémentale (ne régénérer que ce qui a changé)
- [ ] Index JSON pour recherche front

### 7. Optimisation & finalisation
- [ ] Minification HTML/CSS/JS
- [ ] Audit sécurité
- [ ] Documentation utilisateur
- [ ] Tests

---

## Architecture du projet

```
blog/
├── admin/
│   ├── public/
│   │   └── index.php          # Point d'entrée admin
│   └── src/
│       └── Controller/
│           ├── DashboardController.php
│           ├── PostController.php
│           ├── CategoryController.php
│           └── TagController.php
├── config/
│   ├── config.php             # Configuration générale
│   ├── routes.php             # Routes (si pas attributs)
│   └── services.php           # Services personnalisés
├── data/
│   └── blog.sqlite            # Base de données
├── public/                    # Site statique généré
│   ├── index.html
│   ├── post/{slug}/index.html
│   ├── category/{path}/index.html
│   ├── tag/{slug}/index.html
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── sitemap.xml
│   ├── feed.xml
│   └── search-index.json
├── src/
│   ├── Entity/                # Entités étendues si besoin
│   ├── Service/               # Services spécifiques
│   │   ├── ImmutableSlugService.php
│   │   ├── CategoryTreeService.php
│   │   ├── IncrementalGenerator.php
│   │   └── LinkAutocompleteService.php
│   └── Middleware/
│       ├── RateLimitMiddleware.php
│       └── SecurityHeadersMiddleware.php
├── templates/
│   ├── base.html.tpl          # Layout principal (TOUTES les pages héritent)
│   ├── mail.html.tpl          # Layout emails (tous les mails héritent)
│   ├── admin/                 # Templates admin
│   │   ├── layout.html.tpl    # extends base.html.tpl (sidebar, nav admin)
│   │   ├── dashboard.html.tpl
│   │   ├── post/
│   │   │   ├── list.html.tpl
│   │   │   └── form.html.tpl
│   │   ├── category/
│   │   └── tag/
│   ├── public/                # Templates site statique
│   │   ├── layout.html.tpl    # extends base.html.tpl (header, footer public)
│   │   ├── index.html.tpl
│   │   ├── post.html.tpl
│   │   ├── category.html.tpl
│   │   ├── tag.html.tpl
│   │   └── components/
│   └── mail/                  # Templates emails
│       ├── welcome.html.tpl   # extends mail.html.tpl
│       ├── reset-password.html.tpl
│       └── notification.html.tpl
└── tests/
```

---

## Architecture des templates (héritage unifié)

Tous les templates utilisent le système d'héritage de lunar-template.

### Hiérarchie des pages

```
base.html.tpl                    ← Layout racine (HTML, head, body, scripts)
├── admin/layout.html.tpl        ← Layout admin (sidebar, nav, zone contenu)
│   ├── admin/dashboard.html.tpl
│   ├── admin/post/list.html.tpl
│   ├── admin/post/form.html.tpl
│   └── ...
└── public/layout.html.tpl       ← Layout public (header, footer, main)
    ├── public/index.html.tpl
    ├── public/post.html.tpl
    ├── public/category.html.tpl
    └── ...
```

### Hiérarchie des emails

```
mail.html.tpl                    ← Layout email (DOCTYPE, styles inline, structure)
├── mail/welcome.html.tpl
├── mail/reset-password.html.tpl
└── mail/notification.html.tpl
```

### Template base.html.tpl (exemple)

```html
<!DOCTYPE html>
<html lang="[[ lang | default('fr') ]]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[% block title %]Blog[% endblock %]</title>
    <meta name="description" content="[% block description %][% endblock %]">
    [% block meta %][% endblock %]
    [% block styles %]
    <link rel="stylesheet" href="/assets/css/main.css">
    [% endblock %]
</head>
<body class="[% block body_class %][% endblock %]">
    [% block body %]
    [% endblock %]

    [% block scripts %]
    <script type="module" src="/assets/js/main.js"></script>
    [% endblock %]
</body>
</html>
```

### Template admin/layout.html.tpl (exemple)

```html
[% extends 'base.html.tpl' %]

[% block styles %]
[% parent %]
<link rel="stylesheet" href="/assets/css/admin.css">
[% endblock %]

[% block body_class %]admin[% endblock %]

[% block body %]
<div class="admin-layout">
    <aside class="admin-sidebar">
        [% include 'admin/components/nav.html.tpl' %]
    </aside>
    <main class="admin-main">
        <header class="admin-header">
            [% block header %][% endblock %]
        </header>
        <div class="admin-content">
            [% block content %][% endblock %]
        </div>
    </main>
</div>
[% endblock %]
```

### Template public/layout.html.tpl (exemple)

```html
[% extends 'base.html.tpl' %]

[% block body_class %]public[% endblock %]

[% block body %]
<header class="site-header">
    [% include 'public/components/header.html.tpl' %]
</header>

<main class="site-main">
    [% block content %][% endblock %]
</main>

<footer class="site-footer">
    [% include 'public/components/footer.html.tpl' %]
</footer>
[% endblock %]
```

### Template mail.html.tpl (exemple)

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[% block subject %]Notification[% endblock %]</title>
    <style>
        /* Styles inline pour compatibilité email */
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a1a2e; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #ffffff; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 12px 24px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            [% block header %]
            <h1>[[ siteName ]]</h1>
            [% endblock %]
        </div>
        <div class="content">
            [% block content %][% endblock %]
        </div>
        <div class="footer">
            [% block footer %]
            <p>&copy; [[ year ]] [[ siteName ]]</p>
            [% endblock %]
        </div>
    </div>
</body>
</html>
```

---

## Schéma URLs

| Page | URL | Fichier généré |
|------|-----|----------------|
| Accueil | `/` | `public/index.html` |
| Accueil page N | `/page/2/` | `public/page/2/index.html` |
| Post | `/post/{slug}/` | `public/post/{slug}/index.html` |
| Catégorie | `/category/{path}/` | `public/category/{path}/index.html` |
| Tag | `/tag/{slug}/` | `public/tag/{slug}/index.html` |
| Sitemap | `/sitemap.xml` | `public/sitemap.xml` |
| RSS | `/feed.xml` | `public/feed.xml` |
| Recherche JSON | `/search-index.json` | `public/search-index.json` |

---

## Modèle de données (adaptations)

Les entités de base existent dans lunar-quanta. Adaptations nécessaires :

### Post (extension)
```php
// Ajout du verrouillage slug
class Post extends \Lunar\Entity\Post
{
    private bool $slugLocked = false;

    public function setSlug(string $slug): void
    {
        if ($this->slugLocked && $this->slug !== null) {
            throw new SlugImmutableException();
        }
        $this->slug = $slug;
    }

    public function lockSlug(): void
    {
        $this->slugLocked = true;
    }
}
```

### Category (extension pour arbre)
```php
// Ajout gestion hiérarchique
class Category extends \Lunar\Entity\Category
{
    private ?int $parentId = null;
    private string $path = '';      // ex: "tech/web/php"
    private int $depth = 0;

    // Méthodes pour navigation arbre
    public function getAncestors(): array { }
    public function getDescendants(): array { }
    public function moveTo(?Category $newParent): void { }
}
```

### Table SQLite supplémentaire
```sql
-- Rate limiting
rate_limits (
    id INTEGER PRIMARY KEY,
    ip_address TEXT NOT NULL,
    action TEXT NOT NULL,          -- 'login', 'api', etc.
    attempts INTEGER DEFAULT 1,
    first_attempt_at DATETIME,
    blocked_until DATETIME
)

-- Logs sécurité
security_logs (
    id INTEGER PRIMARY KEY,
    event_type TEXT,               -- 'login_success', 'login_failed', 'blocked'
    ip_address TEXT,
    user_agent TEXT,
    user_id INTEGER,
    details TEXT,
    created_at DATETIME
)

-- Tracking génération (pour incrémental)
generation_log (
    id INTEGER PRIMARY KEY,
    entity_type TEXT,              -- 'post', 'category', 'tag'
    entity_id INTEGER,
    generated_at DATETIME,
    file_path TEXT
)
```

---

## Autocomplétion des liens internes

### Comportement
1. L'utilisateur tape `[[` dans l'éditeur Markdown
2. Un menu dropdown apparaît
3. Recherche en temps réel (posts, catégories, tags)
4. Navigation clavier (↑↓ Enter Esc)
5. Sélection insère `[[slug]]` ou `[Titre](/post/slug/)`

### Implémentation
```php
// API endpoint
#[Route('/admin/api/autocomplete', methods: ['GET'])]
public function autocomplete(Request $request): Response
{
    $query = $request->get('q', '');
    $results = $this->linkAutocompleteService->search($query);
    return new JsonResponse($results);
}
```

```javascript
// JavaScript côté éditeur
editor.addEventListener('input', (e) => {
    const text = e.target.value;
    const cursor = e.target.selectionStart;

    // Détecter [[
    if (text.substring(cursor - 2, cursor) === '[[') {
        showAutocompleteMenu(cursor);
    }
});
```

---

## Estimation effort restant

| Domaine | Effort | Commentaire |
|---------|--------|-------------|
| Configuration/intégration | Faible | Frameworks prêts |
| Slug immuable + arbre catégories | Faible | Extensions simples |
| Rate limiting + headers sécurité | Faible | Middlewares simples |
| Interface Admin | **Moyen** | UI à créer from scratch |
| Autocomplétion liens | Moyen | Fonctionnalité spécifique |
| Templates public | Moyen | Design à créer |
| CSS/JS public | Moyen | Assets à créer |
| Génération incrémentale | Faible | Adapter l'existant |
| Tests | Moyen | Couverture à assurer |

**Total estimé** : ~60% du travail initial économisé grâce aux frameworks.
