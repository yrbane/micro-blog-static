# Issues par Milestone

## M1 - Fondations (Core)

### Issue #1 - Structure projet et autoloading
**Labels**: setup, core
```
- Créer l'arborescence de dossiers
- Configurer autoloading PSR-4
- Créer .gitignore
- Créer fichier de configuration
```

### Issue #2 - Kernel applicatif
**Labels**: core
```
- Classe App (bootstrap)
- Chargement configuration
- Gestion environnement (dev/prod)
- Container services basique
```

### Issue #3 - Router minimaliste
**Labels**: core
```
- Classe Router (pattern matching)
- Support GET/POST/PUT/DELETE
- Extraction paramètres URL
- Dispatch vers controllers
```

### Issue #4 - Base de données SQLite
**Labels**: core, database
```
- Classe Database (PDO wrapper)
- Système de migrations
- Scripts création tables
- Seeds données de test
```

### Issue #5 - Authentification sécurisée
**Labels**: core, security
```
- Hash Argon2id pour mots de passe
- Classe Session (tokens sécurisés)
- Cookies HttpOnly/Secure/SameSite
- Middleware authentification
```

### Issue #6 - Protection CSRF
**Labels**: security
```
- Génération tokens CSRF
- Validation tokens sur POST
- Helper pour formulaires
- Tests de validation
```

### Issue #7 - Rate limiting
**Labels**: security
```
- Tracking tentatives par IP
- Blocage après N échecs
- Durée de blocage configurable
- Logs des blocages
```

### Issue #8 - Headers de sécurité
**Labels**: security
```
- Middleware SecurityHeaders
- CSP, X-Frame-Options
- X-Content-Type-Options
- Referrer-Policy, Permissions-Policy
```

### Issue #9 - Tests unitaires Core
**Labels**: testing
```
- Tests Router
- Tests Database
- Tests Session
- Tests CSRF
- Tests Rate Limiting
```

---

## M2 - Modèles & CRUD

### Issue #10 - Model Post
**Labels**: model
```
- Entité Post avec propriétés
- Slug immuable (verrouillage après création)
- Statuts draft/published
- Timestamps automatiques
```

### Issue #11 - Model Category (arbre)
**Labels**: model
```
- Entité Category
- Gestion parent/enfants
- Calcul path hiérarchique
- Réorganisation de l'arbre
```

### Issue #12 - Model Tag
**Labels**: model
```
- Entité Tag
- Relation many-to-many avec Post
- Slug unique
```

### Issue #13 - Repository Posts
**Labels**: repository
```
- PostRepository (CRUD)
- Requêtes avec filtres
- Pagination
- Recherche par catégorie/tag
```

### Issue #14 - Repository Categories
**Labels**: repository
```
- CategoryRepository (CRUD)
- Récupération arbre complet
- Déplacement dans l'arbre
- Récupération ancêtres/descendants
```

### Issue #15 - Repository Tags
**Labels**: repository
```
- TagRepository (CRUD)
- Tags par post
- Posts par tag
- Tags les plus utilisés
```

### Issue #16 - Service Slugs
**Labels**: service
```
- Génération slug depuis titre
- Unicité garantie
- Caractères autorisés
- Verrouillage slug post
```

### Issue #17 - Validation données
**Labels**: service
```
- Classe Validator
- Règles de validation
- Messages d'erreur
- Validation formulaires
```

### Issue #18 - Tests unitaires Models
**Labels**: testing
```
- Tests entités
- Tests repositories
- Tests services
- Tests validation
```

---

## M3 - Parser Markdown

### Issue #19 - Parser Markdown base
**Labels**: markdown
```
- Titres (h1-h6)
- Paragraphes
- Gras, italique, code inline
- Listes (ordonnées/non-ordonnées)
- Blocs de code
```

### Issue #20 - Parser liens et images
**Labels**: markdown
```
- Liens [texte](url)
- Images ![alt](src)
- Liens internes [[slug]]
- Résolution des slugs
```

### Issue #21 - Sanitizer HTML
**Labels**: markdown, security
```
- Whitelist tags autorisés
- Whitelist attributs par tag
- Suppression javascript:
- Échappement contenu
```

### Issue #22 - Tests parser + sécurité
**Labels**: testing, security
```
- Tests conversions MD
- Tests payloads XSS
- Tests liens malveillants
- Tests edge cases
```

---

## M4 - Interface Admin

### Issue #23 - Layout admin
**Labels**: admin, ui
```
- Template base admin
- Navigation sidebar
- Header avec user info
- CSS admin (moderne, minimal)
```

### Issue #24 - Dashboard
**Labels**: admin, ui
```
- Stats posts (draft/published)
- Posts récents
- Actions rapides
- État dernière génération
```

### Issue #25 - CRUD Posts UI
**Labels**: admin, ui
```
- Liste posts (filtres, pagination)
- Formulaire création/édition
- Prévisualisation
- Actions (publier, supprimer)
```

### Issue #26 - CRUD Catégories UI
**Labels**: admin, ui
```
- Tree view catégories
- Drag & drop réorganisation
- Formulaire création/édition
- Gestion parent
```

### Issue #27 - CRUD Tags UI
**Labels**: admin, ui
```
- Liste tags avec count posts
- Création rapide
- Édition inline
- Suppression avec confirmation
```

