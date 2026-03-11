# Setup con MAMP/WAMP

## Indice

- [1. Installazione](#installazione)
- [2. Configurazione `.env`](#configurazione-env)
- [3. Creare il database (obbligatorio prima di migrate/db:seed)](#creare-il-database)
- [3.1 Terminale](#terminale)
- [3.2 phpMyAdmin](#phpmyadmin)
- [4. Avvio servizi (locale)](#avvio-servizi-locale)
- [5. Verifiche rapide](#verifiche-rapide)
- [6. Comandi custom](#comandi-custom)

## Installazione

1. Installa MAMP da https://www.mamp.info
2. Avvia Apache + MySQL.
3. Posiziona il progetto in:
   - MAMP: `~/Applications/MAMP/htdocs/tickme-to-event`
   - WAMP: `C:\wamp64\www\tickme-to-event`


## Creare il database

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

```bash
APP_NAME="Tickme to Event"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8888

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=root
DB_PASSWORD=root

QUEUE_CONNECTION=database
```
> Se hai creato un utente diverso da root, aggiorna `DB_USERNAME` e `DB_PASSWORD`.

### Terminale

```bash
mysql -u root -p -h 127.0.0.1 -P 3306 -e "CREATE DATABASE IF NOT EXISTS tickme_to_event CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

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

Se usi una versione PHP diversa in shell, punta a PHP MAMP:

```bash
export PHP_BIN=/Applications/MAMP/bin/php/php8.2.x/bin/php
$PHP_BIN -v
$PHP_BIN artisan migrate
```

### phpMyAdmin

1. Apri phpMyAdmin (es. `http://localhost/phpmyadmin`).
2. Crea nuovo database `tickme_to_event` con collation `utf8mb4_unicode_ci`.

## Avvio servizi (locale)

```bash
php artisan optimize:clear
php artisan schedule:work
php artisan queue:listen --tries=1 --timeout=0
php artisan queue:work --sleep=3 --tries=3
```

## Verifiche rapide

```bash
php artisan queue:failed
php artisan schedule:list
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
