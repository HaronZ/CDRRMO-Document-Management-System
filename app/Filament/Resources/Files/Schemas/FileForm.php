<?php

namespace App\Filament\Resources\Files\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FileForm
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
                Select::make('folder_id')
                    ->label('Folder')
                    ->relationship('folder', 'name')
                    ->searchable()
                    ->preload()
                    ->helperText('Leave blank to place this file at the root.'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('upload')
                    ->label('File')
                    ->disk('local')
                    ->directory('files/admin-uploads')
                    ->visibility('private')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->maxSize(20480)
                    ->helperText('Uploading here creates the file with its first version.'),
                Placeholder::make('current_version_info')
                    ->label('Current version')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->content(fn (?\App\Models\File $record): string => $record?->currentVersion
                        ? "{$record->currentVersion->mime} · " . number_format($record->currentVersion->size / 1024, 1) . ' KB · uploaded ' . $record->currentVersion->created_at->diffForHumans()
                        : 'No version on record.'),
            ]);
    }
}
