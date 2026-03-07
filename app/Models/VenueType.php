<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VenueType extends Model
{
    /** @use HasFactory<\Database\Factories\VenueTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * ------------------------------------------------------------
     * RELATTIONS
     * ------------------------------------------------------------
     */

    // Relazione eventi
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
