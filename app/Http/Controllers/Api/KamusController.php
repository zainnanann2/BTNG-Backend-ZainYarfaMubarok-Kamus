<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKamusRequest;
use App\Http\Requests\UpdateKamusRequest;
use App\Http\Resources\KamusResource;
use App\Http\Responses\ApiResponse;
use App\Models\Kamus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KamusController extends Controller
{
    use ApiResponse;

    /**
     * Kolom yang boleh dipakai untuk mengurutkan.
     * Daftar putih ini mencegah klien mengurutkan berdasarkan kolom sembarang.
     */
    private const KOLOM_SORTIR = ['kata', 'arti_indonesia', 'bahasa_daerah', 'tingkat_kesopanan', 'created_at'];

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'bahasa_daerah' => ['nullable', 'string', 'max:50'],
            'tingkat_kesopanan' => ['nullable', 'string'],
            'sort_by' => ['nullable', 'string'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $sortBy = in_array($validated['sort_by'] ?? null, self::KOLOM_SORTIR, true)
            ? $validated['sort_by']
            : 'kata';

        $sortDir = $validated['sort_dir'] ?? 'asc';

        $data = Kamus::query()
            ->cari($validated['search'] ?? null)
            ->bahasa($validated['bahasa_daerah'] ?? null)
            ->tingkat($validated['tingkat_kesopanan'] ?? null)
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate($validated['per_page'] ?? 10)
            ->withQueryString();

        return $this->paginated(
            'Daftar kata berhasil diambil',
            KamusResource::collection($data)
        );
    }

    public function store(StoreKamusRequest $request): JsonResponse
    {
        $kamus = Kamus::create($request->validated());

        return $this->success(
            'Kata berhasil ditambahkan',
            new KamusResource($kamus),
            201
        );
    }

    public function show(Kamus $kamus): JsonResponse
    {
        return $this->success(
            'Detail kata berhasil diambil',
            new KamusResource($kamus)
        );
    }

    public function update(UpdateKamusRequest $request, Kamus $kamus): JsonResponse
    {
        $kamus->fill($request->validated())->save();

        return $this->success(
            'Kata berhasil diperbarui',
            new KamusResource($kamus->refresh())
        );
    }

    public function destroy(Kamus $kamus): JsonResponse
    {
        $kamus->delete();

        return $this->success('Kata berhasil dihapus', null);
    }
}
