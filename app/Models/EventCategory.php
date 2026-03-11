<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasAdminTable;
use App\Models\Scopes\EventCategory\FilterScopes;
use App\Models\Scopes\EventCategory\SortScopes;
use App\Support\Tables\EventCategoryTableColumns;

class EventCategory extends Model
{
    /** @use HasFactory<\Database\Factories\EventCategoryFactory> */
    use HasFactory;
    use HasAdminTable;
    use FilterScopes;
    use SortScopes;

    
    protected $fillable = [
        'name',
        'slug',
    ];
    

    // Gestione colonne tabella dashboard admin
    public static function tableColumns(): array
    {
        return EventCategoryTableColumns::columns();
    }

    /**
     * ------------------------------------------------------------
     * RELATIONS
     * ------------------------------------------------------------
     */

    // Relazione eventi
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    
}
