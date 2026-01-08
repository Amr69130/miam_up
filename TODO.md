# TODO - Miam Up Project

## 🎨 Frontend / Styling

### ⚠️ IMPORTANT: Migration Tailwind CDN → Installation complète
**Priorité: AVANT LA MISE EN PRODUCTION**

Actuellement, Tailwind est chargé via CDN (pour le développement rapide).
Il faut l'installer correctement pour la production.

**Étapes à suivre:**

1. **Installer les dépendances Node.js**
   ```bash
   npm init -y
   npm install -D tailwindcss postcss autoprefixer
   ```

2. **Créer la configuration Tailwind**
   ```bash
   npx tailwindcss init
   ```

3. **Configurer `tailwind.config.js`**
   ```javascript
   module.exports = {
     content: [
       "./templates/**/*.html.twig",
       "./assets/**/*.js",
     ],
     theme: {
       extend: {},
     },
     plugins: [],
   }
   ```

4. **Créer le fichier CSS principal** (`assets/styles/app.css`)
   ```css
   @tailwind base;
   @tailwind components;
   @tailwind utilities;
   ```

5. **Configurer Webpack Encore** (ou AssetMapper selon la config)
   - Intégrer PostCSS avec Tailwind
   - Compiler les assets

6. **Retirer le CDN** de `templates/base.html.twig`
   - Supprimer: `<script src="https://cdn.tailwindcss.com"></script>`
   - Ajouter: `{{ encore_entry_link_tags('app') }}` (si Webpack Encore)

**Avantages:**
- ✅ Meilleure performance en production
- ✅ CSS optimisé et purifié (seules les classes utilisées)
- ✅ Fichiers plus légers
- ✅ Personnalisation complète (couleurs, fonts, etc.)
- ✅ Plugins Tailwind disponibles

---

## 📝 Fonctionnalités à développer

### Utilisateurs
- [ ] Page d'inscription
- [ ] Page de connexion
- [ ] Page de profil
- [ ] Édition du profil
- [ ] Upload photo de profil
- [ ] Liste des utilisateurs (admin)

### À définir...
