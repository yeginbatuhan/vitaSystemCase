<?php

use App\Exceptions\InvalidCredentialsException;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('api/*') ? null : '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (InvalidCredentialsException $e) {
            return ApiResponse::error($e->getMessage(), 401);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return ApiResponse::error('Bu işlem için giriş yapmanız gerekiyor.', 401);
        });

        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::error('Gönderilen veriler geçersiz.', 422, $e->errors());
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return ApiResponse::error('Kayıt bulunamadı.', 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('İstenen kaynak bulunamadı.', 404);
            }

            return null;
        });
    })->create();
