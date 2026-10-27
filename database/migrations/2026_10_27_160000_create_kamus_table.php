<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamus', function (Blueprint $table) {
            $table->id();
            $table->string('kata', 100);
            $table->string('bahasa_daerah', 50);
            $table->string('arti_indonesia', 255);
            $table->text('contoh_kalimat')->nullable();
            $table->string('tingkat_kesopanan', 30)->default('netral');
            $table->timestamps();

            /*
             * Satu kata pada satu bahasa daerah boleh muncul berkali-kali
             * asalkan tingkat kesopanannya berbeda (mis. "mangan" ngoko
             * dan "dhahar" krama_inggil adalah dua entri terpisah, tetapi
             * "mangan" ngoko tidak boleh didaftarkan dua kali).
             */
            $table->unique(['kata', 'bahasa_daerah', 'tingkat_kesopanan'], 'kamus_unik');

            $table->index('bahasa_daerah');
            $table->index('tingkat_kesopanan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamus');
    }
};
