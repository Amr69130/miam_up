# Installation Tailwind CSS pour Symfony

Ce projet utilise **symfonycasts/tailwind-bundle** (pas de Node.js requis).

## Prerequis

Activer l'extension PDO MySQL dans php.ini :
```ini
extension=pdo_mysql
extension=mysqli
```

## Installation (deja fait)

```bash
composer require symfonycasts/tailwind-bundle
php bin/console tailwind:init
```

## Utilisation quotidienne

Terminal 1 (Serveur) :
```bash
php -S localhost:8000 -t public
```


Terminal 2 (Tailwind watch) :
```bash
php bin/console tailwind:build --watch
```

Terminal 1 : Serveur PHP local pour acceder a ton site (http://localhost:8000)
Terminal 2 : Tailwind surveille tes fichiers et recompile automatiquement le CSS quand tu modifies un template
Les deux doivent tourner en meme temps pendant le developpement.

