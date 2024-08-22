# Installation
## Environnement nécessaire
Symfony 6.4.*

PHP 8.2.*

MySql 8

## Suivre les étapes suivantes :

**Etape 1.1 :** Cloner le repository suivant depuis votre terminal :

```
git clone https://github.com/KarolZ13/P8-OCR
```

**Etape 1.2 :** Executer la commande suivante :

```
composer install
```

**Etape 2 :** Editer le fichier .env

- pour renseigner vos paramètres de connexion à votre base de donnée dans la variable DATABASE_URL

**Etape 3 :** Démarrer votre environnement local (Par exemple : Xampp)

**Etape 4 :** Exécuter les commandes symfony suivantes depuis votre terminal

```
    symfony console doctrine:database:create (ou php bin/console d:d:c si vous n'avez pas installé le client symfony)
    symfony console doctrine:schema:update
    symfony console doctrine:fixtures:load  
```

