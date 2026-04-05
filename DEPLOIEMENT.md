# Déploiement — Altra Production

## Hébergement OVH

| Paramètre | Valeur |
|---|---|
| Hébergement | `altrapy.cluster029.hosting.ovh.net` |
| Offre | Perso |
| Domaine | `altraproduction.com` |
| Dossier racine | `/home/altrapy/www/` |
| Serveur FTP/SFTP | `ftp.cluster129.hosting.ovh.net` |
| Port SFTP | `22` |
| Login FTP | `altrapy` |
| BDD nom | `altrapy318` |
| BDD user | `altrapy318` |
| BDD hôte | `altrapy318.mysql.db` |
| BDD version | MySQL 8.0 |

**URL de login WP :** `altraproduction.com/wp-login.php?altra-acces`
**Login WP admin :** `admin5370`

---

## Procédure de déploiement (mise à jour du thème)

Le déploiement consiste à uploader les fichiers du thème via SFTP. La base de données et le contenu (projets, pages) restent sur OVH.

### Outil : WinSCP

Paramètres de connexion WinSCP :
- Protocole : SFTP
- Hôte : `ftp.cluster129.hosting.ovh.net`
- Port : `22`
- Login : `altrapy`
- Mot de passe : (mot de passe FTP OVH)

### Fichiers à uploader

Dossier local → dossier distant :
```
d:\IT\altra-production\wordpress\wp-content\themes\altra-theme\
→ /home/altrapy/www/wp-content/themes/altra-theme/
```

**Exclure impérativement** le dossier `node_modules/` (trop lourd, inutile sur le serveur).

Fichiers utiles à uploader :
- `assets/` — CSS et images
- `build/` — JS compilé (généré par `npm run build`)
- `template-parts/` — templates PHP
- Tous les fichiers `.php` à la racine du thème
- `style.css`

### Build JS avant déploiement

Si des fichiers JS/React ont été modifiés, lancer le build avant d'uploader :
```bash
cd wordpress/wp-content/themes/altra-theme
npm run build
```

---

## Mode maintenance

Le site affiche "Bientôt en ligne" aux visiteurs non connectés tant que le mode maintenance est actif.

**Pour désactiver** (quand le contenu est prêt) : ouvrir `functions.php` et supprimer ou commenter le bloc `// MAINTENANCE` en bas du fichier, puis uploader `functions.php` sur OVH.

---

## Mise à jour du contenu (projets, pages)

Le contenu est géré directement dans le WP Admin OVH :
- `altraproduction.com/wp-login.php?altra-acces`
- Projets → Custom Post Type "Projects" → ajouter/modifier
- Pages Infos et Contact → Pages WP (slugs `infos` et `contact`)
- Grille homepage → bouton "Edit Grid" sur la homepage (connecté)

---

## phpMyAdmin

Accessible depuis OVH → Hébergements → altraproduction.com → Bases de données → icône phpMyAdmin.
Connexion : user `altrapy318`, mot de passe BDD OVH.

---

## Notes

- Le WordPress OVH est indépendant du WordPress local (Docker). Les deux ont des BDD séparées.
- Le contenu local (projets de test) n'a pas été migré — les projets sont à recréer sur OVH.
- PHP 8.0 sur OVH (compatible avec le thème).
