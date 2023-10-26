# Description
API de gestion d'équipements

## Pré-requis
- PHP 7.2 et supérieur
- Mysql
- Composer 2.4
- Git

## Installation
```
git clone https://github.com/sergechantave84/equipment-management.git
```

```
composer update
```

Changer la variable d'environnement DATABASE_URL dans le fichier .env
```
DATABASE_URL=mysql://user:password@127.0.0.1:3306/equipment-management
```

### Créer la base de donnée
```
php bin/console d:d:create
```

### Créer les tables et les index
- méthode 1
```
php bin/console d:s:u --force
```

- méthode 2
```
php bin/console doctrine:migrations:generate
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:execute --up 'DoctrineMigrations\Your-File-Migration-Without-php-extension'
```

## Executer l'application
```
php -S 127.0.0.1:8000 -t public
```

## Accéder à la documentation de l'API
```
http://localhost:8000/api/doc
```

## Accéder à l'application
Depuis un client d'API comme Postman, utiliser http://localhost:8000 comme host
- Exemple d'utilisation
```
GET http://localhost:8000/api/equipments
```
