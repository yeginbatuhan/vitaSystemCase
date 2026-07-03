<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function search(string $term, int $perPage): LengthAwarePaginator;

    public function findByUuids(array $uuids): Collection;
}
