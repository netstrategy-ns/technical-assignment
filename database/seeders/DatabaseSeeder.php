<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        // Per creare utente di test utilizzare il comando: php artisan user:create
        // Per creare utente admin utilizzare il comando: php artisan admin:create

        /**
         * ------------------------------------------------------------
         *  ATTENZIONE ORDINE IMPORTANTE PER FOREIGN KEYS
         * ------------------------------------------------------------
         * 1. EventCategorySeeder
         * 2. VenueTypeSeeder
         * 3. EventSeeder
         * 4. TicketTypeSeeder
         * 5. TicketTypeQuotaSeeder (somma quote per evento ≤ event.available_tickets)
         * 6. TicketSeeder
         * ------------------------------------------------------------
         */
        $this->call([
            EventCategorySeeder::class,
            VenueTypeSeeder::class,
            EventSeeder::class,
            TicketTypeSeeder::class,
            TicketTypeQuotaSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
