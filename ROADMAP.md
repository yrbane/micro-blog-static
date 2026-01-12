# Roadmap - Micro Blog Statique

## Milestones

---

### M1 - Fondations (Core)
**Objectif** : Infrastructure de base, routing, sécurité, base de données

- Configuration projet (structure, autoloading PSR-4)
- Kernel applicatif (bootstrap, configuration)
- Router minimaliste
- Connexion SQLite + migrations
- Système d'authentification (Argon2id, sessions sécurisées)
- Protection CSRF
- Rate limiting
- Middleware de sécurité (headers)
- Tests unitaires core

---

### M2 - Modèles & CRUD
**Objectif** : Entités métier et opérations CRUD complètes

- Model Post (avec slug immuable)
- Model Category (arbre hiérarchique)
- Model Tag
- Repository pattern pour chaque entité
- Service de gestion des slugs
- Service de gestion de l'arbre catégories
- Validation des données
- Tests unitaires models/services

---

### M3 - Parser Markdown
**Objectif** : Conversion Markdown → HTML sécurisée

- Parser Markdown minimaliste (titres, paragraphes, listes, code, liens, images, gras, italique)
- Sanitizer HTML (whitelist stricte)
- Support liens internes `[[slug]]`
- Échappement XSS
- Tests unitaires parser + tests sécurité (payloads XSS)

---

### M4 - Interface Admin
**Objectif** : UI admin fonctionnelle

- Layout admin (HTML/CSS)
- Dashboard
- Formulaires CRUD Posts
- Formulaires CRUD Catégories (avec tree view)
- Formulaires CRUD Tags
- Éditeur Markdown avec preview live
- Autocomplétion liens internes
- Messages flash / notifications
- Tests e2e admin

---

### M5 - Moteur de Génération
**Objectif** : Génération des pages statiques

- Moteur de templates (sans lib)
- Générateur de pages posts
- Générateur index (avec pagination)
- Générateur pages catégories
- Générateur pages tags
- Génération sitemap.xml
- Génération feed RSS/Atom
- Génération index JSON (recherche)
- Système de verrou (concurrence)
- Génération incrémentale (détection changements)
- Tests unitaires génération

---

### M6 - Frontend Public
**Objectif** : Design et interactivité du site statique

- Structure HTML sémantique
- CSS moderne (layers, custom properties, container queries)
- Composants : header, navigation, cards, pagination
- Page post (typographie, code highlighting CSS)
- Pages listing (index, catégorie, tag)
- JavaScript minimal (prefetch, view transitions, recherche)
- Responsive design
- Accessibilité (a11y)
- Tests visuels / snapshot

---

### M7 - Optimisation & Sécurité Finale
**Objectif** : Performance, sécurité, polish

- Minification HTML/CSS/JS
- Audit sécurité complet
- Headers de sécurité (CSP, etc.)
- Optimisation images (lazy loading, dimensions)
- Cache busting assets
- Documentation utilisateur
- Documentation technique
- Tests de charge basiques
- Tests sécurité e2e (injections, CSRF)

---

## Timeline Visuelle

```
M1 ──→ M2 ──→ M3 ──→ M4 ──→ M5 ──→ M6 ──→ M7
Core   Models  MD    Admin  Gen   Front  Polish
```

Chaque milestone est indépendamment testable et livrable.
