<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Kamus
 */
class KamusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kata' => $this->kata,
            'bahasa_daerah' => $this->bahasa_daerah,
            'arti_indonesia' => $this->arti_indonesia,
            'contoh_kalimat' => $this->contoh_kalimat,
            'tingkat_kesopanan' => $this->tingkat_kesopanan,
            'dibuat_pada' => $this->created_at?->toIso8601String(),
            'diperbarui_pada' => $this->updated_at?->toIso8601String(),
        ];
    }
}
