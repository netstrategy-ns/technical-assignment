<?php

namespace Database\Seeders;

use App\Models\EventCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Popola la tabella `event_categories` con categorie fisse (Concerti, Sport, Teatro, ecc.).
 * Non usa le Factory: i dati sono deterministici e devono essere sempre gli stessi
 * per coerenza con l’applicazione (filtri, label, slug nelle URL).
 *
 * firstOrCreate evita duplicati: se lo slug esiste già il record non viene creato.
 * Utile per rieseguire il seeder senza avere categorie doppie.
 */
class EventCategorySeeder extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Concerti'],
            ['name' => 'Sport'],
            ['name' => 'Teatro'],
            ['name' => 'Cinema'],
            ['name' => 'Altro'],
        ];

        foreach ($categories as $category) {
            EventCategory::firstOrCreate(
                ['name' => $category['name']],
                ['slug' => Str::slug($category['name'])]
            );
        }
    }
}
