<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->password()
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8)
                    ->helperText('Leave blank to keep the current password. New accounts set their own password via the welcome email instead.'),
                Toggle::make('is_admin')
                    ->label('Administrator')
                    ->helperText('Admins have full control over every user, file, and folder.')
                    ->default(false),
            ]);
    }
}
