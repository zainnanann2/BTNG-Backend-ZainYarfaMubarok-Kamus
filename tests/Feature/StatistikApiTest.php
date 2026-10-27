<?php

namespace Tests\Feature;

use App\Models\Kamus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatistikApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistik_meringkas_katalog(): void
    {
        Kamus::create([
            'kata' => 'mangan',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'makan',
            'contoh_kalimat' => 'Aku mangan sega.',
            'tingkat_kesopanan' => 'ngoko',
        ]);

        Kamus::create([
            'kata' => 'dhahar',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'makan (halus)',
            'contoh_kalimat' => null,
            'tingkat_kesopanan' => 'krama_inggil',
        ]);

        Kamus::create([
            'kata' => 'dahar',
            'bahasa_daerah' => 'Sunda',
            'arti_indonesia' => 'makan',
            'contoh_kalimat' => 'Abdi dahar sangu.',
            'tingkat_kesopanan' => 'loma',
        ]);

        $this->getJson('/api/v1/statistik')
            ->assertOk()
            ->assertJsonPath('message', 'Statistik katalog berhasil diambil')
            ->assertJsonPath('data.total_kata', 3)
            ->assertJsonPath('data.total_bahasa_daerah', 2)
            ->assertJsonPath('data.kata_punya_contoh_kalimat', 2)
            ->assertJsonPath('data.persentase_contoh_kalimat', 66.7)
            ->assertJsonStructure([
                'data' => [
                    'total_kata',
                    'total_bahasa_daerah',
                    'kata_per_bahasa',
                    'kata_per_tingkat_kesopanan',
                    'kata_punya_contoh_kalimat',
                    'persentase_contoh_kalimat',
                    'kata_terbaru',
                ],
            ]);
    }

    public function test_statistik_tetap_jalan_saat_katalog_kosong(): void
    {
        $this->getJson('/api/v1/statistik')
            ->assertOk()
            ->assertJsonPath('data.total_kata', 0)
            ->assertJsonPath('data.total_bahasa_daerah', 0)
            ->assertJsonPath('data.persentase_contoh_kalimat', 0)
            ->assertJsonCount(0, 'data.kata_terbaru');
    }
}
