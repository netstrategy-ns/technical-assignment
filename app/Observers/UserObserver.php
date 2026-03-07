<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{

    // Aggiorna e invalida le sessioni dell'utente quando cambia il ruolo admin
    public function updated(User $user): void
    {
        if (! $user->wasChanged('is_admin')) {
            return;
        }

        $eraAdmin = $user->getOriginal('is_admin') === true;
        $oraAdmin = $user->is_admin === true;

        if ($eraAdmin && ! $oraAdmin && config('session.driver') === 'database') {
            $this->invalidateSessionsForUser($user->id);
        }
    }

    private function invalidateSessionsForUser(int $userId): void
    {
        $table = config('session.table', 'sessions');
        DB::connection(config('session.connection'))->table($table)->where('user_id', $userId)->delete();
    }
}
