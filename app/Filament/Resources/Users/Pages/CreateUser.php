<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Notifications\WelcomeNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static bool $canCreateAnother = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // New accounts set their own password via the welcome email's
        // "set your password" link, so this value is never used to log in.
        $data['password'] = Str::random(40);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->notify(new WelcomeNotification());
    }
}
