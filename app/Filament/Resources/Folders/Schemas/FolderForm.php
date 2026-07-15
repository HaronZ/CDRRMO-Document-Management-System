<?php

namespace App\Filament\Resources\Folders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FolderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('owner_id')
                    ->label('Owner')
                    ->relationship('owner', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('parent_id')
                    ->label('Parent folder')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Leave blank for a root-level folder.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
