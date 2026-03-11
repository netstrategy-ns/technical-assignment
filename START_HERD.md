# Setup con Herd

## Indice

- [1. Installazione Herd](#installazione-herd)
- [2. Creare il database](#creare-il-database)
- [3. Configurazione environment](#configurazione-env)
- [4. Avvio servizi locali](#avvio-servizi-locali)
- [5. Verifiche rapide](#verifiche-rapide)
- [6. Comandi custom](#comandi-custom)

## Installazione Herd

- Windows: https://herd.laravel.com/windows
- macOS: https://herd.laravel.com

Assicurati di usare PHP 8.2+.

## Creare il database

> Se il DB non esiste, `php artisan db:seed` fallisce.

```bash
mysql -u root -p # oppure utilizza sudo mysql se hai problemi di permessi
# Nella Shell Mysql:
CREATE DATABASE IF NOT EXISTS database_name;
# Se vuoi utilizzare un utente diverso da root, crea un utente:
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'localhost';
FLUSH PRIVILEGES;
```

## Configurazione `.env`

Imposta almeno:

```bash
APP_NAME="Tickme to Event"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://nome-del-progetto.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

> Se hai creato un utente diverso da root, aggiorna `DB_USERNAME` e `DB_PASSWORD`.

Poi esegui:

```bash
composer install
composer dump-autoload # IMPORTANTE PER IL FUNZIONAMENTO DEL PROGETTO
php artisan key:generate
php artisan storage:link
php artisan optimize:clear
php artisan migrate --seed
php artisan db:seed # Se si vuole avere più dati di prova
```

## Avvio servizi locali

```bash
herd link nome-del-progetto
herd secure
npm run build
php artisan optimize:clear
php artisan schedule:work
php artisan queue:work --sleep=3 --tries=3
```

> Il progetto viene servito su `http://nome-del-progetto.test` oppure `https://nome-del-progetto.test` dopo `herd secure`.

## Verifiche rapide
```bash
php artisan schedule:list
php artisan queue:failed
```

## Comandi custom

```bash
php artisan user:create
php artisan admin:create
php artisan expire-holds --chunk=500
```

- `user:create`  
  Crea un utente normale interattivamente (`Nome`, `Email`, `Password`, `Conferma password`).

- `admin:create`  
  Crea un utente con `is_admin = true` con lo stesso flusso interattivo.

- `expire-holds [--chunk=<numero>]`  
  Esegue la pulizia delle hold scadute subito da terminale.

  - Default: `--chunk=500`
  - Esempio: `php artisan expire-holds --chunk=200`
