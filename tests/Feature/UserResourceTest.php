<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->count(3)->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)->assertSuccessful();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@cdrrmo.test',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'juan@cdrrmo.test',
            'is_admin' => false,
        ]);
    }

    public function test_creating_a_user_sends_them_a_welcome_email_with_a_set_password_link(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@cdrrmo.test',
                'is_admin' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $newUser = User::where('email', 'juan@cdrrmo.test')->firstOrFail();

        Notification::assertSentTo($newUser, WelcomeNotification::class);
    }

    public function test_admin_can_resend_the_welcome_email(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->callTableAction('resendWelcomeEmail', $user);

        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_admin_cannot_resend_welcome_email_to_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertTableActionHidden('resendWelcomeEmail', $admin);
    }
}
