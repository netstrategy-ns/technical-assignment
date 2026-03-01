# START — Avvio del Progetto da Zero

## 1) Prerequisiti
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL

## 2) Clonare il progetto
```bash
git clone <IL_TUO_FORK_URL>
cd technical-assignment
```

## 3) Installare le dipendenze
```bash
composer install
npm install
```

## 4) Configurare l'ambiente
```bash
cp .env.example .env
php artisan key:generate
```

Modifica il file `.env` con le credenziali MySQL:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=il_tuo_db
DB_USERNAME=il_tuo_utente
DB_PASSWORD=la_tua_password
```

Se il database non esiste, `php artisan migrate` può chiedere se vuoi crearlo
(è necessario che l’utente MySQL abbia i permessi). In alternativa puoi crearlo a mano:
```sql
CREATE DATABASE il_tuo_db;
```

## 5) Eseguire migrations e seeders
```bash
php artisan migrate --seed
```

### Utente di test
Dopo il seeding, è disponibile un utente di test:
- Email: `test@example.com`
- Password: `password`

## 6) Avviare l'applicazione (dev)
In terminali separati:
```bash
php artisan serve
npm run dev
php artisan schedule:work
```

Poi apri:
```
http://127.0.0.1:8000
```

## 7) Comandi utili
```bash
php artisan holds:expire
php artisan queue:process-events
php artisan queue:expire-entries
```

## 8) Note sul funzionamento (riassunto)
- La lista eventi e il dettaglio usano Inertia (pagine server‑driven).
- Le azioni (`holds`, `checkout`, `queue`) usano `fetch` con CSRF.
- La disponibilità tiene conto di biglietti venduti e hold attivi non scaduti.
- La scadenza degli hold è gestita da scheduler (`holds:expire`).
- La coda usa polling nel frontend (aggiornamento automatico ogni 15s) e uno scheduler nel backend eseguito ogni minuto per gestire abilitazioni e scadenze.
- Il checkout è per evento e supporta idempotenza tramite `Idempotency-Key`, evitando doppie conferme in caso di retry.
