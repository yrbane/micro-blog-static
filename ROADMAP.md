# Roadmap - Micro Blog Statique

## Stack

- **Framework** : lunar-quanta (PHP 8.3+)
- **Templates** : lunar-template
- **BDD** : SQLite

---

## Milestones (révisés)

### M1 - Fondations (Core) ✅ Majoritairement couvert par lunar-quanta

| Issue | Titre | Statut |
|-------|-------|--------|
| ~~#1~~ | ~~Structure projet et autoloading~~ | ✅ Fourni |
| ~~#2~~ | ~~Kernel applicatif~~ | ✅ Fourni |
| ~~#3~~ | ~~Router minimaliste~~ | ✅ Fourni |
| #4 | Migrations SQLite spécifiques | À faire |
| ~~#5~~ | ~~Authentification sécurisée~~ | ✅ Fourni |
| ~~#6~~ | ~~Protection CSRF~~ | ✅ Fourni |
| #7 | Rate limiting | À faire |
| #8 | Headers de sécurité | À faire |
| ~~#9~~ | ~~Tests unitaires Core~~ | ✅ Fourni |

**Reste à faire** : 3 issues (#4, #7, #8)

---

### M2 - Modèles & CRUD ✅ Majoritairement couvert par lunar-quanta

| Issue | Titre | Statut |
|-------|-------|--------|
| #10 | Extension Post avec slug immuable | À faire |
| #11 | Extension Category avec arbre | À faire |
| ~~#12~~ | ~~Model Tag~~ | ✅ Fourni |
| ~~#13~~ | ~~Repository Posts~~ | ✅ Fourni |
| #14 | Service CategoryTree | À faire |
| ~~#15~~ | ~~Repository Tags~~ | ✅ Fourni |
| ~~#16~~ | ~~Service Slugs~~ | ✅ Fourni |
| ~~#17~~ | ~~Validation données~~ | ✅ Fourni |
| #18 | Tests des extensions | À faire |
| **#59** | **Entity Option et OptionService** | À faire |

**Reste à faire** : 5 issues (#10, #11, #14, #18, #59)

---

### M3 - Parser Markdown ✅ Majoritairement couvert par lunar-quanta

| Issue | Titre | Statut |
|-------|-------|--------|
| ~~#19~~ | ~~Parser Markdown base~~ | ✅ Fourni |
| #20 | Support liens internes [[slug]] | À faire |
| ~~#21~~ | ~~Sanitizer HTML~~ | ✅ Fourni |
| ~~#22~~ | ~~Tests parser + sécurité~~ | ✅ Fourni |

**Reste à faire** : 1 issue (#20)

---

### M4 - Interface Admin 🔧 À développer

| Issue | Titre | Statut |
|-------|-------|--------|
| #23 | Layout admin | À faire |
| #24 | Dashboard | À faire |
| #25 | CRUD Posts UI | À faire |
| #26 | CRUD Catégories UI | À faire |
| #27 | CRUD Tags UI | À faire |
| #28 | Éditeur Markdown | À faire |
| #29 | Autocomplétion liens | À faire |
| #30 | Messages et notifications | À faire |
| #31 | Tests e2e Admin | À faire |
| **#60** | **CRUD Options UI (paramètres site)** | À faire |

**Reste à faire** : 10 issues

---

### M5 - Moteur de Génération 🔧 Adaptation nécessaire

| Issue | Titre | Statut |
|-------|-------|--------|
| ~~#32~~ | ~~Moteur templates~~ | ✅ Fourni (lunar-template) |
| #33 | Adaptation générateur posts | À faire |
| #34 | Adaptation générateur index | À faire |
| #35 | Adaptation générateur catégories | À faire |
| #36 | Adaptation générateur tags | À faire |
| ~~#37~~ | ~~Sitemap et RSS~~ | ✅ Fourni |
| #38 | Index JSON recherche | À faire |
| #39 | Système verrou | À faire |
| #40 | Génération incrémentale | À faire |
| #41 | Tests génération | À faire |

**Reste à faire** : 7 issues

---

### M6 - Frontend Public 🔧 À développer

| Issue | Titre | Statut |
|-------|-------|--------|
| **#58** | **Templates de base (base.html.tpl + mail.html.tpl)** | À faire |
| #42 | Structure HTML (layout public) | À faire |
| #43 | Architecture CSS | À faire |
| #44 | Composants UI | À faire |
| #45 | Page post | À faire |
| #46 | Pages listing | À faire |
| #47 | JavaScript minimal | À faire |
| #48 | Recherche client | À faire |
| #49 | Responsive et a11y | À faire |
| #50 | Tests frontend | À faire |

**Reste à faire** : 10 issues

---

### M7 - Optimisation & Sécurité Finale 🔧 À faire

| Issue | Titre | Statut |
|-------|-------|--------|
| #51 | Minification | À faire |
| #52 | Audit sécurité | À faire |
| #53 | Optimisation images | À faire |
| #54 | Cache et assets | À faire |
| #55 | Documentation utilisateur | À faire |
| #56 | Documentation technique | À faire |
| #57 | Tests finaux | À faire |

**Reste à faire** : 7 issues (toutes)

---

## Résumé

| Milestone | Total | Fermées | Restantes |
|-----------|-------|---------|-----------|
| M1 - Core | 9 | 6 | **3** |
| M2 - Models | 10 | 5 | **5** |
| M3 - Markdown | 4 | 3 | **1** |
| M4 - Admin | 10 | 0 | **10** |
| M5 - Generator | 10 | 2 | **8** |
| M6 - Frontend | 10 | 0 | **10** |
| M7 - Optim | 7 | 0 | **7** |
| **TOTAL** | **60** | **16** | **44** |

**~28% du travail économisé** grâce à lunar-quanta et lunar-template.

---

## Timeline Visuelle (révisée)

```
M1 ──→ M2 ──→ M3 ──→ M4 ──→ M5 ──→ M6 ──→ M7
[3]    [5]    [1]   [10]    [8]   [10]    [7]
         ↓
    Focus principal : Admin (M4) + Frontend (M6)
```

Les milestones M1, M2, M3 sont maintenant légers.
Le gros du travail est sur **M4 (Admin)** et **M6 (Frontend)**.
