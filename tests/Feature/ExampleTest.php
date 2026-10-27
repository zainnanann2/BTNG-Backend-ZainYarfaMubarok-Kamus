<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Akar aplikasi bukan halaman HTML, melainkan pengalihan ke
     * dokumentasi interaktif.
     */
    public function test_akar_aplikasi_mengarah_ke_dokumentasi(): void
    {
        $this->get('/')->assertRedirect('/docs/api');
    }
}
