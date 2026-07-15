<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_visiting_root_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_guest_can_view_the_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertSuccessful();
        $response->assertSee('Sign in');
    }

    public function test_authenticated_admin_visiting_root_is_redirected_to_the_panel(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect('/admin');
    }

    public function test_authenticated_regular_user_visiting_root_is_redirected_to_my_files(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('my-files.index'));
    }

    public function test_admin_login_redirects_to_the_panel(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_regular_user_login_redirects_to_my_files(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('my-files.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_cannot_reach_my_files(): void
    {
        $response = $this->get('/my-files');

        $response->assertRedirect('/login');
    }

    public function test_regular_user_can_view_my_files(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'name' => 'Juan']);

        $response = $this->actingAs($user)->get('/my-files');

        $response->assertSuccessful();
        $response->assertSee('Juan');
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
