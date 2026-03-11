# Guida Setup completo - Tickme to Event

## 1. Prerequisiti

- Git
- PHP 8.2 (o superiore)
- Composer 2
- Node.js 22 + npm 10
- Laravel 12 / Inertia.js 2

## 2. Clona repository e branch

```bash
git clone https://github.com/tuo-utente/tickme-to-event.git
```

oppure

```bash
git clone git@github.com:tuo-utente/tickme-to-event.git
```

Spostati in cartella:

```bash
cd tickme-to-event
```

Se serve un branch:

```bash
git checkout -b feature/your-name
```

## 3. Comandi comuni iniziali (valido per tutti gli ambienti)

Esegui sempre:

```bash
cp .env.example .env
npm install
php artisan key:generate
```

> Il passaggio `composer dump-autoload`, `migrate` e `db:seed` dipende dall'ambiente scelto.
> Apri la guida dedicata e segui l'ordine corretto (DB -> dump-autoload -> migrate -> db:seed).

## 4. Scegli l'ambiente

Per lo sviluppo ho utilizzato Sail (consigliato), ma è possibile utilizzare anche Herd o MAMP.

- [START_SAIL.md](./START_SAIL.md): setup con Docker + Laravel Sail
- [START_HERD.md](./START_HERD.md): setup con Herd
- [START_MAMP.md](./START_MAMP.md): setup con MAMP / WAMP

## 5. Note rapide

- `README.md` rimane il documento generale del repository.
- Le tre guide dedicate contengono i passaggi specifici di:
  - setup ambiente
  - creazione DB
  - avvio servizi locali
  - comandi di verifica queue/scheduler
