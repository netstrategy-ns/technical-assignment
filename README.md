# 📌 Istruzioni

1. Effettua il fork di questo repository.
2. Clona il tuo fork in locale.
3. Crea un nuovo branch:

   ```bash
   git checkout -b feature/your-name
   ```

4. Implementa la soluzione.
5. Esegui i commit utilizzando messaggi significativi.
6. Effettua il push del branch sul tuo fork.
7. Apri una Pull Request verso il branch `main` del repository originale.

❗ **Non effettuare il push direttamente sul repository originale.**

# Prova tecnica — Portale Ticketing (Eventi & Biglietti)

## Contesto

Realizzare un portale di compravendita di biglietti per eventi (ispirato a piattaforme di ticketing), dove gli utenti possono registrarsi/accedere (autenticazione Laravel già presente), cercare e filtrare eventi, entrare nel dettaglio evento e acquistare biglietti tramite un flusso di prenotazione temporanea.

Il progetto viene fornito come scaffolding vuoto con **Laravel + Vue**.

---

## Requisiti principali

### 1) Catalogo eventi

Implementare:

- **Home**: mostra di default gli eventi **in evidenza**
- **Lista eventi**: ricerca e filtri (vedi sezione dedicata)
- **Dettaglio evento**: informazioni complete e selezione biglietti

Ogni evento deve includere almeno:

- titolo
- descrizione
- categoria/tipo (es. concerti, sport, teatro, ecc.)
- data/ora inizio (e fine se utile)
- luogo (testuale, minimo città)
- immagine (può essere placeholder)
- flag **in evidenza**
- data/ora **inizio vendita**
- configurazione per il **sistema di coda** (vedi sezione **Coda**)

---

### 2) Ricerca e filtri

Nella lista eventi implementare almeno:

- ricerca testuale su titolo e/o descrizione
- filtro per categoria/tipo
- filtro per range date (da/a)
- filtro per luogo/città (anche solo testo)
- filtro “in evidenza” (opzionale se già presente in home, ma gradito)

Ordinamenti consigliati (almeno uno):

- data più vicina
- eventi più recenti
- eventi in evidenza prima (se non già separati)

---

## Ticketing & disponibilità

### 3) Tipologie biglietto

Per ogni evento devono esistere una o più tipologie di biglietto (ticket type), con:

- nome (es. Standard, VIP)
- prezzo
- **quantità totale disponibile**
- limite per utente (es. max 4 per ordine) — gradito

> Requisito fondamentale: deve essere **impossibile** vendere più biglietti del massimo disponibile, anche in condizioni di concorrenza.

---

## Flusso di acquisto: hold di 10 minuti (carrello)

### 4) Vendita non iniziata

Se la vendita non è iniziata:

- nel dettaglio evento deve apparire un avviso chiaro
- non deve essere possibile avviare prenotazioni/hold o checkout

---

### 5) Aggiunta al carrello (HOLD)

Quando un utente seleziona una quantità di biglietti (per una tipologia):

- viene creata una **prenotazione temporanea** (hold/reservation)
- la prenotazione è legata all’utente
- la prenotazione ha una scadenza: **10 minuti** da quando viene creata/aggiornata
- per tutta la durata dell’hold, quei biglietti devono risultare **non disponibili** per altri utenti

La disponibilità deve tenere conto di:

- biglietti venduti
- biglietti in hold non scaduti

Formula di riferimento:

**Disponibili = Totale - Venduti - InHoldValidi**

---

### 6) Scadenza hold e rilascio biglietti

Se l’utente non completa l’acquisto entro 10 minuti:

- la prenotazione scade
- i biglietti tornano disponibili per tutti

L’applicazione deve garantire che lo stato scaduto venga gestito in modo affidabile, senza richiedere interventi manuali.

---

### 7) Checkout simulato

Non è richiesto alcun sistema di pagamento reale.

Il checkout deve:

- verificare che le prenotazioni (hold) dell’utente siano **ancora valide**
- creare un ordine confermato
- generare i biglietti/righe ordine assegnati all’utente
- chiudere/svuotare il carrello

---

## Sistema di coda

### 8) Comportamento generale

Per alcuni eventi, in base a un valore/configurazione dell’evento, deve essere attivo un **sistema di coda** che:

- regola l’accesso alle operazioni critiche (almeno: avvio hold e/o checkout)
- impedisce che un numero elevato di utenti esegua simultaneamente operazioni che possono sovraccaricare il sistema o portare a race condition
- gestisce lo stato dell’utente nella coda (es. in attesa / abilitato / scaduto / completato)

> Il candidato è libero di scegliere modalità e strategia per implementare la coda (l’importante è che sia efficace, coerente e testabile).

---

## Area utente

### 9) Account: ordini e biglietti

Nell’area account l’utente deve poter vedere:

- elenco ordini effettuati
- dettaglio ordine (gradito)
- biglietti acquistati (evento, tipologia, quantità, data acquisto, stato ordine)

---

## Requisiti tecnici

### 10) Stack e vincoli

- Backend: Laravel (fornito)
- Frontend: Vue (fornito)
- Database: **MySQL**
- Autenticazione: già presente nello scaffolding (riutilizzarla)

Vincoli:

- evitare Redis e strumenti esterni equivalenti per locking/queue/cache
- la logica deve essere affidabile anche con richieste concorrenti
- non integrare pagamenti reali

---

### 11) Concorrenza e integrità

Le operazioni di hold e checkout devono essere robuste in concorrenza:

- adottare una strategia che impedisca overbooking e doppie conferme
- gestire correttamente scadenze hold e rilasci

---

## Deliverables

### 12) Obbligatori

- implementazione completa backend + frontend
- migrazioni e seeders per generare dati demo (eventi e tipologie biglietto)

---

## START.md (obbligatorio)

Creare un file `START.md` nella root del progetto che contenga **TUTTE** le istruzioni per avviare il progetto da zero, includendo anche i passaggi più banali, ad esempio:

- comandi di installazione dipendenze backend:
  - `composer install`
  - `php artisan key:generate`
- setup database:
  - migrazioni
  - seed
- comandi frontend:
  - `npm install`
  - `npm run dev` (o equivalente)
- avvio applicazione (comandi completi)

Obiettivo: chi valuta deve poter eseguire la prova senza dover “intuire” nessun passaggio.

---

## Bonus (facoltativi)

- UX curata per lista/filtri/carrello/coda
- limiti per utente (max biglietti per ordine)
- gestione idempotenza checkout (evitare doppie conferme)

---

## Cosa verrà valutato

- correttezza e robustezza del flusso hold (10 minuti) e rilascio biglietti
- affidabilità in concorrenza (no overbooking)
- implementazione efficace del sistema di coda
- qualità del codice e architettura (separazione responsabilità)
- chiarezza e completezza di `START.md`
