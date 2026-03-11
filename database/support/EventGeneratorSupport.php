<?php

namespace Database\Support;

use App\Models\EventCategory;
use App\Models\VenueType;
use Illuminate\Support\Arr;


/**
 * File di supporto generato con AI per dare consistenza ai titoli e location degli eventi.
 */

class EventGeneratorSupport
{
    private const LEAGUES = [
        'Serie A Tim 2025/2026',
        'Serie A Tim 2026/2027',
        'Serie B 2025/2026',
        'Serie B 2026/2027',
        'Coppa Italia 2025/2026',
        'Coppa Italia 2026/2027',
        'Supercoppa Italiana 2026',
    ];

    private const FOOTBALL_TEAMS = [
        'Inter',
        'Milan',
        'Juventus',
        'Roma',
        'Lazio',
        'Napoli',
        'Atalanta',
        'Fiorentina',
        'Bologna',
        'Torino',
        'Genoa',
        'Cagliari',
        'Udinese',
        'Hellas Verona',
        'Lecce',
        'Empoli',
        'Monza',
        'Frosinone',
        'Como',
        'Venezia',
        'Palermo',
        'Cremonese',
        'Brescia',
        'Parma',
        'Bari',
        'Sampdoria',
    ];

    private const ARTISTS = [
        'Vasco Rossi',
        'Laura Pausini',
        'Eros Ramazzotti',
        'Jovanotti',
        'Tiziano Ferro',
        'Marco Mengoni',
        'Ultimo',
        'Madame',
        'BLANCO',
        'Måneskin',
        'Coldplay',
        'Ed Sheeran',
        'Taylor Swift',
        'Bruce Springsteen',
        'U2',
        'Sting',
        'Elton John',
        'Andrea Bocelli',
        'Il Volo',
        'Negramaro',
        'Cesare Cremonini',
        'Lorenzo Fragola',
        'Alessandra Amoroso',
        'Emma',
        'Giorgia',
        'Caparezza',
        'Subsonica',
        'Ligabue',
        'Biagio Antonacci',
    ];

    private const THEATRE_SHOWS = [
        'Sei personaggi in cerca d\'autore',
        'Così è (se vi pare)',
        'Il berretto a sonagli',
        'Enrico IV',
        'Amleti',
        'Romeo e Giulietta',
        'Sogno di una notte di mezza estate',
        'Otello',
        'La dodicesima notte',
        'Cyrano de Bergerac',
        'Le troiane',
        'Medea',
        'Edipo re',
        'Antigone',
        'Arlecchino servitore di due padroni',
        'La locandiera',
        'Il bugiardo',
        'La vita che ti diedi',
        'Questi fantasmi!',
        'Filumena Marturano',
        'Napoli milionaria!',
        'Il sindaco del rione Sanità',
        'Le voci di dentro',
        'La cantatrice calva',
        'Rhinocéros',
        'La lezione',
        'Aspettando Godot',
        'Finale di partita',
        'Krapp l\'ultimo nastro',
        'Se devi dire una buglia dilla grossa',
    ];

    private const CINEMA_EVENTS = [
        'Rassegna Cinema d\'Autore 2026',
        'Festival del Cinema di Roma 2026',
        'Milano Film Festival 2026',
        'Torino Film Festival 2026',
        'Venezia Classici 2026',
        'Cinema sotto le stelle 2026',
        'Retrospettiva Fellini 2026',
        'Omaggio a Nanni Moretti 2026',
        'Cinema italiano anni \'80 2026',
        'Notte degli Oscar 2026',
    ];

    private const OTHER_EVENTS = [
        'Convention Tech 2026',
        'Salone del Libro 2026',
        'Fiera dell\'Artigianato 2026',
        'Festival della Letteratura 2026',
        'Giornata dello Sport 2026',
        'Open Day 2026',
        'Meetup Developer 2026',
        'Workshop Creativo 2026',
    ];

    private const CITIES = [
        'Milano',
        'Roma',
        'Napoli',
        'Torino',
        'Firenze',
        'Bologna',
        'Genova',
        'Palermo',
        'Venezia',
        'Verona',
        'Bari',
        'Catania',
        'Padova',
        'Trieste',
        'Brescia',
        'Parma',
        'Modena',
        'Reggio Emilia',
        'Livorno',
        'Cagliari',
        'Foggia',
        'Ravenna',
        'Ferrara',
        'Rimini',
        'Siracusa',
    ];

    private const VENUE_NAMES = [
        'Stadio' => [
            'Stadio San Siro',
            'Stadio Olimpico',
            'Stadio Diego Armando Maradona',
            'Stadio Artemio Franchi',
            'Stadio Renato Dall\'Ara',
            'Stadio Luigi Ferraris',
            'Stadio Renzo Barbera',
            'Stadio Friuli',
            'Stadio Marcantonio Bentegodi',
            'Stadio Via del Mare',
            'Stadio Carlo Castellani',
            'Stadio Arechi',
            'Stadio San Paolo',
            'Palazzetto dello Sport',
            'PalaAlpitour',
        ],
        'Teatro' => [
            'Teatro alla Scala',
            'Teatro La Fenice',
            'Teatro San Carlo',
            'Teatro Massimo',
            'Teatro Regio di Torino',
            'Teatro Regio di Parma',
            'Teatro Comunale di Bologna',
            'Teatro Eden',
            'Teatro Olimpico',
            'Teatro Romano di Verona',
            'Teatro Argentina',
            'Teatro Bellini',
            'Teatro Petruzzelli',
            'Teatro Verdi',
            'Teatro Grande',
        ],
        'Arena' => [
            'Arena di Verona',
            'Arena Flegrea',
            'Arena Sanremo',
            'RCF Arena',
            'Arena del Sole',
        ],
        'Locale' => [
            'Alcatraz',
            'Forum di Assago',
            'Mediolanum Forum',
            'Unipol Arena',
            'Palazzo dello Sport',
            'PalaLottomatica',
            'Fiera Milano',
            'Palazzo dei Congressi',
            'Teatro Tendastrisce',
            'Circolo degli Illuminati',
        ],
        'Aperto' => [
            'Villa Erba',
            'Ippodromo San Siro',
            'Parco Sempione',
            'Arena Civica',
            'Villa Manin',
            'Riserva naturale',
            'Parco della Musica',
            'Piazza del Duomo',
        ],
    ];


