<?php

namespace App\Http\Controllers\Api;

use App\Actions\Products\SearchProductsAction;
use App\Data\Product\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSearchRequest;
use Spatie\LaravelData\DataCollection;

class ProductController extends Controller
{
    public function index(ProductSearchRequest $request, SearchProductsAction $action): DataCollection
    {
        $perPage = (int) $request->validated('per_page', 15);

        $paginator = $action($request->validated('search'), $perPage);

        $products = collect($paginator->items())
            ->map(fn ($product) => ProductData::fromModel($product));

        return ProductData::collect($products, DataCollection::class)->wrap('data');
    }
}
