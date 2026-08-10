<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\RecoveryCode;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_without_two_factor_configured_redirects_to_profile_setup()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'document' => $user->document,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response = $this->get('/contacts');

        $response->assertRedirect(route('profile.show'));
    }

    public function test_login_with_two_factor_configured_challenges_before_authenticating_session()
    {
        $user = $this->createUserWithTwoFactor();

        $this->post('/login', [
            'document' => $user->document,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response = $this->get('/contacts');

        $response->assertRedirect('/login');
    }

    public function test_login_with_valid_totp_code_authenticates_and_passes_the_two_factor_gate()
    {
        [$user, $secret] = $this->createUserWithTwoFactor(returnSecret: true);

        $this->post('/login', [
            'document' => $user->document,
            'password' => 'password',
        ]);

        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->post('/two-factor-challenge', [
            'code' => $code,
        ]);

        $this->assertAuthenticatedAs($user);

        $response = $this->get('/contacts');

        $response->assertOk();
    }

    public function test_login_with_valid_recovery_code_authenticates_and_consumes_the_code()
    {
        $recoveryCode = 'recovery-code-1';
        $user = $this->createUserWithTwoFactor(recoveryCodes: [$recoveryCode, 'recovery-code-2']);

        $this->post('/login', [
            'document' => $user->document,
            'password' => 'password',
        ]);

        $this->post('/two-factor-challenge', [
            'recovery_code' => $recoveryCode,
        ]);

        $this->assertAuthenticatedAs($user);

        $user->refresh();

        $this->assertNotContains($recoveryCode, $user->recoveryCodes());
    }

    /**
     * @return \App\Models\User|array
     */
    private function createUserWithTwoFactor(bool $returnSecret = false, ?array $recoveryCodes = null)
    {
        $secret = (new Google2FA)->generateSecretKey();

        $recoveryCodes ??= collect(range(1, 8))->map(fn () => RecoveryCode::generate())->all();

        $user = User::factory()->create([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at' => now(),
        ]);

        return $returnSecret ? [$user, $secret] : $user;
    }
}
