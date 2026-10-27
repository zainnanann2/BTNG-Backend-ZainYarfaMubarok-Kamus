<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Semua error di jalur API dikembalikan sebagai JSON dengan amplop
         * yang sama seperti respons sukses: { message, data, [errors] }.
         *
         * Tanpa ini, Laravel bisa membalas halaman HTML (pada 404) atau
         * { message } saja (pada 401), sehingga klien harus menangani
         * beberapa bentuk respons sekaligus.
         */
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage(),
                'data' => null,
                'errors' => $e->errors(),
            ], $e->status);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            /*
             * AuthenticationException tidak mewarisi HttpExceptionInterface,
             * jadi ditangani terpisah agar 401 tetap memakai amplop yang sama.
             */
            return response()->json([
                'message' => 'Tidak terautentikasi. Sertakan header Authorization: Bearer <token>.',
                'data' => null,
            ], 401);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $sebelumnya = $e->getPrevious();

            $pesan = $sebelumnya instanceof ModelNotFoundException
                ? 'Data tidak ditemukan.'
                : 'Endpoint atau data tidak ditemukan.';

            return response()->json([
                'message' => $pesan,
                'data' => null,
            ], 404);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Permintaan gagal diproses.',
                'data' => null,
            ], $e->getStatusCode(), $e->getHeaders());
        });
    })->create();
