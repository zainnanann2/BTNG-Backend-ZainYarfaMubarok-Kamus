<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * Halaman dokumentasi dibuka untuk umum.
         *
         * Middleware RestrictedDocsAccess milik Scramble sudah meloloskan
         * semua permintaan saat APP_ENV=local, tetapi pada produksi ia
         * memeriksa gate "viewApiDocs". Tanpa definisi berikut, dokumentasi
         * akan membalas 403 setelah aplikasi di-deploy.
         *
         * Pada proyek nyata, batasi misalnya:
         *   Gate::define('viewApiDocs', fn ($user = null) => $user?->is_admin);
         */
        Gate::define('viewApiDocs', fn ($user = null) => true);

        // Tombol "Authorize" pada UI Scramble untuk token Bearer Sanctum.
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
            $openApi->secure(
                SecurityScheme::http('bearer', 'Bearer')
            );
        });

        // Pengalihan "/" ke dokumentasi didefinisikan di routes/web.php.
        // Rute tidak boleh didaftarkan dari provider: berkas rute dimuat
        // setelah provider di-boot, sehingga rute di sini akan tertimpa.
    }
}
