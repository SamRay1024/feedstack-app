# FeedStack

FeedStack est une application PHP pour la lecture de flux RSS réalisée grâce au framework [wlib-application](https://github.com/SamRay1024/wlib-application).

La partie front-end est bâtie grâce à [AlpineJS](https://alpinejs.dev/). Les données sont stockées dans une simple base [SQLite](https://www.sqlite.org/).

**Cette application est en cours de développement** (WIP pour les intimes) et **NE DEVRAIT PAS** être déployée en production.

![feedstack-app-preview](https://github.com/user-attachments/assets/d9d8bd89-bf5c-48af-93e0-99934a01b16a)

## Envie d'essayer ?

### Prérequis

- PHP 7.4.0
- Git
- Composer
- En option : MySQL si vous ne souhaitez pas utiliser SQLite, mais le support est peut-être incomplet (je développe principalement avec SQLite)

### Installer

Ouvrez un terminal et jouez les instruction suivantes :

```bash
git clone https://github.com/SamRay1024/feedstack-app
cd feedstack-app
composer install
php -S localhost:8000 -t public
```

Ouvrez votre navigateur préféré (Firefox, car il ne doit pas mourir :-P) pour accédez à http://localhost:8000/.

Suivez la procédure d'installation.