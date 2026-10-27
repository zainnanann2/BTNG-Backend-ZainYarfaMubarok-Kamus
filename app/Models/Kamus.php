<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamus extends Model
{
    use HasFactory;

    /**
     * Laravel akan menebak nama tabel "kamuses" dari nama model "Kamus",
     * sedangkan nama tabel yang dipakai adalah "kamus".
     */
    protected $table = 'kamus';

    /**
     * Tingkat kesopanan yang dikenal.
     *
     * "netral" dipakai untuk bahasa daerah yang tidak mengenal undak-usuk
     * (tingkatan tutur), sehingga kolom ini tetap wajib terisi.
     *
     * Jawa memakai ngoko / ngoko_alus / krama_madya / krama_inggil.
     * Sunda memakai loma (akrab) / lemes (halus).
     */
    public const TINGKAT_KESOPANAN = [
        'ngoko',
        'ngoko_alus',
        'krama_madya',
        'krama_inggil',
        'loma',
        'lemes',
        'netral',
    ];

    /**
     * Label Indonesia untuk tiap tingkat, dipakai pada dokumentasi dan statistik.
     */
    public const LABEL_TINGKAT = [
        'ngoko' => 'Ngoko (akrab, untuk teman sebaya)',
        'ngoko_alus' => 'Ngoko alus (ngoko yang menghormati lawan bicara)',
        'krama_madya' => 'Krama madya (sopan, untuk percakapan umum)',
        'krama_inggil' => 'Krama inggil (halus, untuk orang yang dihormati)',
        'loma' => 'Loma (Sunda: akrab)',
        'lemes' => 'Lemes (Sunda: halus)',
        'netral' => 'Netral (bahasa tanpa tingkatan tutur)',
    ];

    protected $fillable = [
        'kata',
        'bahasa_daerah',
        'arti_indonesia',
        'contoh_kalimat',
        'tingkat_kesopanan',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Pencarian bebas pada kata, arti, dan contoh kalimat.
     */
    public function scopeCari(Builder $query, ?string $istilah): Builder
    {
        if (blank($istilah)) {
            return $query;
        }

        $pola = '%'.$istilah.'%';

        return $query->where(function (Builder $q) use ($pola) {
            $q->where('kata', 'like', $pola)
                ->orWhere('arti_indonesia', 'like', $pola)
                ->orWhere('contoh_kalimat', 'like', $pola);
        });
    }

    public function scopeBahasa(Builder $query, ?string $bahasa): Builder
    {
        return blank($bahasa) ? $query : $query->where('bahasa_daerah', $bahasa);
    }

    public function scopeTingkat(Builder $query, ?string $tingkat): Builder
    {
        return blank($tingkat) ? $query : $query->where('tingkat_kesopanan', $tingkat);
    }
}
