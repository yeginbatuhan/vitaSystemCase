<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\ListOrdersAction;
use App\Data\Order\CreateOrderData;
use App\Data\Order\OrderData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\LaravelData\DataCollection;

class OrderController extends Controller
{
    public function index(ListOrdersAction $action, Request $request): DataCollection
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);

        $paginator = $action(Auth::guard('api')->user(), $perPage);

        $orders = collect($paginator->items())
            ->map(fn ($order) => OrderData::fromModel($order));

        return OrderData::collect($orders, DataCollection::class)->wrap('data');
    }

    public function store(CreateOrderRequest $request, CreateOrderAction $action): OrderData
    {
        $order = $action(Auth::guard('api')->user(), CreateOrderData::from($request->validated()));

        return OrderData::fromModel($order);
    }
}
