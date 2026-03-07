<?php

namespace Database\Seeders;

use App\Models\VenueType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VenueTypeSeeder extends Seeder
{
     /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venueTypes = [
            'Stadio',
            'Teatro',
            'Arena',
            'Locale',
            'Aperto',
        ];

        foreach ($venueTypes as $name) {
            VenueType::firstOrCreate(
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]
            );
        }
    }
}
