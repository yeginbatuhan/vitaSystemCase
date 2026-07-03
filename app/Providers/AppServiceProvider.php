<?php

namespace App\Providers;

use App\Http\Controllers\Api\OrderController;
use App\Support\Scramble\DataToSchema;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Scramble::registerExtension(DataToSchema::class);

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(SecurityScheme::http('bearer', 'JWT'));
            })
            ->withOperationTransformers(function (Operation $operation, RouteInfo $routeInfo) {
                if ($routeInfo->className() === OrderController::class && $routeInfo->methodName() === 'store') {
                    foreach ($operation->responses as $response) {
                        if ($response instanceof Response && $response->code === 200) {
                            $response->setCode(201);
                        }
                    }
                }
            });
    }
}
