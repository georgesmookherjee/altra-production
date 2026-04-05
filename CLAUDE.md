# CLAUDE.md — Altra Production

Contexte complet pour Claude Code. À lire en début de session avant de toucher au code.

---

## Projet

Site portfolio de **Basile Mookherjee**, directeur artistique / DA de la société **Altra Production** (Paris). Le site présente ses projets photo et vidéo sous forme de grille visuelle inspirée de [Sheriff Projects](https://sheriff.paris).

- Dev géré par **Georges Mookherjee** (frère de Basile)
- Repo GitHub : `georgesmookherjee/altra-production`
- Branche principale : `master`
- Environnement local : Docker (WordPress + MySQL)
- Admin local : `http://localhost:8083/wp-admin`

---

## Stack technique

| Couche | Techno |
|---|---|
| CMS | WordPress 6.x |
| Thème | Custom PHP (`altra-theme`) |
| JS/React | `@wordpress/element` (React wrapper WP) + Webpack (`@wordpress/scripts`) |
| Drag-drop grid | GridStack 12.x |
| Focal point | react-easy-crop (dans Card Editor admin) |
| Conteneurisation | Docker Compose |
| Typo cible | Suisse Ecal International (OTF à convertir en WOFF2) |

---

## Architecture du thème

```
wordpress/wp-content/themes/altra-theme/
├── functions.php              # Tout : CPT, metaboxes, REST API, enqueue scripts
├── index.php                  # Homepage (grille de projets)
├── single-project.php         # Page projet (slideshow media + navigation clic)
├── page-infos.php             # Template page Infos (slug: infos)
├── page-contact.php           # Template page Contact (slug: contact)
├── header.php / footer.php
├── template-parts/
│   └── project-card.php       # Carte projet (image ou vidéo Vimeo)
├── assets/
│   ├── css/
│   │   ├── flexible-layout.css  # Grid CSS principal + responsive mobile homepage
│   │   └── admin.css            # Styles backoffice
│   └── js/
│       ├── admin.js             # JS backoffice (gallery drag-drop, Vimeo form)
│       └── src/
│           ├── grid-manager/    # React : gestion drag-drop grille (frontend admin)
│           ├── card-editor/     # React : Card Editor (admin backoffice)
│           └── card-editor-inline/ # Vanilla JS : éditeur inline homepage (logged-in)
└── build/                     # Bundles compilés (ne pas éditer manuellement)
```

**Build :** `cd wordpress/wp-content/themes/altra-theme && npm run build`

---

## Custom Post Type : `project`

### Champs custom (post meta)

| Clé meta | Valeurs | Description |
|---|---|---|
| `_altra_client` | string | Client |
| `_altra_photographer` | string | Photographe |
| `_altra_stylist` | string | Styliste |
| `_altra_art_director` | string | DA |
| `_altra_project_date` | string | Date |
| `_altra_location` | string | Lieu |
| `_altra_team_members` | string (une par ligne) | Équipe |
| `_altra_featured_media_type` | `'image'` \| `'video'` | Type de média couverture |
| `_altra_featured_video_url` | URL Vimeo | Ex: `https://vimeo.com/123456789` |
| `_altra_featured_video_orientation` | `'portrait'` \| `'landscape'` | Orientation vidéo |
| `_altra_project_gallery` | JSON array | Voir format ci-dessous |
| `_altra_visual_settings` | JSON object | Focal point + zoom (Card Editor) |
| `_altra_grid_position` | JSON object | Position/taille dans la grille |

### Format gallery (`_altra_project_gallery`)

Nouveau format JSON (migré automatiquement depuis l'ancien format CSV d'IDs) :

```json
[
  { "type": "image", "id": 42 },
  { "type": "image", "id": 55 },
  { "type": "video", "url": "https://vimeo.com/123456789", "orientation": "landscape" }
]
```

**Helper PHP :** `altra_get_gallery_items($post_id)` — lit les deux formats, retourne toujours le nouveau format.

### Format visual settings (`_altra_visual_settings`)

```json
{ "focalPoint": { "x": 50, "y": 50 }, "zoom": 1.0, "textLayers": [] }
```

---

## Logique grille CSS (homepage)

- **4 colonnes** CSS Grid
- Portraits : `grid-column: span 1`, `aspect-ratio: 3/4`
- Paysages photo : `grid-column: span 2`, `aspect-ratio: 3/1.9`
- Vidéo portrait : `grid-column: span 1`, `aspect-ratio: 3/4`
- Vidéo paysage : `grid-column: span 4` (pleine largeur), `aspect-ratio: 3/1`

La hauteur des lignes est cohérente : à 4 colonnes de largeur `W`, une vidéo `3/1` a la même hauteur qu'un portrait `3/4` en 1 colonne (`4W/3 = W*4/3`).

---

## Support vidéo Vimeo

### Homepage (autoplay inline)

Embed Vimeo en background mode :
```
https://player.vimeo.com/video/{ID}?background=1&autoplay=1&loop=1&muted=1&byline=0&title=0
```

Structure HTML dans `project-card.php` :
```html
<div class="project-image">               <!-- aspect-ratio défini par CSS -->
  <div class="project-video-wrapper">    <!-- position: absolute, 100%x100% -->
    <iframe ...></iframe>
  </div>
</div>
```

### Thumbnail pour Card Editor

Service : Vimeo oEmbed API — `https://vimeo.com/api/oembed.json?url={url}&width=400`
- Fonctionne pour les vidéos **publiques et non répertoriées** ("Masquer sur Vimeo")
- Ne fonctionne **pas** pour les vidéos "Privé" (accès bloqué par Vimeo)
- Résultat mis en cache via WordPress transients (24h) — helper PHP : `altra_get_vimeo_thumbnail($url)`
- Vimeo Free ne permet que Public ou Privé ; "Masquer sur Vimeo" (non répertorié) nécessite le forfait Starter (10€/mois minimum)

### Inline Card Editor (homepage, logged-in)

`card-editor-inline/index.js` : le transformTarget est `img` pour les photos, `.project-video-wrapper` pour les vidéos (le zoom/focal point s'applique au wrapper qui contient l'iframe).

---

## Backoffice admin — metaboxes projet

1. **Cover Media** (side, high priority) — radio Image/Vidéo + champ URL Vimeo + orientation
2. **Project Details** — champs texte client/photographe/etc.
3. **Project Gallery** — drag-drop JSON (images WP Media + vidéos Vimeo)
4. **Visual Card Editor** (React) — focal point + zoom sur l'image/vignette de couverture
5. **Grid Position** (auto-généré par Grid Manager)

---

## REST API custom

Endpoint : `POST /wp-json/altra/v1/project/{id}/visual-settings`
- Sauvegarde `_altra_visual_settings` (focalPoint, zoom, textLayers)
- Nonce WP REST pour auth

Endpoint : `GET/POST /wp-json/altra/v1/grid`
- Lit/écrit les positions de grille de tous les projets

---

## Travail en cours / Pending

- [ ] **Typographie** : Suisse Ecal International — OTF disponibles à la racine du repo (hors git), à convertir en WOFF2 via fontsquirrel.com puis intégrer via `@font-face`. Positionnement des éléments typo à définir.
- [ ] **Dimensions grille** : ajustements fins (gaps, tailles) — Basile gère lui-même via l'admin
- [ ] **Déploiement** : hébergement OVH — déploiement à configurer (prévu via API ou FTP)
- [ ] **Favicon** : pas encore créé/intégré
- [ ] **noindex** : à activer dans WP Admin > Réglages > Lecture pendant la phase de développement
- [ ] **PDF de référence** : `Site-altra.pdf` à la racine (hors git, design cible pages 5-7)
- [ ] **Responsive** : quelques détails mobiles restants à affiner (répertoriés lors de la prochaine session)

## Implémenté et fonctionnel

- [x] Grille homepage 4 colonnes avec drag-drop (Grid Manager)
- [x] Card Editor inline (homepage, zoom + focal point)
- [x] Card Editor admin (backoffice, React, focal point + zoom)
- [x] Support vidéo Vimeo : autoplay inline, card paysage pleine largeur, card portrait 1 col
- [x] Migration automatique gallery CSV → JSON
- [x] Metabox Cover Media (toggle Image/Vidéo)
- [x] Miniature Vimeo dans Card Editor via oEmbed API (fonctionne pour vidéos publiques et non répertoriées)
- [x] Grid Manager : vignettes correctes, spans corrects (portrait=1col, paysage=4col), positions persistantes, `float:true` pour éviter l'auto-compaction
- [x] Homepage : seuls les projets avec `_altra_grid_position` sauvegardée s'affichent (meta_query EXISTS)
- [x] **Page projet** (single-project.php) : slideshow media (images + vidéos Vimeo) avec navigation au clic, label projet, compteur slides, métadonnées en bas
- [x] **Pages Infos et Contact** : templates PHP dédiés (`page-infos.php`, `page-contact.php`), contenu texte centré, typographie légère
- [x] **Responsive mobile complet** :
  - Homepage : 2 colonnes ≤1024px, breakpoints portrait/paysage, valeurs `vw` pour spacing proportionnel
  - Pages projet : galerie adaptée, label au-dessus, compteur aligné, hauteur fixe 65vh (portrait) / 80vh (paysage)
  - Header mobile : padding réduit, font-size ajusté
  - Footer : compact, centré verticalement
  - Pages Infos/Contact : padding `clamp()`, texte centré

---

## Conventions de développement

- Pas de plugin, tout dans `functions.php` et le thème
- Les hooks PHP : préfixe `altra_`
- Les clés meta : préfixe `_altra_`
- Build obligatoire après tout changement JS/React : `npm run build`
- Ne pas éditer les fichiers `build/` à la main
- `object-fit: cover` partout, pas de letterbox/pillarbox
