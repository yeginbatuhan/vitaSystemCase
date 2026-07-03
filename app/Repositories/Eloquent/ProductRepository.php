<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function search(string $term, int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->with('manufacturer')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function findByUuids(array $uuids): Collection
    {
        return Product::query()
            ->with('manufacturer')
            ->whereIn('uuid', $uuids)
            ->get();
    }
}
