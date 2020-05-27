# English version

## Description

This web site software permits a community of contributors filling in, and then
studying, metrics related to the pollution of underground waters in karstic
environment.

It is developed in Wallonia by [Intuisix](https://www.intuisix.com), on request
of the [Commission Wallonne d'Etude et de Protection des Sites Souterrains](https://www.cwepss.org)
and the funding from the [Société Publique de Gestion de l'Eau](http://www.spge.be).

Presently, the software is available only in French language.


# Version française

## Description

Ce logiciel de site web permet à une communauté de contributeurs de compléter,
et ensuite d'étudier, des métriques concernant la pollution d'eaux souterraines
en milieu karstique.

Il est développé en Wallonie par [Intuisix](https://www.intuisix.com), à la
demande de la [Commission Wallonne d'Etude et de Protection des Sites Souterrains](https://www.cwepss.org)
grâce aux fonds de la [Société Publique de Gestion de l'Eau](http://www.spge.be).

## Installation

### Exigences

Le logiciel nécessite au minimum:
* Un hébergement (par exemple Apache) disposant de PHP version 7.3 ou supérieur,
* Une base de données PostgreSQL version 11 ou supérieure,
* Un serveur SMTP, destiné à l'envoi de messages aux utilisateurs.

Le logiciel est basé sur le framework [Symfony](https://symfony.com) et utilise
beaucoup de composants open-source. Vous aurez donc besoin de [Composer](https://getcomposer.org)
pour l'installation, la gestion et la mise à jour des dépendances.

Vous devrez faire du dossier "public" la racine de votre serveur web.

### Conseil

Si vous disposez d'un accès SSH au serveur ou installez localement, vous pouvez
réaliser les opérations qui suivent directement sur celui-ci.

A defaut, il vous faudra passer par un ordinateur de développement et transférer
ensuite l'intégralité des fichiers et la base de données. Cela vous prendra plus
de temps et d'énergie que la première option.

### Dépendances

Installez les dépendances, qui seront placées dans le dossier "vendor":

    composer install

### Environnement

Voyez dans le fichier ".env" la base pour construire votre fichier
d'environnement et déterminer comment vous devez le nommer. Normalement,
vous pourriez l'appeler ".env.local".

Vous devez au minimum y définir les variables suivantes:
* "APP_ENV" => "prod" ou "dev" selon que vous configuriez un environnement de
  production ou un environnement de développement,
* "DATABASE_URL" => chaîne de connexion vers votre base de données, par exemple:
  "postgresql://user:password@server.domain:port/database?serverVersion=11".
* "MAILER_DSN" => chaîne de connexion vers votre serveur SMTP, par exemple:
  "smtp://user:password@server.domain:port",
* "MAILER_NAME" => nom qui apparaîtra comme expéditeur des e-mails,
* "MAILER_EMAIL" => adresse d'expédition des e-mails et identifiant du premier
  compte d'utilisateur créé en vue de l'administration,
* "SECURE_SCHEME" => 'https' ou 'http' selon le degré de sécurité souhaité.

Compilez l'environnement pour la production:

    composer dump-env prod

### Base de données et fixtures

Créez la base de données et migrez-en le schéma:

    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate

Créez ensuite les données d'exemple:

    php bin/console doctrine:fixtures:load

### Compte d'administration

Le premier compte d'utilisateur, qui devient théoriquement le compte
d'administration du site, est créé parmi les données d'exemple. Celles-ci
sont donc nécessaires pour une première connexion.

Pour vous connecter au site pour la première fois, utilisez l'adresse e-mail
qui est renseignée par la variable "MAILER_EMAIL" dans votre environnement.
Le mot de passe par défaut est "password".

Il vous reste à vous rendre dans le menu d'administration pour changer le
mot de passe d'administration et ajouter d'autre utilisateurs.

### Données d'exemple

A part le compte d'administrateur, le reste des fixtures sont des donnée
suggérées qui ont, pour la plupart, été générées au hasard. Il ne vous reste
plus qu'à les explorer, les adapter, et supprimer ce qui ne vous est pas utile.

## Renseignements et contribution

Besoin de renseignements? Besoin de voir si cette application peut vous servir
à vous aussi? Pas de problème, nous sommes là, nous vous répondrons volontiers!
