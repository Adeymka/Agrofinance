<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_get_auth_identifier_name_returns_telephone(): void
    {
        $user = new User();

        $this->assertSame('telephone', $user->getAuthIdentifierName());
    }

    public function test_verifier_pin_returns_expected_result(): void
    {
        $pin = '1234';

        $user = new User();
        $user->pin_hash = Hash::make($pin);

        $this->assertTrue($user->verifierPin($pin));
        $this->assertFalse($user->verifierPin('0000'));
    }
}

