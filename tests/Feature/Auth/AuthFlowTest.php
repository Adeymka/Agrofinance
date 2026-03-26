<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_auth_flow_works_from_signup_to_logout(): void
    {
        $telephone = '+22967000001';

        $inscription = $this->postJson('/api/auth/inscription', [
            'nom' => 'Akobi',
            'prenom' => 'Fidele',
            'telephone' => $telephone,
            'type_exploitation' => 'cultures_vivrieres',
        ]);

        $inscription->assertStatus(201)
            ->assertJson([
                'succes' => true,
            ]);

        $otpKey = 'otp_' . preg_replace('/[^0-9]/', '', $telephone);
        $otpData = Cache::get($otpKey);

        $this->assertNotNull($otpData);
        $this->assertArrayHasKey('code', $otpData);

        $verification = $this->postJson('/api/auth/verification-otp', [
            'telephone' => $telephone,
            'code' => $otpData['code'],
        ]);

        $verification->assertStatus(200)
            ->assertJson([
                'succes' => true,
            ]);

        $pinToken = $verification->json('pin_creation_token');

        $this->postJson('/api/auth/creer-pin', [
            'telephone' => $telephone,
            'pin' => '1234',
            'pin_confirmation' => '1234',
            'otp_token' => $pinToken,
        ])->assertStatus(200)->assertJson([
            'succes' => true,
        ]);

        $connexion = $this->postJson('/api/auth/connexion', [
            'telephone' => $telephone,
            'pin' => '1234',
        ]);

        $connexion->assertStatus(200)
            ->assertJsonPath('succes', true);

        $token = $connexion->json('data.token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('succes', true)
            ->assertJsonPath('data.telephone', $telephone);

        $this->withToken($token)
            ->postJson('/api/auth/deconnexion')
            ->assertStatus(200)
            ->assertJson([
                'succes' => true,
                'message' => 'Déconnecté.',
            ]);

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    public function test_me_requires_authentication_without_token(): void
    {
        $this->getJson('/api/auth/me')
            ->assertStatus(401)
            ->assertJson([
                'succes' => false,
                'message' => 'Non authentifié.',
            ]);
    }

    public function test_connexion_fails_with_wrong_pin(): void
    {
        $user = User::create([
            'nom' => 'Akobi',
            'prenom' => 'Fidele',
            'telephone' => '+22967000002',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        $this->assertNotNull($user->id);

        $this->postJson('/api/auth/connexion', [
            'telephone' => '+22967000002',
            'pin' => '9999',
        ])->assertStatus(401)->assertJson([
            'succes' => false,
            'message' => 'Numéro ou PIN incorrect.',
        ]);
    }
}

