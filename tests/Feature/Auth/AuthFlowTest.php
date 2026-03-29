<?php

namespace Tests\Feature\Auth;

use App\Models\Abonnement;
use App\Models\Exploitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_auth_flow_works_from_signup_to_logout(): void
    {
        $telephone = '+22967000001';

        $inscription = $this->postJson('/api/v1/auth/inscription', [
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

        $verification = $this->postJson('/api/v1/auth/verification-otp', [
            'telephone' => $telephone,
            'code' => $otpData['code'],
        ]);

        $verification->assertStatus(200)
            ->assertJson([
                'succes' => true,
            ]);

        $this->postJson('/api/v1/auth/creer-pin', [
            'telephone' => $telephone,
            'pin' => '1234',
            'pin_confirmation' => '1234',
        ])->assertStatus(200)->assertJson([
            'succes' => true,
        ]);

        $connexion = $this->postJson('/api/v1/auth/connexion', [
            'telephone' => $telephone,
            'pin' => '1234',
        ]);

        $connexion->assertStatus(200)
            ->assertJsonPath('succes', true);

        $token = $connexion->json('data.token');
        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('succes', true)
            ->assertJsonPath('data.telephone', $telephone);

        $this->withToken($token)
            ->postJson('/api/v1/auth/deconnexion')
            ->assertStatus(200)
            ->assertJson([
                'succes' => true,
                'message' => 'Déconnecté.',
            ]);

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }

    public function test_me_requires_authentication_without_token(): void
    {
        $this->getJson('/api/v1/auth/me')
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

        $this->postJson('/api/v1/auth/connexion', [
            'telephone' => '+22967000002',
            'pin' => '9999',
        ])->assertStatus(401)->assertJson([
            'succes' => false,
            'message' => 'Numéro ou PIN incorrect.',
        ]);
    }

    public function test_connexion_api_rate_limits_after_ten_failed_attempts(): void
    {
        $user = User::create([
            'nom' => 'Rate',
            'prenom' => 'Limit',
            'telephone' => '+22967000003',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/auth/connexion', [
                'telephone' => $user->telephone,
                'pin' => '9999',
            ])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/connexion', [
            'telephone' => $user->telephone,
            'pin' => '9999',
        ])
            ->assertStatus(429)
            ->assertJsonPath('code', 'TOO_MANY_ATTEMPTS');
    }

    public function test_api_exploitation_of_another_user_returns_404(): void
    {
        $userA = User::create([
            'nom' => 'A',
            'prenom' => 'A',
            'telephone' => '+22967000004',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);
        $userB = User::create([
            'nom' => 'B',
            'prenom' => 'B',
            'telephone' => '+22967000005',
            'type_exploitation' => 'mixte',
            'pin_hash' => bcrypt('1234'),
        ]);

        foreach ([$userA, $userB] as $u) {
            Abonnement::create([
                'user_id' => $u->id,
                'plan' => 'essentielle',
                'statut' => 'actif',
                'date_debut' => now()->subDay()->toDateString(),
                'date_fin' => now()->addMonth()->toDateString(),
                'montant' => 0,
            ]);
        }

        $exploitationB = Exploitation::create([
            'user_id' => $userB->id,
            'nom' => 'Ferme B',
            'type' => 'mixte',
            'localisation' => 'Test',
        ]);

        Sanctum::actingAs($userA);

        $this->getJson('/api/v1/exploitations/'.$exploitationB->id)
            ->assertStatus(404);
    }
}

