<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class SetPassword extends Component
{
    public string $token;

    public string $email;

    public string $password = '';

    public string $passwordConfirmation = '';

    public bool $invalid = false;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function setPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8'],
            'passwordConfirmation' => ['required', 'same:password'],
        ]);

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->passwordConfirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
                Auth::login($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->invalid = true;

            return;
        }

        $this->redirect(route('home'), navigate: false);
    }

    public function render()
    {
        return view('livewire.set-password');
    }
}
