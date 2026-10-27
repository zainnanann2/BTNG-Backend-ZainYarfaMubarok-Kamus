<?php

namespace App\Http\Requests;

use App\Models\Kamus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKamusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kata' => [
                'required',
                'string',
                'max:100',
                // Kombinasi kata + bahasa + tingkat harus unik.
                Rule::unique('kamus', 'kata')
                    ->where('bahasa_daerah', $this->input('bahasa_daerah'))
                    ->where('tingkat_kesopanan', $this->input('tingkat_kesopanan', 'netral')),
            ],
            'bahasa_daerah' => ['required', 'string', 'max:50'],
            'arti_indonesia' => ['required', 'string', 'max:255'],
            'contoh_kalimat' => ['nullable', 'string', 'max:500'],
            'tingkat_kesopanan' => ['sometimes', 'string', Rule::in(Kamus::TINGKAT_KESOPANAN)],
        ];
    }

    public function messages(): array
    {
        return [
            'kata.required' => 'Kolom kata wajib diisi.',
            'kata.max' => 'Kata maksimal 100 karakter.',
            'kata.unique' => 'Kata ini sudah terdaftar pada bahasa dan tingkat kesopanan yang sama.',
            'bahasa_daerah.required' => 'Kolom bahasa daerah wajib diisi.',
            'bahasa_daerah.max' => 'Bahasa daerah maksimal 50 karakter.',
            'arti_indonesia.required' => 'Kolom arti Indonesia wajib diisi.',
            'arti_indonesia.max' => 'Arti Indonesia maksimal 255 karakter.',
            'contoh_kalimat.max' => 'Contoh kalimat maksimal 500 karakter.',
            'tingkat_kesopanan.in' => 'Tingkat kesopanan harus salah satu dari: '
                .implode(', ', Kamus::TINGKAT_KESOPANAN).'.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kata' => is_string($this->kata) ? trim($this->kata) : $this->kata,
            'bahasa_daerah' => is_string($this->bahasa_daerah)
                ? ucfirst(strtolower(trim($this->bahasa_daerah)))
                : $this->bahasa_daerah,
            'arti_indonesia' => is_string($this->arti_indonesia) ? trim($this->arti_indonesia) : $this->arti_indonesia,
            'tingkat_kesopanan' => $this->input('tingkat_kesopanan', 'netral'),
        ]);
    }
}
