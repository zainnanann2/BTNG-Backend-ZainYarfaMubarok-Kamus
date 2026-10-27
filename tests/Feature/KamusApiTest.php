<?php

namespace Tests\Feature;

use App\Models\Kamus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamusApiTest extends TestCase
{
    use RefreshDatabase;

    private function buat(array $override = []): Kamus
    {
        return Kamus::create(array_merge([
            'kata' => 'mangan',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'makan',
            'contoh_kalimat' => 'Aku mangan sega goreng ing warung.',
            'tingkat_kesopanan' => 'ngoko',
        ], $override));
    }

    public function test_index_memakai_amplop_dan_paginasi(): void
    {
        $this->buat();
        $this->buat(['kata' => 'turu', 'arti_indonesia' => 'tidur']);

        $this->getJson('/api/v1/kamus')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => [['id', 'kata', 'bahasa_daerah', 'arti_indonesia', 'contoh_kalimat', 'tingkat_kesopanan']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page', 'from', 'to'],
                'links' => ['first', 'prev', 'next', 'last'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_index_bisa_disaring_per_bahasa_daerah(): void
    {
        $this->buat();
        $this->buat([
            'kata' => 'dahar',
            'bahasa_daerah' => 'Sunda',
            'tingkat_kesopanan' => 'loma',
        ]);

        $this->getJson('/api/v1/kamus?bahasa_daerah=Sunda')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.kata', 'dahar');
    }

    public function test_index_bisa_disaring_per_tingkat_kesopanan(): void
    {
        $this->buat();
        $this->buat(['kata' => 'dhahar', 'tingkat_kesopanan' => 'krama_inggil']);

        $this->getJson('/api/v1/kamus?tingkat_kesopanan=krama_inggil')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.kata', 'dhahar');
    }

    public function test_index_bisa_dicari_pada_kata_dan_arti(): void
    {
        $this->buat();
        $this->buat(['kata' => 'turu', 'arti_indonesia' => 'tidur']);

        $this->getJson('/api/v1/kamus?search=tidur')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.kata', 'turu');
    }

    public function test_index_menghormati_per_page(): void
    {
        foreach (range(1, 5) as $i) {
            $this->buat(['kata' => 'kata'.$i]);
        }

        $this->getJson('/api/v1/kamus?per_page=2')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3)
            ->assertJsonCount(2, 'data');
    }

    public function test_index_menolak_per_page_di_luar_batas(): void
    {
        $this->getJson('/api/v1/kamus?per_page=500')
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['message', 'data', 'errors' => ['per_page']]);
    }

    public function test_index_mengurutkan_sesuai_permintaan(): void
    {
        $this->buat(['kata' => 'zebra']);
        $this->buat(['kata' => 'apel']);

        $this->getJson('/api/v1/kamus?sort_by=kata&sort_dir=asc')
            ->assertOk()
            ->assertJsonPath('data.0.kata', 'apel');

        $this->getJson('/api/v1/kamus?sort_by=kata&sort_dir=desc')
            ->assertOk()
            ->assertJsonPath('data.0.kata', 'zebra');
    }

    public function test_store_menyimpan_data_baru(): void
    {
        $payload = [
            'kata' => 'sare',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'tidur (halus)',
            'contoh_kalimat' => 'Bapak sampun sare.',
            'tingkat_kesopanan' => 'krama_inggil',
        ];

        $this->postJson('/api/v1/kamus', $payload)
            ->assertCreated()
            ->assertJsonPath('message', 'Kata berhasil ditambahkan')
            ->assertJsonPath('data.kata', 'sare')
            ->assertJsonPath('data.tingkat_kesopanan', 'krama_inggil');

        $this->assertDatabaseHas('kamus', ['kata' => 'sare']);
    }

    public function test_store_menormalkan_huruf_bahasa_daerah(): void
    {
        $this->postJson('/api/v1/kamus', [
            'kata' => 'aeng',
            'bahasa_daerah' => '  mADURA ',
            'arti_indonesia' => 'air',
            'tingkat_kesopanan' => 'netral',
        ])
            ->assertCreated()
            ->assertJsonPath('data.bahasa_daerah', 'Madura');
    }

    public function test_store_memakai_netral_bila_tingkat_tidak_dikirim(): void
    {
        $this->postJson('/api/v1/kamus', [
            'kata' => 'aeng',
            'bahasa_daerah' => 'Madura',
            'arti_indonesia' => 'air',
        ])
            ->assertCreated()
            ->assertJsonPath('data.tingkat_kesopanan', 'netral');
    }

    public function test_store_menolak_kata_ganda_pada_tingkat_yang_sama(): void
    {
        $this->buat();

        $this->postJson('/api/v1/kamus', [
            'kata' => 'mangan',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'makan lagi',
            'tingkat_kesopanan' => 'ngoko',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['errors' => ['kata']]);
    }

    public function test_store_mengizinkan_kata_sama_pada_tingkat_berbeda(): void
    {
        $this->buat();

        $this->postJson('/api/v1/kamus', [
            'kata' => 'mangan',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'makan (versi halus)',
            'tingkat_kesopanan' => 'krama_madya',
        ])->assertCreated();
    }

    public function test_store_menolak_input_tidak_valid(): void
    {
        $this->postJson('/api/v1/kamus', [
            'kata' => '',
            'tingkat_kesopanan' => 'ngawur',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonStructure([
                'message',
                'data',
                'errors' => ['kata', 'bahasa_daerah', 'arti_indonesia', 'tingkat_kesopanan'],
            ]);
    }

    public function test_show_menampilkan_satu_data(): void
    {
        $kamus = $this->buat();

        $this->getJson('/api/v1/kamus/'.$kamus->id)
            ->assertOk()
            ->assertJsonPath('message', 'Detail kata berhasil diambil')
            ->assertJsonPath('data.id', $kamus->id)
            ->assertJsonPath('data.kata', 'mangan');
    }

    public function test_show_membalas_404_dengan_amplop(): void
    {
        $this->getJson('/api/v1/kamus/999999')
            ->assertNotFound()
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_update_dengan_put_mengganti_seluruh_field(): void
    {
        $kamus = $this->buat();

        $this->putJson('/api/v1/kamus/'.$kamus->id, [
            'kata' => 'nedha',
            'bahasa_daerah' => 'Jawa',
            'arti_indonesia' => 'makan (krama)',
            'contoh_kalimat' => 'Kula nedha sekul.',
            'tingkat_kesopanan' => 'krama_madya',
        ])
            ->assertOk()
            ->assertJsonPath('data.kata', 'nedha')
            ->assertJsonPath('data.tingkat_kesopanan', 'krama_madya');
    }

    public function test_update_dengan_patch_hanya_mengubah_field_yang_dikirim(): void
    {
        $kamus = $this->buat();

        $this->patchJson('/api/v1/kamus/'.$kamus->id, [
            'arti_indonesia' => 'makan (sehari-hari)',
        ])
            ->assertOk()
            ->assertJsonPath('data.arti_indonesia', 'makan (sehari-hari)')
            ->assertJsonPath('data.kata', 'mangan')
            ->assertJsonPath('data.tingkat_kesopanan', 'ngoko');
    }

    public function test_update_menolak_kata_ganda(): void
    {
        $this->buat();
        $kedua = $this->buat(['kata' => 'turu', 'arti_indonesia' => 'tidur']);

        $this->patchJson('/api/v1/kamus/'.$kedua->id, ['kata' => 'mangan'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['kata']]);
    }

    public function test_update_membalas_404_untuk_id_tidak_ada(): void
    {
        $this->patchJson('/api/v1/kamus/999999', ['arti_indonesia' => 'x'])
            ->assertNotFound()
            ->assertJsonPath('data', null);
    }

    public function test_destroy_menghapus_data(): void
    {
        $kamus = $this->buat();

        $this->deleteJson('/api/v1/kamus/'.$kamus->id)
            ->assertOk()
            ->assertJsonPath('message', 'Kata berhasil dihapus')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('kamus', ['id' => $kamus->id]);
    }

    public function test_destroy_membalas_404_untuk_id_tidak_ada(): void
    {
        $this->deleteJson('/api/v1/kamus/999999')
            ->assertNotFound()
            ->assertJsonPath('data', null);
    }

    public function test_endpoint_tidak_dikenal_tetap_membalas_json(): void
    {
        $this->getJson('/api/v1/tidak-ada')
            ->assertNotFound()
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['message', 'data']);
    }
}
