<?php

use Illuminate\Support\Facades\Route;

/*
 * Akar aplikasi diarahkan ke dokumentasi interaktif.
 *
 * Rute ini sengaja didefinisikan di sini, bukan di AppServiceProvider.
 * Berkas rute dimuat setelah provider di-boot, sehingga rute "/" yang
 * didaftarkan dari provider akan tertimpa oleh rute di berkas ini.
 */
Route::redirect('/', '/docs/api');
