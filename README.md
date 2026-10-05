# CIMS API

## NOTE

### For this project to work you'll need the Frontend:

## App Setup

composer install

cp .env.example .env

php artisan cache:clear

composer dump-autoload

php artisan key:generate

composer require laravel/breeze --dev

php artisan breeze:install (FOR THIS SELECT THE API INSTALL)

php artisan serve

```

Create a DATABASE. Make sure the DB_DATABASE in the .env is the same and then run this command
```

php artisan migrate

```

You should be good to go!
```
