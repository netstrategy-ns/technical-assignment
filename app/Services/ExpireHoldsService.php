<?php

namespace App\Services;

use App\Enums\HoldStatusEnum;
use App\Models\Hold;

class ExpireHoldsService
{
    public function run(int $chunkSize = 500): int
    {
        $expiredCount = 0;

        Hold::query()
            ->select('id')
            ->active()
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(max(1, $chunkSize), function ($holds) use (&$expiredCount): void {
                $ids = $holds->pluck('id');

                if ($ids->isEmpty()) {
                    return;
                }

                Hold::query()
                    ->whereKey($ids)
                    ->update([
                        'status' => HoldStatusEnum::EXPIRED->value,
                    ]);

                $expiredCount += $ids->count();
            });

        return $expiredCount;
    }
}
