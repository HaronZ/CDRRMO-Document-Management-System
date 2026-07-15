<?php

namespace App\Livewire;

use App\Models\File;
use App\Models\FileVersion;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class MyFiles extends Component
{
    use WithFileUploads;

    #[Url(as: 'folder')]
    public ?int $currentFolderId = null;

    public bool $showTrash = false;

    public string $newFolderName = '';

    public $upload = null;

    public ?string $renamingType = null;

    public ?int $renamingId = null;

    public string $renameValue = '';

    public function mount(): void
    {
        if ($this->currentFolderId) {
            $folder = Folder::find($this->currentFolderId);

            if (! $folder || $folder->owner_id !== auth()->id()) {
                $this->currentFolderId = null;
            }
        }
    }

    public function currentFolder()
    {
        return $this->currentFolderId ? Folder::find($this->currentFolderId) : null;
    }

    public function breadcrumbs()
    {
        $trail = [];
        $folder = $this->currentFolder();

        while ($folder) {
            array_unshift($trail, $folder);
            $folder = $folder->parent;
        }

        return $trail;
    }

    public function folders()
    {
        $query = Folder::where('owner_id', auth()->id())
            ->where('parent_id', $this->currentFolderId);

        return $this->showTrash
            ? $query->onlyTrashed()->latest('deleted_at')->get()
            : $query->orderBy('name')->get();
    }

    public function files()
    {
        $query = File::where('owner_id', auth()->id())
            ->where('folder_id', $this->currentFolderId);

        return $this->showTrash
            ? $query->onlyTrashed()->latest('deleted_at')->get()
            : $query->orderBy('name')->get();
    }

    public function enterFolder(int $folderId): void
    {
        $folder = Folder::findOrFail($folderId);
        $this->authorize('view', $folder);

        $this->currentFolderId = $folderId;
    }

    public function goToRoot(): void
    {
        $this->currentFolderId = null;
    }

    public function toggleTrash(): void
    {
        $this->showTrash = ! $this->showTrash;
    }

    public function createFolder(): void
    {
        $this->authorize('create', Folder::class);

        $this->validate([
            'newFolderName' => ['required', 'string', 'max:255'],
        ]);

        $name = trim($this->newFolderName);

        $exists = Folder::where('owner_id', auth()->id())
            ->where('parent_id', $this->currentFolderId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->exists();

        if ($exists) {
            $this->addError('newFolderName', 'A folder with that name already exists here.');

            return;
        }

        Folder::create([
            'owner_id' => auth()->id(),
            'parent_id' => $this->currentFolderId,
            'name' => $name,
        ]);

        $this->newFolderName = '';
    }

    public function uploadFile(): void
    {
        $this->authorize('create', File::class);

        $this->validate([
            'upload' => ['required', 'file', 'max:20480'],
        ]);

        $originalName = $this->upload->getClientOriginalName();
        $name = $this->uniqueFileName($originalName);

        $path = $this->upload->store('files/' . auth()->id(), 'local');

        $file = File::create([
            'owner_id' => auth()->id(),
            'folder_id' => $this->currentFolderId,
            'name' => $name,
        ]);

        $version = FileVersion::create([
            'file_id' => $file->id,
            'version_no' => 1,
            'disk' => 'local',
            'path' => $path,
            'size' => $this->upload->getSize(),
            'mime' => $this->upload->getMimeType(),
            'uploaded_by_user_id' => auth()->id(),
        ]);

        $file->update(['current_version_id' => $version->id]);

        $this->upload = null;
    }

    protected function uniqueFileName(string $name): string
    {
        $query = fn (string $candidate) => File::where('owner_id', auth()->id())
            ->where('folder_id', $this->currentFolderId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($candidate)])
            ->exists();

        if (! $query($name)) {
            return $name;
        }

        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        $i = 1;
        do {
            $candidate = $extension ? "{$base} ({$i}).{$extension}" : "{$base} ({$i})";
            $i++;
        } while ($query($candidate));

        return $candidate;
    }

    public function startRenameFolder(int $folderId): void
    {
        $folder = Folder::findOrFail($folderId);
        $this->authorize('update', $folder);

        $this->renamingType = 'folder';
        $this->renamingId = $folderId;
        $this->renameValue = $folder->name;
    }

    public function startRenameFile(int $fileId): void
    {
        $file = File::findOrFail($fileId);
        $this->authorize('update', $file);

        $this->renamingType = 'file';
        $this->renamingId = $fileId;
        $this->renameValue = $file->name;
    }

    public function saveRename(): void
    {
        $this->validate([
            'renameValue' => ['required', 'string', 'max:255'],
        ]);

        if ($this->renamingType === 'folder') {
            $folder = Folder::findOrFail($this->renamingId);
            $this->authorize('update', $folder);
            $folder->update(['name' => trim($this->renameValue)]);
        } elseif ($this->renamingType === 'file') {
            $file = File::findOrFail($this->renamingId);
            $this->authorize('update', $file);
            $file->update(['name' => trim($this->renameValue)]);
        }

        $this->cancelRename();
    }

    public function cancelRename(): void
    {
        $this->renamingType = null;
        $this->renamingId = null;
        $this->renameValue = '';
    }

    public function deleteFolder(int $folderId): void
    {
        $folder = Folder::findOrFail($folderId);
        $this->authorize('delete', $folder);

        $this->softDeleteFolderRecursively($folder);
    }

    protected function softDeleteFolderRecursively(Folder $folder): void
    {
        $folder->files()->get()->each->delete();

        $folder->children()->get()->each(
            fn (Folder $child) => $this->softDeleteFolderRecursively($child)
        );

        $folder->delete();
    }

    public function deleteFile(int $fileId): void
    {
        $file = File::findOrFail($fileId);
        $this->authorize('delete', $file);

        $file->delete();
    }

    public function restoreFolder(int $folderId): void
    {
        $folder = Folder::onlyTrashed()->findOrFail($folderId);
        $this->authorize('restore', $folder);

        $this->restoreFolderRecursively($folder);
    }

    protected function restoreFolderRecursively(Folder $folder): void
    {
        $folder->restore();

        Folder::onlyTrashed()->where('parent_id', $folder->id)->get()
            ->each(fn (Folder $child) => $this->restoreFolderRecursively($child));

        File::onlyTrashed()->where('folder_id', $folder->id)->get()->each->restore();
    }

    public function restoreFile(int $fileId): void
    {
        $file = File::onlyTrashed()->findOrFail($fileId);
        $this->authorize('restore', $file);

        $file->restore();
    }

    public function downloadFile(int $fileId)
    {
        $file = File::findOrFail($fileId);
        $this->authorize('view', $file);

        return Storage::disk($file->currentVersion->disk)
            ->download($file->currentVersion->path, $file->name);
    }

    public function render()
    {
        return view('livewire.my-files', [
            'folders' => $this->folders(),
            'files' => $this->files(),
            'breadcrumbs' => $this->breadcrumbs(),
        ]);
    }
}
