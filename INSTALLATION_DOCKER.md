# Installation et utilisation de Docker pour Miam Up

Docker fournit deux services qui tournent en parallèle de ton installation locale :
- **MySQL 8.0** sur le port `3307` (pour ne pas entrer en conflit avec MySQL local sur 3306)
- **Mailpit** sur les ports `1025` (SMTP) et `8025` (interface web des emails)

---

## Prérequis

Installer **Docker Desktop** : https://www.docker.com/products/docker-desktop/

---

## Première installation sur un nouveau poste

### 1. Cloner le projet et installer les dépendances
```bash
git clone <url-du-repo>
cd miam_up
composer install
npm install
```

### 2. Créer le fichier `.env.local`
Créer un fichier `.env.local` à la racine (jamais commité) avec tes credentials :
```
# Choisir UNE des deux lignes selon si tu utilises Docker ou MySQL local

# MySQL DOCKER — port 3307
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3307/miam_up?serverVersion=8.0.32&charset=utf8mb4"

# MySQL LOCAL (MySQL80 Windows) — port 3306
# DATABASE_URL="mysql://root:TON_MOT_DE_PASSE@127.0.0.1:3306/miam_up?serverVersion=8.0"

# Credentials Google OAuth
GOOGLE_CLIENT_ID=ton_client_id
GOOGLE_CLIENT_SECRET=ton_client_secret
```

### 3. Lancer Docker et initialiser la base de données
```bash
docker compose up -d
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

### 4. Lancer l'application
```bash
npm run start
```
L'application est disponible sur http://127.0.0.1:8000

---

## Utilisation quotidienne

```bash
docker compose up -d    # démarrer (données conservées entre les sessions)
docker compose down     # arrêter
docker compose ps       # voir l'état
```

> Docker ne se relance pas automatiquement au démarrage de Windows — refaire `docker compose up -d` après un reboot.

---

## Switcher entre Docker et MySQL local

Les deux peuvent tourner **en même temps** (ports différents : Docker=3307, local=3306).  
Pour changer, décommenter la bonne ligne dans `.env.local` et vider le cache :
```bash
php bin/console cache:clear
```

---

## Intercepter les emails avec Mailpit

Ajouter dans `.env.local` :
```
MAILER_DSN=smtp://localhost:1025
```
Puis ouvrir **http://localhost:8025** pour voir les emails.

---

## Connexion avec MySQL Workbench / DBeaver

| | Docker | MySQL local |
|---|---|---|
| Hôte | `127.0.0.1` | `127.0.0.1` |
| Port | `3307` | `3306` |
| Utilisateur | `app` | `root` |
| Mot de passe | `!ChangeMe!` | ton mot de passe |
| Base | `miam_up` | `miam_up` |

---

## Réinitialiser complètement la base Docker

```bash
docker compose down -v   # supprime les containers ET les données
docker compose up -d
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

> `down -v` supprime définitivement toutes les données du container.

### Cas d'erreur à connaître

Si `doctrine:migrations:migrate` échoue avec une erreur du type `Table 'xxx' already exists`, cela signifie en général que le volume MySQL Docker contient encore un schéma d'une ancienne exécution.

Procédure à suivre:
```bash
docker compose down -v
docker compose up -d
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
```

Cette remise à zéro est la méthode la plus fiable pour tester un merge ou une nouvelle migration sur Docker.
