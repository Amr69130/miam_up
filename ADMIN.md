# 🔐 Guide d'Administration - Miam Up

Ce document sert de guide technique et opérationnel pour la gestion du panel d'administration 

---

## 🚀 Démarrage Rapide

### 1. Lancer le projet
Pour accéder au panel, le serveur local doit être en cours d'exécution :
```bash
# Option A : Si vous avez le binaire Symfony
symfony server:start

# Option B : Serveur PHP classique
php -S localhost:8000 -t public

URL d'accès : http://localhost:8000/admin

🛠️ Maintenance & Dépannage 
🧹 Le remède miracle : Vider le Cache
Si une modification ne s'affiche pas ou si une erreur inconnue survient, videz le cache immédiatement :

Bash
# Méthode rapide (suppression manuelle)
rm -rf var/cache/*

# Méthode Symfony
php bin/console cache:clear
🗄️ Mise à jour de la Base de Données
Si vous voyez une erreur SQL de type Column not found ou cannot be null après avoir modifié une entité :

Bash
php bin/console doctrine:schema:update --force
🖼️ Problèmes d'images (Logos)
Si l'upload d'image échoue avec l'erreur MIME type :

Vérifiez que extension=fileinfo est décommentée dans votre php.ini.

Vérifiez que le dossier public/uploads/logos existe.

Redémarrez impérativement votre serveur et votre terminal.

📝 Règles de Développement Admin
🔑 Sécurité des Utilisateurs
Les mots de passe ne doivent jamais être modifiés directement en base de données. Ils sont hachés automatiquement par le UtilisateurCrudController via le service UserPasswordHasherInterface.

🔗 Gestion des Relations (ManyToMany)
Pour que les listes déroulantes (Spécialités, Régimes, Types, Aménagements) fonctionnent dans le formulaire Restaurant, chaque entité liée doit contenir une méthode __toString() :

PHP
// Exemple dans src/Entity/Type.php
public function __toString(): string
{
    return $this->nom_type ?? '';
}
📂 Structure des fichiers Admin
src/Controller/Admin/ : Contient tous les CrudControllers qui définissent les champs affichés.

src/Entity/ : Contient la logique de données et les méthodes __toString().

public/uploads/ : Stockage physique des fichiers médias.

Dernière mise à jour : Février 2026