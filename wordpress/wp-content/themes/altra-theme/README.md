# Altra Theme

Thème WordPress custom pour le portfolio de [Altra Production](https://altraproduction.com) — société de production photo/vidéo de Basile Smookherjee.

> Pour la documentation technique complète (architecture, data model, conventions), voir `CLAUDE.md` à la racine du repo.

---

## Fonctionnalités

- Grille homepage 4 colonnes avec drag-drop (Grid Manager)
- Cartes projet : images et vidéos Vimeo (portrait 1 col / paysage pleine largeur)
- Autoplay Vimeo inline (background mode, muet, loop)
- Card Editor inline sur la homepage (zoom + focal point, logged-in uniquement)
- Card Editor admin (backoffice React, focal point + zoom)
- Page projet : slideshow media (images + vidéos)
- Backoffice : toggle Cover Media Image/Vidéo, gallery drag-drop mixte

---

## Structure

```
altra-theme/
├── functions.php              # CPT, metaboxes, REST API, enqueue
├── index.php                  # Homepage (grille)
├── single-project.php         # Page projet (slideshow)
├── header.php / footer.php
├── template-parts/
│   └── project-card.php       # Carte (image ou vidéo Vimeo)
├── assets/
│   ├── css/
│   │   ├── flexible-layout.css
│   │   └── admin.css
│   └── js/
│       ├── admin.js
│       └── src/
│           ├── grid-manager/
│           ├── card-editor/
│           └── card-editor-inline/
└── build/                     # Bundles compilés (généré automatiquement)
```

---

## Développement

### Prérequis

- Docker + Docker Compose
- Node.js 18+
- WordPress 6.x (lancé via Docker)

### Démarrage

```bash
# Depuis la racine du repo
docker-compose up -d

# Admin : http://localhost:8083/wp-admin
```

### Build JS/React

```bash
cd wordpress/wp-content/themes/altra-theme
npm install       # première fois seulement
npm run build     # build production
npm run start     # watch (développement)
```

> À relancer après chaque modification dans `assets/js/src/`.

---

## Ajout d'un projet

1. **Projects > Ajouter** dans le backoffice
2. Renseigner le titre
3. **Cover Media** (sidebar) : choisir Image ou Vidéo
   - Image : définir une image à la une
   - Vidéo : coller l'URL Vimeo + choisir l'orientation
4. **Project Gallery** : ajouter des images WP et/ou vidéos Vimeo (drag-drop pour ordonner)
5. **Visual Card Editor** : ajuster le cadrage (focal point + zoom) de la carte homepage
6. **Project Details** : client, photographe, styliste, DA, date, lieu
7. Publier

---

## Format des données gallery

Les items de gallery sont stockés en JSON dans `_altra_project_gallery` :

```json
[
  { "type": "image", "id": 42 },
  { "type": "video", "url": "https://vimeo.com/123456789", "orientation": "landscape" }
]
```

L'ancien format CSV (`"42,55,35"`) est migré automatiquement par `altra_get_gallery_items()`.
