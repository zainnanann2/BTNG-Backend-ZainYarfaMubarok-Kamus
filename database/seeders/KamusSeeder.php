<?php

namespace Database\Seeders;

use App\Models\Kamus;
use Illuminate\Database\Seeder;

class KamusSeeder extends Seeder
{
    /**
     * Kosakata contoh untuk katalog.
     *
     * Pola yang dipakai adalah pasangan tingkat tutur: satu makna dicatat
     * beberapa kali dengan tingkat kesopanan berbeda, persis seperti kamus
     * bahasa daerah yang sebenarnya.
     *
     * Seeder ini idempoten: memakai updateOrCreate pada kunci unik
     * (kata + bahasa_daerah + tingkat_kesopanan), sehingga menjalankan
     * db:seed berkali-kali tidak menggandakan data.
     */
    public function run(): void
    {
        foreach ($this->data() as $baris) {
            Kamus::updateOrCreate(
                [
                    'kata' => $baris[0],
                    'bahasa_daerah' => $baris[1],
                    'tingkat_kesopanan' => $baris[2],
                ],
                [
                    'arti_indonesia' => $baris[3],
                    'contoh_kalimat' => $baris[4] ?? null,
                ]
            );
        }
    }

    /**
     * Format tiap baris: [kata, bahasa_daerah, tingkat_kesopanan, arti_indonesia, contoh_kalimat]
     */
    private function data(): array
    {
        return [
            // ---------- JAWA: makan ----------
            ['mangan', 'Jawa', 'ngoko', 'makan',
                'Aku mangan sega goreng ing warung.'],
            ['nedha', 'Jawa', 'krama_madya', 'makan (halus)',
                'Kula nedha sekul goreng wonten warung.'],
            ['dhahar', 'Jawa', 'krama_inggil', 'makan (untuk orang yang dihormati)',
                'Simbah sampun dhahar.'],

            // ---------- JAWA: tidur ----------
            ['turu', 'Jawa', 'ngoko', 'tidur',
                'Adhiku turu ing kamar.'],
            ['sare', 'Jawa', 'krama_inggil', 'tidur (untuk orang yang dihormati)',
                'Bapak sampun sare ing kamar.'],

            // ---------- JAWA: pergi / datang ----------
            ['lunga', 'Jawa', 'ngoko', 'pergi',
                'Aku arep lunga menyang pasar.'],
            ['tindak', 'Jawa', 'krama_inggil', 'pergi (untuk orang yang dihormati)',
                'Ibu tindak dhateng pasar.'],
            ['teka', 'Jawa', 'ngoko', 'datang',
                'Dheweke teka wingi sore.'],
            ['rawuh', 'Jawa', 'krama_inggil', 'datang (untuk orang yang dihormati)',
                'Para tamu sampun rawuh.'],

            // ---------- JAWA: minum ----------
            ['ngombe', 'Jawa', 'ngoko', 'minum',
                'Aku ngombe banyu bening.'],
            ['ngunjuk', 'Jawa', 'krama_madya', 'minum (halus)',
                'Kula ngunjuk toya bening.'],

            // ---------- JAWA: rumah ----------
            ['omah', 'Jawa', 'ngoko', 'rumah',
                'Omahku cedhak kali.'],
            ['dalem', 'Jawa', 'krama_madya', 'rumah (halus)',
                'Dalem kula celak kali.'],

            // ---------- JAWA: air ----------
            ['banyu', 'Jawa', 'ngoko', 'air',
                'Banyu ing sumur iku adhem.'],
            ['toya', 'Jawa', 'krama_madya', 'air (halus)',
                'Toya ing sumur menika asrep.'],

            // ---------- JAWA: nasi / lauk ----------
            ['sega', 'Jawa', 'ngoko', 'nasi',
                'Aku mangan sega saben dina.'],
            ['sekul', 'Jawa', 'krama_madya', 'nasi (halus)',
                'Kula nedha sekul saben dinten.'],
            ['iwak', 'Jawa', 'ngoko', 'lauk, ikan',
                'Iwak ing pasar iku seger.'],
            ['ulam', 'Jawa', 'krama_madya', 'lauk (halus)',
                'Ulam ing peken menika seger.'],

            // ---------- JAWA: anggota tubuh ----------
            ['mripat', 'Jawa', 'ngoko', 'mata',
                'Mripatku perih merga kesel.'],
            ['paningal', 'Jawa', 'krama_inggil', 'mata (untuk orang yang dihormati)',
                'Paningalipun ketingal cerah.'],
            ['tangan', 'Jawa', 'ngoko', 'tangan',
                'Tangane reged kena lemah.'],
            ['asta', 'Jawa', 'krama_inggil', 'tangan (untuk orang yang dihormati)',
                'Asta panjenengan menika alit.'],
            ['sikil', 'Jawa', 'ngoko', 'kaki',
                'Sikilku lara merga mlaku adoh.'],
            ['suku', 'Jawa', 'krama_madya', 'kaki (halus)',
                'Suku kula sakit amargi mlampah tebih.'],
            ['rambut', 'Jawa', 'ngoko', 'rambut',
                'Rambute dawa lan ireng.'],
            ['rikma', 'Jawa', 'krama_inggil', 'rambut (untuk orang yang dihormati)',
                'Rikmanipun dawa lan cemeng.'],

            // ---------- JAWA: keluarga ----------
            ['anak', 'Jawa', 'ngoko', 'anak',
                'Anake loro, lanang lan wadon.'],
            ['putra', 'Jawa', 'krama_inggil', 'anak (untuk orang yang dihormati)',
                'Putra panjenengan sampun sekolah?'],

            // ---------- JAWA: sifat ----------
            ['gedhe', 'Jawa', 'ngoko', 'besar',
                'Omah iku gedhe banget.'],
            ['ageng', 'Jawa', 'krama_madya', 'besar (halus)',
                'Dalem menika ageng sanget.'],
            ['cilik', 'Jawa', 'ngoko', 'kecil',
                'Kucingku isih cilik.'],
            ['alit', 'Jawa', 'krama_madya', 'kecil (halus)',
                'Kucing kula taksih alit.'],
            ['apik', 'Jawa', 'ngoko', 'bagus, baik',
                'Pegaweyane apik banget.'],
            ['sae', 'Jawa', 'krama_madya', 'bagus (halus)',
                'Pandamelanipun sae sanget.'],
            ['ala', 'Jawa', 'ngoko', 'jelek, buruk',
                'Kabar iku ala banget.'],
            ['awon', 'Jawa', 'krama_madya', 'jelek (halus)',
                'Kabar menika awon sanget.'],

            // ---------- JAWA: jarak / ukuran ----------
            ['dhuwur', 'Jawa', 'ngoko', 'tinggi',
                'Gedhung iku dhuwur banget.'],
            ['inggil', 'Jawa', 'krama_madya', 'tinggi (halus)',
                'Gedhong menika inggil sanget.'],
            ['adoh', 'Jawa', 'ngoko', 'jauh',
                'Pasar iku adoh saka omahku.'],
            ['tebih', 'Jawa', 'krama_madya', 'jauh (halus)',
                'Peken menika tebih saking dalem kula.'],
            ['cedhak', 'Jawa', 'ngoko', 'dekat',
                'Sekolahan cedhak karo omah.'],
            ['celak', 'Jawa', 'krama_madya', 'dekat (halus)',
                'Sekolahan celak kaliyan dalem.'],

            // ---------- JAWA: kegiatan ----------
            ['tuku', 'Jawa', 'ngoko', 'beli',
                'Aku tuku roti ing toko.'],
            ['tumbas', 'Jawa', 'krama_madya', 'beli (halus)',
                'Kula tumbas roti ing toko.'],
            ['mlaku', 'Jawa', 'ngoko', 'berjalan',
                'Aku mlaku menyang sekolah.'],
            ['mlampah', 'Jawa', 'krama_madya', 'berjalan (halus)',
                'Kula mlampah dhateng sekolah.'],
            ['maca', 'Jawa', 'ngoko', 'membaca',
                'Aku maca buku saben sore.'],
            ['maos', 'Jawa', 'krama_madya', 'membaca (halus)',
                'Kula maos buku saben sonten.'],
            ['nulis', 'Jawa', 'ngoko', 'menulis',
                'Dheweke nulis layang kanggo ibune.'],
            ['nyerat', 'Jawa', 'krama_madya', 'menulis (halus)',
                'Panjenenganipun nyerat serat kagem ibunipun.'],
            ['sregep', 'Jawa', 'ngoko', 'rajin',
                'Bocah iku sregep sinau.'],
            ['sengkud', 'Jawa', 'krama_madya', 'rajin (halus)',
                'Lare menika sengkud sinau.'],

            // ---------- SUNDA ----------
            ['dahar', 'Sunda', 'loma', 'makan',
                'Abdi dahar sangu jeung lauk.'],
            ['neda', 'Sunda', 'lemes', 'makan (halus)',
                'Abdi neda sangu sareng lauk.'],
            ['imah', 'Sunda', 'loma', 'rumah',
                'Imah abdi caket ka walungan.'],
            ['bumi', 'Sunda', 'lemes', 'rumah (halus)',
                'Bumi abdi caket ka walungan.'],
            ['indit', 'Sunda', 'loma', 'pergi',
                'Anjeunna indit ka pasar kamari.'],
            ['angkat', 'Sunda', 'lemes', 'pergi (halus)',
                'Anjeunna angkat ka pasar kamari.'],
            ['hees', 'Sunda', 'loma', 'tidur',
                'Pun adi nuju hees di kamar.'],
            ['sare', 'Sunda', 'lemes', 'tidur (halus)',
                'Bapa nuju sare di kamar.'],
            ['alus', 'Sunda', 'loma', 'bagus',
                'Baju anjeun alus pisan.'],
            ['sae', 'Sunda', 'lemes', 'bagus (halus)',
                'Baju anjeun sae pisan.'],
            ['badag', 'Sunda', 'loma', 'besar',
                'Imahna badag pisan.'],
            ['ageung', 'Sunda', 'lemes', 'besar (halus)',
                'Bumina ageung pisan.'],

            // ---------- MADURA (tanpa tingkatan tutur) ----------
            ['ngakan', 'Madura', 'netral', 'makan',
                "Sengko' ngakan nasi."],
            ['aeng', 'Madura', 'netral', 'air',
                "Aeng e somor te' dingin."],
            ['bengko', 'Madura', 'netral', 'nanti',
                "Bengko sengko' entar."],
        ];
    }
}
