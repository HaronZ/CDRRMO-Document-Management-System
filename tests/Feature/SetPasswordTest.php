<?php

namespace Tests\Feature;

use App\Livewire\SetPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class SetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_their_password_with_a_valid_token_and_is_logged_in(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        Livewire::test(SetPassword::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('password', 'my-new-password')
            ->set('passwordConfirmation', 'my-new-password')
            ->call('setPassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('my-new-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::test(SetPassword::class, ['token' => 'not-a-real-token'])
            ->set('email', $user->email)
            ->set('password', 'my-new-password')
            ->set('passwordConfirmation', 'my-new-password')
            ->call('setPassword')
            ->assertSet('invalid', true);

        $this->assertGuest();
    }

    public function test_mismatched_confirmation_is_rejected_before_touching_the_token(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        Livewire::test(SetPassword::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('password', 'my-new-password')
            ->set('passwordConfirmation', 'does-not-match')
            ->call('setPassword')
            ->assertHasErrors('passwordConfirmation');

        $this->assertGuest();
    }

    public function test_the_email_query_param_is_read_from_a_real_request(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->get("/set-password/{$token}?email=" . urlencode($user->email));

        $response->assertSuccessful();
        $response->assertSee('Set your password');
    }
}