### Issue #28 - Éditeur Markdown
**Labels**: admin, ui, editor
```
- Textarea avec preview live
- Boutons formatage
- Raccourcis clavier
- Split view
```

### Issue #29 - Autocomplétion liens
**Labels**: admin, ui, editor
```
- Déclencheur [[ ou /
- Recherche posts/catégories/tags
- Menu dropdown navigable clavier
- Insertion lien formaté
```

### Issue #30 - Messages et notifications
**Labels**: admin, ui
```
- Flash messages
- Toasts notifications
- Confirmations actions
- Gestion erreurs
```

### Issue #31 - Tests e2e Admin
**Labels**: testing
```
- Tests login/logout
- Tests CRUD posts
- Tests CRUD catégories
- Tests CRUD tags
- Tests éditeur
```

---

## M5 - Moteur de Génération

### Issue #32 - Moteur templates
**Labels**: generator
```
- Fonction render()
- Extraction variables
- Includes/partials
- Échappement automatique
```

### Issue #33 - Générateur pages posts
**Labels**: generator
```
- Template page post
- Métadonnées SEO
- Navigation prev/next
- Posts liés
```

### Issue #34 - Générateur index
**Labels**: generator
```
- Page index principale
- Pagination
- Filtres actifs
- Posts featured
```

### Issue #35 - Générateur catégories
**Labels**: generator
```
- Pages par catégorie
- Breadcrumb hiérarchique
- Sous-catégories
- Pagination posts
```

### Issue #36 - Générateur tags
**Labels**: generator
```
- Pages par tag
- Liste tous les tags
- Pagination posts
- Tag cloud (optionnel)
```

### Issue #37 - Sitemap et RSS
**Labels**: generator
```
- Génération sitemap.xml
- Génération feed.xml (Atom)
- URLs canoniques
- Dates modification
```

### Issue #38 - Index JSON recherche
**Labels**: generator
```
- Index compact posts
- Titre, extrait, URL, tags
- Compression données
```

### Issue #39 - Système verrou
**Labels**: generator
```
- Verrou fichier flock()
- Timeout génération
- Logs génération
- Gestion erreurs
```

### Issue #40 - Génération incrémentale
**Labels**: generator
```
- Tracking dernière génération
- Détection fichiers modifiés
- Graphe dépendances
- Régénération ciblée
```

### Issue #41 - Tests génération
**Labels**: testing
```
- Tests templates
- Tests génération pages
- Tests incrémental
- Tests concurrence
```

---

## M6 - Frontend Public

### Issue #42 - Structure HTML
**Labels**: frontend
```
- HTML5 sémantique
- Landmarks ARIA
- Microdata/JSON-LD
- Meta tags
```

### Issue #43 - Architecture CSS
**Labels**: frontend, css
```
- CSS Layers (@layer)
- Custom properties (tokens)
- Container queries
- Reset minimal
```

### Issue #44 - Composants UI
**Labels**: frontend, css
```
- Header/navigation
- Cards posts
- Pagination
- Footer
- Sidebar
```

### Issue #45 - Page post
**Labels**: frontend, css
```
- Typographie article
- Code highlighting (CSS only)
- Images responsive
- Navigation article
```

### Issue #46 - Pages listing
**Labels**: frontend, css
```
- Grille posts
- Filtres visuels
- États vides
- Loading states
```

### Issue #47 - JavaScript minimal
**Labels**: frontend, js
```
- ES modules
- Prefetch navigation
- View Transitions API
- Progressive enhancement
```

### Issue #48 - Recherche client
**Labels**: frontend, js
```
- Chargement index JSON
- Recherche fuzzy simple
- Affichage résultats
- Debounce input
```

### Issue #49 - Responsive et a11y
**Labels**: frontend, a11y
```
- Breakpoints mobiles
- Focus visible
- Skip links
- Contraste couleurs
- Tests lecteur écran
```

### Issue #50 - Tests frontend
**Labels**: testing
```
- Tests visuels
- Tests accessibilité
- Tests performance
- Tests JS
```

---

## M7 - Optimisation & Sécurité Finale

### Issue #51 - Minification
**Labels**: optimization
```
- Minification HTML
- Minification CSS
- Minification JS
- Intégration génération
```

### Issue #52 - Audit sécurité
**Labels**: security
```
- Revue code sécurité
- Tests injection
- Tests CSRF
- Tests authentification
- Rapport vulnérabilités
```

### Issue #53 - Optimisation images
**Labels**: optimization
```
- Lazy loading natif
- Attributs dimensions
- Formats modernes (recommandations)
- Documentation pipeline
```

### Issue #54 - Cache et assets
**Labels**: optimization
```
- Versioning assets (hash)
- Cache headers recommandés
- Documentation serveur
```

### Issue #55 - Documentation utilisateur
**Labels**: documentation
```
- Guide installation
- Guide utilisation admin
- Guide rédaction Markdown
- FAQ
```

### Issue #56 - Documentation technique
**Labels**: documentation
```
- Architecture détaillée
- API interne
- Guide contribution
- Changelog
```

### Issue #57 - Tests finaux
**Labels**: testing
```
- Tests de charge
- Tests sécurité e2e
- Tests cross-browser
- Validation W3C
```
