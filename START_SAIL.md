# Setup con Laravel Sail

## Indice

- [1. Avvio rapido](#avvio-rapido)
- [2. Installazione Docker](#installazione-docker)
- [3. Configurazione delle porte](#configurazione-delle-porte)
- [4. Stack Docker](#stack-docker)
- [5. Avvio ambiente](#avvio-ambiente)
- [6. Database in Sail](#database-in-sail)
- [7. Comandi iniziali](#comandi-iniziali)
- [8. Comandi Sail utili](#comandi-sail-utili)
- [9. Verifica queue e scheduler](#verifica-queue-e-scheduler)
- [10. Comandi custom](#comandi-custom)

## Avvio rapido

- Laravel Sail è l’ambiente consigliato (Docker) per questo progetto.
- La coda (`queue`) e lo scheduler sono già configurati in `compose.yaml`.

## Installazione Docker

- Assicurati di avere Docker installato sulla macchina prima di proseguire.
- Se non lo hai, installa:
  - **macOS / Windows**: Docker Desktop
  - **Linux**: Docker Engine + plugin Docker Compose

Link ufficiali:

- https://www.docker.com/products/docker-desktop
- https://docs.docker.com/engine/install/

Verifica che sia tutto a posto:

```bash
docker --version
docker compose version
```

## Configurazione delle porte

Sail espone porte locali usando queste variabili dell'.env:

- `APP_PORT` per l’applicazione web (`80` di default dentro il container).
- `VITE_PORT` per il dev server Vite (`5173`).
- `FORWARD_DB_PORT` per MySQL (`3306` lato host).

Se una porta è già occupata dal tuo stack locale (MAMP/WAMP/altro), impostala a mano nel `.env` prima di eseguire `sail up`.

Esempio:

```bash
APP_PORT=8081
FORWARD_DB_PORT=3307
VITE_PORT=5174
APP_URL=http://localhost:8081
```

## Stack Docker

- `laravel.test` (app HTTP)
- `laravel.queue` con `php artisan queue:work --sleep=3 --tries=3`
- `laravel.scheduler` con `php artisan schedule:work`

## Avvio ambiente

Documentazione ufficiale: https://laravel.com/docs/12.x/sail

```bash
php artisan sail:install   # solo se non ancora eseguito
./vendor/bin/sail up -d
```

Configurazione alias (facoltativa):

```bash
sudo nano .bashrc_aliases # oppure .zshrc_aliases
# Dentro nano
alias sail='./vendor/bin/sail'
# Salva e esci e ricarica il file nella shell:
source ~/.bashrc_aliases   # oppure .zshrc_aliases
```

URL:

```text
http://localhost:${APP_PORT}
```

## Database in Sail

In ambiente Sail, con la configurazione attuale, il database indicato da `DB_DATABASE` viene creato automaticamente dal container MySQL al primo avvio.
Quindi di solito non è necessario eseguire manualmente `CREATE DATABASE`.
In .env.example è spiegato come impostare le porte del database per non avere conflitti se mysql è già in esecuzione su altre porte.


## Comandi iniziali

> Ricorda: per Sail usa `sail` per tutti i comandi `composer` / `artisan`.

```bash
sail composer install
sail composer dump-autoload # IMPORTANTE PER IL FUNZIONAMENTO DEL PROGETTO
sail artisan key:generate
sail artisan storage:link
sail artisan optimize:clear
sail artisan migrate --seed
sail artisan db:seed # Se si vuole avere più dati di prova
```

Poi avvia i servizi con:

```bash
sail up -d
```

## Comandi Sail utili

```bash
sail up -d
sail restart
sail down
sail down -v          # rimuove anche i volumi (incluso DB)
sail ps
sail logs -f laravel.queue laravel.scheduler
sail artisan <comando>
sail composer <comando>
sail npm <comando>
```

## Verifica queue e scheduler

```bash
sail ps
sail logs -f laravel.queue laravel.scheduler
```

## Comandi custom

> In Sail usa sempre `sail artisan` per eseguire i comandi.

```bash
sail artisan user:create
sail artisan admin:create
sail artisan expire-holds --chunk=500
```

- `user:create`  
  Crea un utente normale interattivamente (`Nome`, `Email`, `Password`, `Conferma password`).

- `admin:create`  
  Crea un utente con `is_admin = true` con lo stesso flusso interattivo.

- `expire-holds [--chunk=<numero>]`  
  Esegue la pulizia delle hold scadute subito da terminale.

  - Default: `--chunk=500`
  - Esempio: `sail artisan expire-holds --chunk=200`