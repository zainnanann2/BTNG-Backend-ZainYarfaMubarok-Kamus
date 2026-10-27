<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $override = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin Kamus',
            'email' => 'admin@kamus.test',
            'password' => Hash::make('password123'),
        ], $override));
    }

    public function test_register_membuat_akun_dan_menerbitkan_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Operator',
            'email' => 'operator@kamus.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'operator@kamus.test')
            ->assertJsonStructure(['message', 'data' => ['user' => ['id', 'name', 'email'], 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'operator@kamus.test']);
    }

    public function test_register_menolak_email_ganda(): void
    {
        $this->user();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Duplikat',
            'email' => 'admin@kamus.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_register_menolak_konfirmasi_password_tidak_cocok(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Operator',
            'email' => 'operator@kamus.test',
            'password' => 'password123',
            'password_confirmation' => 'beda-password',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_login_membalas_token(): void
    {
        $this->user();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@kamus.test',
            'password' => 'password123',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Login berhasil')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_menolak_password_salah(): void
    {
        $this->user();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@kamus.test',
            'password' => 'salah-sekali',
        ])
            ->assertStatus(422)
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_login_menolak_email_tidak_terdaftar(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'tidak-ada@kamus.test',
            'password' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_me_mengembalikan_profil_dengan_token(): void
    {
        $user = $this->user();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@kamus.test')
            ->assertJsonMissingPath('data.password');
    }

    public function test_me_tanpa_token_membalas_401_dengan_amplop(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('data', null)
            ->assertJsonStructure(['message', 'data']);
    }

    public function test_logout_mencabut_token_yang_dipakai(): void
    {
        $user = $this->user();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('data', null);

        // Baris token benar-benar dihapus dari database.
        $this->assertDatabaseCount('personal_access_tokens', 0);

        /*
         * Guard auth menyimpan user hasil resolusi sebelumnya di dalam
         * container, dan container tidak dibuat ulang antar-request pada
         * test. forgetGuards() memaksa resolusi ulang dari token, sehingga
         * penolakan 401 di bawah benar-benar menguji token yang dicabut.
         */
        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('data', null);
    }
}
