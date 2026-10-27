<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KamusController;
use App\Http\Controllers\Api\StatistikController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute API Kamus Bahasa Daerah
|--------------------------------------------------------------------------
|
| Versi API ditaruh di prefix /api/v1 agar perubahan besar di masa depan
| bisa hidup berdampingan tanpa merusak klien lama. Prefix /api sendiri
| sudah ditambahkan otomatis oleh Laravel (bootstrap/app.php).
|
*/

Route::prefix('v1')->group(function () {
    // Kesehatan layanan: dipakai monitoring untuk memastikan app hidup.
    Route::get('health', fn (): JsonResponse => response()->json([
        'message' => 'API Kamus Bahasa Daerah berjalan',
        'data' => [
            'status' => 'ok',
            'versi' => '1.0.0',
            'waktu' => now()->toIso8601String(),
        ],
    ]));

    /*
     * Autentikasi.
     *
     * Sengaja didaftarkan SEBELUM rute di bawahnya, dan tanpa middleware,
     * agar register/login bisa diakses tanpa token.
     */
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    /*
     * CRUD katalog kamus.
     *
     * Rute didaftarkan satu per satu, bukan lewat apiResource, karena dua
     * alasan:
     *   1. apiResource menggabungkan PUT dan PATCH menjadi satu rute
     *      (PUT|PATCH) sehingga Scramble hanya mendokumentasikan PUT.
     *   2. Pluralizer Laravel memotong "kamus" menjadi "kamu", jadi nama
     *      parameter bawaannya {kamu} dan route model binding pada
     *      `Kamus $kamus` tidak akan pernah terisi.
     */
    Route::get('kamus', [KamusController::class, 'index'])->name('kamus.index');
    Route::post('kamus', [KamusController::class, 'store'])->name('kamus.store');
    Route::get('kamus/{kamus}', [KamusController::class, 'show'])->name('kamus.show');
    Route::put('kamus/{kamus}', [KamusController::class, 'update'])->name('kamus.update');
    Route::patch('kamus/{kamus}', [KamusController::class, 'update'])->name('kamus.patch');
    Route::delete('kamus/{kamus}', [KamusController::class, 'destroy'])->name('kamus.destroy');

    Route::get('statistik', [StatistikController::class, 'index']);
});
