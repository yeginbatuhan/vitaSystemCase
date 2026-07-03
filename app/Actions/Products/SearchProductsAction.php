<?php

namespace App\Actions\Products;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchProductsAction
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {
    }

    public function __invoke(string $term, int $perPage): LengthAwarePaginator
    {
        return $this->products->search($term, $perPage);
    }
}
