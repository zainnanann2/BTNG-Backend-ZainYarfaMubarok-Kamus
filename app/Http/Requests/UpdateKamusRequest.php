<?php

namespace App\Http\Requests;

use App\Models\Kamus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKamusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Kamus $kamus */
        $kamus = $this->route('kamus');

        return [
            'kata' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('kamus', 'kata')
                    ->where('bahasa_daerah', $this->input('bahasa_daerah', $kamus->bahasa_daerah))
                    ->where('tingkat_kesopanan', $this->input('tingkat_kesopanan', $kamus->tingkat_kesopanan))
                    ->ignore($kamus->id),
            ],
            'bahasa_daerah' => ['sometimes', 'required', 'string', 'max:50'],
            'arti_indonesia' => ['sometimes', 'required', 'string', 'max:255'],
            'contoh_kalimat' => ['sometimes', 'nullable', 'string', 'max:500'],
            'tingkat_kesopanan' => ['sometimes', 'required', 'string', Rule::in(Kamus::TINGKAT_KESOPANAN)],
        ];
    }

    public function messages(): array
    {
        return [
            'kata.required' => 'Kolom kata tidak boleh kosong.',
            'kata.unique' => 'Kata ini sudah terdaftar pada bahasa dan tingkat kesopanan yang sama.',
            'bahasa_daerah.required' => 'Kolom bahasa daerah tidak boleh kosong.',
            'arti_indonesia.required' => 'Kolom arti Indonesia tidak boleh kosong.',
            'contoh_kalimat.max' => 'Contoh kalimat maksimal 500 karakter.',
            'tingkat_kesopanan.in' => 'Tingkat kesopanan harus salah satu dari: '
                .implode(', ', Kamus::TINGKAT_KESOPANAN).'.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if (is_string($this->kata)) {
            $data['kata'] = trim($this->kata);
        }

        if (is_string($this->bahasa_daerah)) {
            $data['bahasa_daerah'] = ucfirst(strtolower(trim($this->bahasa_daerah)));
        }

        if (is_string($this->arti_indonesia)) {
            $data['arti_indonesia'] = trim($this->arti_indonesia);
        }

        $this->merge($data);
    }
}
