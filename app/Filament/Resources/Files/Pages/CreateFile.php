<?php

namespace App\Filament\Resources\Files\Pages;

use App\Filament\Resources\Files\FileResource;
use App\Models\FileVersion;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateFile extends CreateRecord
{
    protected static string $resource = FileResource::class;

    protected static bool $canCreateAnother = false;

    protected ?string $uploadedPath = null;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->uploadedPath = $data['upload'] ?? null;
        unset($data['upload']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->uploadedPath) {
            return;
        }

        $disk = 'local';

        $version = FileVersion::create([
            'file_id' => $this->record->id,
            'version_no' => 1,
            'disk' => $disk,
            'path' => $this->uploadedPath,
            'size' => Storage::disk($disk)->size($this->uploadedPath),
            'mime' => Storage::disk($disk)->mimeType($this->uploadedPath),
            'uploaded_by_user_id' => auth()->id(),
        ]);

        $this->record->update(['current_version_id' => $version->id]);
    }
}
