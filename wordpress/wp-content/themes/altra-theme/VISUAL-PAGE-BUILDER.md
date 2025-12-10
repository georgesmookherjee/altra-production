# Visual Page Builder - Guide Utilisateur

## Vue d'ensemble

Le système Visual Page Builder pour Altra Production vous permet de contrôler visuellement deux aspects de votre site :

1. **Grid Manager** : Organiser les projets sur la page d'accueil avec drag & drop
2. **Visual Card Editor** : Personnaliser l'affichage de chaque carte projet (focal point, zoom)

---

## 📐 Grid Manager

### Accéder au Grid Manager

1. Allez sur la **page d'accueil** de votre site (frontend)
2. Connectez-vous en tant qu'administrateur
3. Un bouton **"Edit Grid"** apparaît en haut à droite

### Utiliser le Grid Manager

#### Interface

Quand vous cliquez sur "Edit Grid", vous verrez :
- **Sidebar gauche** : Liste des projets disponibles (non présents dans la grille)
- **Zone centrale** : Grille interactive avec vos projets actuels
- **Toolbar en haut** : Boutons "Save Grid" et "Cancel"

#### Actions possibles

**Ajouter un projet à la grille**
- Cliquez sur un projet dans la sidebar gauche
- Le projet est ajouté automatiquement à la grille

**Déplacer un projet**
- Cliquez et glissez une carte projet dans la grille
- La grille s'ajuste automatiquement

**Changer la taille d'un projet**
- Utilisez les boutons **S** (Small), **M** (Medium), **L** (Large) sur chaque carte
- S = 1/3 de largeur, M = 1/2 largeur, L = pleine largeur

**Retirer un projet**
- Cliquez sur le bouton **×** (rouge) sur une carte
- Le projet retourne dans la sidebar

**Sauvegarder**
- Cliquez sur **"Save Grid"**
- Les positions sont sauvegardées en base de données
- La page se recharge avec la nouvelle disposition

**Annuler**
- Cliquez sur **"Cancel"**
- Toutes les modifications non sauvegardées sont perdues

#### Statistiques

La sidebar affiche :
- Nombre de projets disponibles
- Nombre de projets dans la grille
- Répartition par taille (S/M/L)

---

## 🎨 Visual Card Editor

### Accéder au Visual Card Editor

1. Allez dans **Projets** dans l'admin WordPress
2. Modifiez un projet existant ou créez-en un nouveau
3. Scroll jusqu'à la meta box **"Visual Card Settings"**

### Utiliser le Visual Card Editor

#### Section : Image Settings

**Focal Point Picker**
- Utilisez la zone interactive pour **pan & zoom**
- Le point central de votre sélection devient le focal point
- Valeurs affichées en % (X et Y)
- Utile pour cadrer parfaitement vos images sur la homepage

**Image Zoom**
- Slider : de **1.0x** (taille normale) à **2.5x** (zoom maximum)
- Boutons presets : Fit (1.0x), 1.5x, 2x, 2.5x
- Le zoom s'applique **sans cropper** l'image (toute l'image reste visible)

#### Section : Text Layers

**Champs disponibles**
- ☑ Title
- ☑ Client
- ☑ Photographer
- ☑ Project Name

**Actions**
- Cochez/décochez pour afficher/masquer un champ
- **Glissez-déposez** les champs actifs pour changer leur ordre
- Utilisez les boutons **S**, **M**, **L** pour ajuster la taille du texte

#### Actions

**Reset to Defaults**
- Cliquez sur ce bouton pour tout réinitialiser
- Focal point retourne à 50%, 50%
- Zoom retourne à 1.0x
- Text layers sont vidés

**Sauvegarder**
- Cliquez sur **"Mettre à jour"** (bouton standard WordPress)
- Les paramètres visuels sont sauvegardés avec le projet

### Preview

Une carte de préview s'affiche en temps réel pour vous montrer le rendu approximatif.

⚠️ **Note** : Le rendu final peut varier légèrement selon le template de la carte projet.

---

## 🔧 Fonctionnalités Techniques

### Grid Manager

- **Grille 12 colonnes** : Layout flexible et responsive
- **Hauteur automatique** : Les lignes s'ajoutent dynamiquement
- **Sauvegarde REST API** : Positions sauvegardées en temps réel
- **Fallback intelligent** : Si pas de positions définies, ordre par date

### Visual Card Editor

- **Focal Point** : Exprimé en % depuis top-left (0-100%)
- **Zoom** : Facteur multiplicateur (1.0 - 2.5)
- **Object-fit: contain** : Images toujours entières, jamais croppées
- **Transform CSS** : `transform-origin` + `scale()`

### Données sauvegardées

**Grid Manager** (par projet)
```json
{
  "x": 0,      // Colonne (0-11)
  "y": 0,      // Ligne (0-∞)
  "w": 6,      // Largeur en colonnes (4, 6, ou 12)
  "h": 2,      // Hauteur (fixe)
  "order": 1   // Ordre de tri
}
```

**Visual Card Editor** (par projet)
```json
{
  "focalPoint": { "x": 50, "y": 50 },
  "zoom": 1.0,
  "textLayers": [
    {
      "id": "title",
      "visible": true,
      "size": "medium",
      "position": { "x": 20, "y": 80 }
    }
  ]
}
```

---

## 💡 Conseils d'utilisation

### Grid Manager

1. **Variez les tailles** : Alternez S/M/L pour créer un rythme visuel intéressant
2. **Projets importants en L** : Mettez en avant vos meilleurs projets en pleine largeur
3. **Testez sur mobile** : La grille s'adapte automatiquement sur petit écran
4. **Sauvegardez souvent** : Évitez de perdre votre travail

### Visual Card Editor

1. **Focal point précis** : Prenez le temps de bien cadrer vos images
2. **Zoom modéré** : Un zoom trop fort peut pixelliser l'image
3. **Preview != production** : Vérifiez le rendu final sur la homepage
4. **Featured image requise** : Sans image à la une, le Card Editor n'apparaît pas

---

## 🐛 Dépannage

### Grid Manager ne s'affiche pas
- Vérifiez que vous êtes connecté en admin
- Vérifiez que vous êtes sur la page d'accueil (frontend)
- Videz le cache du navigateur

### Les changements ne sont pas visibles
- Assurez-vous d'avoir cliqué sur "Save Grid"
- Rafraîchissez la page (Ctrl+F5)
- Vérifiez que les projets ont bien une image à la une

### Card Editor vide
- Vérifiez qu'une **image à la une** est définie pour le projet
- Si pas d'image, un message vous demandera d'en ajouter une

### Images déformées
- Vérifiez le **ratio d'aspect** de votre image source
- Le système utilise `object-fit: contain` donc l'image ne devrait jamais être croppée
- Testez avec zoom = 1.0 pour voir l'image originale

---

## 📚 Support technique

Pour toute question technique, contactez votre développeur ou consultez le code source dans :
- `assets/js/src/grid-manager/` - Code Grid Manager
- `assets/js/src/card-editor/` - Code Card Editor
- `functions.php` - Backend WordPress

---

**Version** : 1.0
**Date** : Décembre 2024
**Développé avec** : React, WordPress REST API, GridStack.js, react-easy-crop
