# Blog

Tu es un **architecte logiciel + développeur full-stack senior**. Tu dois concevoir et implémenter un **micro-système de blog** avec une **admin** qui, **à chaque modification**, **régénère des pages HTML statiques** (index + pages post + pages catégories + pages tags). Objectifs prioritaires : **sécurité**, **performance**, **simplicité**, **maintenabilité**.

### 1) Contexte & contraintes

* Le site public est **statique** : pages HTML générées (pas de rendu serveur à la requête).
* Le html est sémentique.
* **Aucune librairie externe** (front & back) : uniquement **notre JS et notre CSS** (et nos utilitaires internes).
* Le **JS et le CSS doivent être légers, modernes et “disruptifs”** (utilise les technos Web récentes **natives** : modules ES, Web Components si utile, CSS layers, :has(), container queries, view transitions, etc. mais garde un fallback propre).
* Les posts sont rédigés en **Markdown** (conversion vers HTML au moment de la génération).
* Chaque post a un **slug immuable** (une fois créé, il ne peut plus être modifié). Le slug sert au linking interne.
* Classement :

  * **Catégories** en **arbre** (parent/enfants) avec héritage/chemins.
  * **Tags** transversaux (indépendants des catégories).

### 2) Fonctionnalités attendues

#### Admin (privée)

* Authentification sécurisée (session, CSRF, protection brute-force).
* CRUD Posts :

  * titre, slug (généré + verrouillé après création), contenu markdown, extrait, statut (draft/published), dates, catégorie, tags, SEO (title/description), options (featured, etc.).


* CRUD Catégories :

  * arbre : création, déplacement, renommage (sans casser les URLs des posts), slug catégorie (peut être modifiable si tu proposes une stratégie safe).


* CRUD Tags :

  * création, fusion éventuelle (optionnel), suppression (avec impacts).


* Éditeur Markdown :

  * preview
  * **linking interne assisté** : quand l’utilisateur tape un pattern de lien (ex: `[[` ou `/` dans un lien Markdown), un **menu déroulant autocomplétant** apparaît pour sélectionner une page existante (posts, catégories, tags). À la sélection, insère le lien correct basé sur le slug.


* À chaque action qui change le contenu publié (ou sur “Publish”), déclencher une **génération incrémentale** :

  * index
  * page post
  * pages catégories (avec listing paginé si besoin)
  * pages tags (idem)
  * sitemap.xml + RSS/Atom (optionnel mais recommandé)
  * fichiers JSON d’index interne (pour recherche front éventuelle)

#### Site public (statique)

* Index avec pagination, filtres (catégorie/tag), pages dédiées :

  * `/` index
  * `/post/<slug>/`
  * `/category/<path>/` (chemin hiérarchique)
  * `/tag/<slug>/`


* Navigation performante :

  * préchargement léger
  * View Transitions (si support) pour transitions fluides

  
* Design “magnifique”, très léger, accessible (a11y), responsive.

### 3) Sécurité (non négociable)

* Threat model minimal : XSS, CSRF, injection, SSRF, path traversal, RCE via génération, accès admin, exfiltration.
* Markdown vers HTML doit être **sanitisé** strictement (liste blanche de tags/attributs). Aucun HTML brut non filtré. Pas de `javascript:` dans les liens.
* Génération de fichiers :

  * chemins safe (pas de `../`), écriture atomique, permissions minimales.
  * verrouillage/concurrence (éviter générations simultanées incohérentes).
* Admin :

  * mots de passe hashés fort (Argon2id / bcrypt), rate limiting, logs de sécurité.
  * CSRF token, cookies `HttpOnly`, `Secure`, `SameSite`.
  * headers de sécurité sur public : CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy.
* Aucune donnée sensible dans le front statique.

### 4) Performance

* Zéro dépendance externe, bundle minimal.
* HTML statique optimisé :

  * CSS critical (optionnel), minification, compression (gzip/brotli côté serveur web), cache headers.
* Images : pipeline recommandé (naming, dimensions, lazy loading), mais sans lib externe : propose une stratégie simple.
* Génération incrémentale : ne régénère que ce qui a changé + pages impactées (index, catégorie, tags concernés).
* Recherche : optionnel, mais si présent, index JSON compact + recherche côté client.

### 5) Architecture & livrables demandés

Produis une réponse structurée avec :

1. **Spécification fonctionnelle** (règles slug immuable, URL scheme, comportement admin).
2. **Modèle de données** (tables/structures + contraintes, ex: posts, categories, category_tree, tags, post_tags).
3. **Algorithme de génération** :

   * génération complète vs incrémentale
   * invalidation des pages dépendantes
   * stratégie de templating interne (sans lib)
4. **Stratégie Markdown → HTML** :

   * parser minimal, extensions supportées
   * sanitation détaillée
5. **UI/UX admin** :

   * comportement exact de l’autocomplétion de liens (déclencheur, requête, insertion, accessibilité clavier).
6. **Front public** :

   * structure CSS moderne (layers, tokens, container queries…)
   * JS minimal (modules, progressive enhancement)
7. **Plan sécurité** (checklist + mesures concrètes).
8. **Plan performance** (budgets, caching, minif, lazy, etc.).
9. **Plan de tests** :

   * unitaires (slug, tree, markdown, sanitize)
   * e2e (admin publish, génération, liens)
   * tests sécu de base (XSS payloads, CSRF).
10. **Arborescence projet** proposée + conventions de code.

### 6) Choix technos (à proposer)

Propose une stack réaliste (ex : PHP 8 + SQLite + templates maison + génération dans `/public/`) OU Node (sans libs) si tu peux le justifier. Mais tu dois rester cohérent avec :

* typage strict si possible,
* doc complète (PHPDoc/JSDoc),
* standards (PSR-12 si PHP).

### 7) Style de réponse

* Sois concret : donne des schémas, exemples d’URLs, pseudo-code des étapes de génération.
* Pas de blabla : chaque décision doit être justifiée par sécurité/perf/maintenabilité.
* Pas de dépendances externes, pas de CDN.

### 8) Dev
* Code SOLID
* TDD (tout le code doit avoir ses tests unitaires)
* Commentaire et documentation exhaustive en français.
