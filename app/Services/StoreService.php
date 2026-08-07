<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;

class StoreService
{
    public function listStores(): Collection
    {
        return Store::query()
            ->orderBy('name')
            ->get();
    }

    public function listActiveStores(): Collection
    {
        return Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
