<?php

namespace App\Observers;

use App\Models\AssetStatusHistory;
use App\Models\JourneyAsset;

class JourneyAssetObserver
{
    public function created(JourneyAsset $journeyAsset): void
    {
        AssetStatusHistory::create([
            'journey_asset_id' => $journeyAsset->id,
            'old_status' => null,
            'new_status' => $journeyAsset->status,
            'changed_by_user_id' => auth()->id(),
            'created_at' => now(),
        ]);
    }

    public function updated(JourneyAsset $journeyAsset): void
    {
        if (! $journeyAsset->wasChanged('status')) {
            return;
        }

        AssetStatusHistory::create([
            'journey_asset_id' => $journeyAsset->id,
            'old_status' => $journeyAsset->getOriginal('status'),
            'new_status' => $journeyAsset->status,
            'changed_by_user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        $journeyAsset->status_changed_at = now();
        $journeyAsset->saveQuietly();
    }
}
