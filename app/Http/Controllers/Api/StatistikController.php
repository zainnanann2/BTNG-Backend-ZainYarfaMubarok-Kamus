<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Kamus;
use Illuminate\Http\JsonResponse;

class StatistikController extends Controller
{
    use ApiResponse;

    /**
     * Ringkasan katalog untuk dashboard: sebaran per bahasa daerah dan
     * per tingkat kesopanan, plus entri terbaru dan kelengkapan contoh kalimat.
     */
    public function index(): JsonResponse
    {
        $perBahasa = Kamus::query()
            ->selectRaw('bahasa_daerah, COUNT(*) as jumlah')
            ->groupBy('bahasa_daerah')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => [
                'bahasa_daerah' => $row->bahasa_daerah,
                'jumlah' => (int) $row->jumlah,
            ]);

        $perTingkat = Kamus::query()
            ->selectRaw('tingkat_kesopanan, COUNT(*) as jumlah')
            ->groupBy('tingkat_kesopanan')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($row) => [
                'tingkat_kesopanan' => $row->tingkat_kesopanan,
                'jumlah' => (int) $row->jumlah,
            ]);

        $total = Kamus::count();
        $punyaContoh = Kamus::whereNotNull('contoh_kalimat')->where('contoh_kalimat', '!=', '')->count();

        return $this->success('Statistik katalog berhasil diambil', [
            'total_kata' => $total,
            'total_bahasa_daerah' => $perBahasa->count(),
            'kata_per_bahasa' => $perBahasa,
            'kata_per_tingkat_kesopanan' => $perTingkat,
            'kata_punya_contoh_kalimat' => $punyaContoh,
            'persentase_contoh_kalimat' => $total > 0
                ? round($punyaContoh / $total * 100, 1)
                : 0.0,
            'kata_terbaru' => \App\Http\Resources\KamusResource::collection(
                Kamus::query()->latest('id')->limit(5)->get()
            ),
        ]);
    }
}