    public static function getRandomCity(): string
    {
        return Arr::random(self::CITIES);
    }

    public static function getRandomVenueName(VenueType $venueType): string
    {
        $names = self::VENUE_NAMES[$venueType->name] ?? ['Luogo'];
        return Arr::random($names);
    }

    public static function getLocation(VenueType $venueType, ?string $city = null): string
    {
        $venueName = self::getRandomVenueName($venueType);
        $city = $city ?? self::getRandomCity();
        $address = fake()->streetAddress();
        return "{$venueName}, {$city} - {$address}";
    }

    public static function getRandomLocation(): string
    {
        $venueTypeNames = array_keys(self::VENUE_NAMES);
        $name = Arr::random($venueTypeNames);
        $venueName = Arr::random(self::VENUE_NAMES[$name]);
        $city = self::getRandomCity();
        $address = fake()->streetAddress();
        return "{$venueName}, {$city} - {$address}";
    }

    public static function generateWithCity(EventCategory $category, VenueType $venueType, ?int $year = null): array
    {
        $year = $year ?? (int) now()->format('Y');
        $categoryName = $category->name;
        $venueName = $venueType->name;

        return match (strtolower($categoryName)) {
            'sport' => self::sportTitleWithCity($venueName, $year),
            'concerti' => self::concertTitleWithCity($venueName, $year),
            'teatro' => self::theatreTitleWithCity($venueName, $year),
            'cinema' => self::cinemaTitleWithCity($venueName, $year),
            'altro' => self::otherTitleWithCity($venueName, $year),
            default => self::fallbackTitleWithCity($categoryName, $venueName, $year),
        };
    }

    public static function generate(EventCategory $category, VenueType $venueType, ?int $year = null): string
    {
        return self::generateWithCity($category, $venueType, $year)['title'];
    }


    private static function sportTitleWithCity(string $venueName, int $year): array
    {
        $league = Arr::random(self::LEAGUES);
        $home = Arr::random(self::FOOTBALL_TEAMS);
        $others = array_values(array_diff(self::FOOTBALL_TEAMS, [$home]));
        $away = Arr::random($others);
        $city = Arr::random(self::CITIES); // lo sport non ha città nel titolo, ma serve per la location
        return ['title' => "{$league} - {$home} - {$away}", 'city' => $city];
    }

    private static function concertTitleWithCity(string $venueName, int $year): array
    {
        $artist = Arr::random(self::ARTISTS);
        $city = Arr::random(self::CITIES);
        return ['title' => "Tour {$year} - {$artist} - {$city}", 'city' => $city];
    }


    private static function theatreTitleWithCity(string $venueName, int $year): array
    {
        $show = Arr::random(self::THEATRE_SHOWS);
        $city = Arr::random(self::CITIES);
        return ['title' => "Stagione Teatrale {$year} - {$show} - {$city}", 'city' => $city];
    }

    private static function cinemaTitleWithCity(string $venueName, int $year): array
    {
        $event = Arr::random(self::CINEMA_EVENTS);
        $city = Arr::random(self::CITIES);
        return ['title' => "{$event} - {$city}", 'city' => $city];
    }

    private static function otherTitleWithCity(string $venueName, int $year): array
    {
        $event = Arr::random(self::OTHER_EVENTS);
        $city = Arr::random(self::CITIES);
        return ['title' => "{$event} - {$city}", 'city' => $city];
    }

    private static function fallbackTitleWithCity(string $categoryName, string $venueName, int $year): array
    {
        $city = Arr::random(self::CITIES);
        return ['title' => "Evento {$categoryName} {$year} - {$city}", 'city' => $city];
    }

    /**
     * Nomi di tipologie di biglietto per tipo di venue (Stadio → Tribuna/Curva, Teatro → Platea, ecc.).
     */
    public static function ticketTypeNamesByVenueType(): array
    {
        return [
            'Stadio' => [
                'Curva Nord', 'Curva Sud', 'Tribuna', 'Tribuna VIP', 'Tribuna Centrale',
                'Parterre', 'Settore Ospiti', 'Prato', 'Gradinata',
            ],
            'Teatro' => ['Platea', 'Galleria', 'Palco', 'Palco Reale', 'Loggione'],
            'Arena' => [
                'Curva', 'Tribuna', 'Tribuna VIP', 'Parterre', 'Prato',
                'Settore Gold', 'Gradinata',
            ],
            'Locale' => ['Platea', 'Tribuna', 'VIP', 'Parterre', 'Gradinata'],
            'Aperto' => [
                'Prato', 'Tribuna', 'Tribuna VIP', 'Settore Gold', 'Gradinata',
                'Parterre', 'Standard',
            ],
            'Sala' => ['Standard', 'Premium', 'VIP'],
        ];
    }

    public static function ticketTypeNamesForVenueType(string $venueTypeName): array
    {
        $map = self::ticketTypeNamesByVenueType();
        return $map[$venueTypeName] ?? ['Standard', 'Premium', 'VIP'];
    }
}
