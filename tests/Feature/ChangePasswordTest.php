<?php

namespace Tests\Feature;

use App\Livewire\ChangePassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_their_own_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        Livewire::actingAs($user)
            ->test(ChangePassword::class)
            ->set('currentPassword', 'old-password')
            ->set('newPassword', 'new-password-123')
            ->set('newPasswordConfirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        Livewire::actingAs($user)
            ->test(ChangePassword::class)
            ->set('currentPassword', 'totally-wrong')
            ->set('newPassword', 'new-password-123')
            ->set('newPasswordConfirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasErrors('currentPassword');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_mismatched_confirmation_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        Livewire::actingAs($user)
            ->test(ChangePassword::class)
            ->set('currentPassword', 'old-password')
            ->set('newPassword', 'new-password-123')
            ->set('newPasswordConfirmation', 'does-not-match')
            ->call('updatePassword')
            ->assertHasErrors('newPasswordConfirmation');
    }

    public function test_guest_cannot_reach_change_password_page(): void
    {
        $response = $this->get('/account/password');

        $response->assertRedirect('/login');
    }
}
