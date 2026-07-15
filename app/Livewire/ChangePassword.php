<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

class ChangePassword extends Component
{
    #[Validate(['required', 'current_password'])]
    public string $currentPassword = '';

    #[Validate(['required', 'string', 'min:8', 'different:currentPassword'])]
    public string $newPassword = '';

    #[Validate(['required', 'same:newPassword'])]
    public string $newPasswordConfirmation = '';

    public bool $justUpdated = false;

    public function updatePassword(): void
    {
        $this->validate();

        auth()->user()->update(['password' => $this->newPassword]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        $this->justUpdated = true;
    }

    public function render()
    {
        return view('livewire.change-password');
    }
}
