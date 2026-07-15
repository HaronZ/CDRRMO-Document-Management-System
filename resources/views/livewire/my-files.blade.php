<div class="min-h-screen bg-gray-50">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900">My Files</h1>
            <div class="flex items-center gap-3 text-sm text-gray-600">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-amber-700 hover:text-amber-800 font-medium">Sign out</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-6">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center flex-wrap gap-1 text-sm text-gray-500 mb-4">
            <button wire:click="goToRoot" class="hover:text-amber-700 font-medium {{ $currentFolderId ? '' : 'text-gray-900' }}">
                My Files
            </button>
            @foreach ($breadcrumbs as $crumb)
                <span>/</span>
                <button
                    wire:click="enterFolder({{ $crumb->id }})"
                    class="hover:text-amber-700 font-medium {{ $currentFolderId === $crumb->id ? 'text-gray-900' : '' }}"
                >
                    {{ $crumb->name }}
                </button>
            @endforeach
        </nav>

        {{-- Toolbar --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 mb-4 flex flex-wrap items-center gap-3">
            <form wire:submit="createFolder" class="flex items-center gap-2">
                <input
                    type="text"
                    wire:model="newFolderName"
                    placeholder="New folder name"
                    class="rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-amber-500 focus:ring-amber-500"
                >
                <button type="submit" class="rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-3 py-2">
                    + New folder
                </button>
            </form>
            @error('newFolderName') <p class="text-xs text-red-600 w-full">{{ $message }}</p> @enderror

            <form wire:submit="uploadFile" class="flex items-center gap-2">
                <input type="file" wire:model="upload" class="text-sm">
                <button
                    type="submit"
                    class="rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-3 py-2"
                    wire:loading.attr="disabled"
                    wire:target="upload,uploadFile"
                >
                    <span wire:loading.remove wire:target="uploadFile">Upload</span>
                    <span wire:loading wire:target="uploadFile">Uploading…</span>
                </button>
            </form>
            @error('upload') <p class="text-xs text-red-600 w-full">{{ $message }}</p> @enderror

            <button
                wire:click="toggleTrash"
                class="ml-auto text-sm font-medium {{ $showTrash ? 'text-amber-700' : 'text-gray-500 hover:text-gray-700' }}"
            >
                {{ $showTrash ? '← Back to files' : 'Trash' }}
            </button>
        </div>

        @if ($showTrash)
            <p class="text-sm text-gray-500 mb-3">Items in Trash can be restored. They are not shown in your regular file view.</p>
        @endif

        {{-- Folders --}}
        @if ($folders->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-4">
                @foreach ($folders as $folder)
                    <div class="bg-white border border-gray-200 rounded-xl p-3 flex flex-col gap-2">
                        @if ($renamingType === 'folder' && $renamingId === $folder->id)
                            <form wire:submit="saveRename" class="flex flex-col gap-1">
                                <input
                                    type="text"
                                    wire:model="renameValue"
                                    autofocus
                                    class="rounded-lg border-gray-300 text-sm px-2 py-1"
                                >
                                <div class="flex gap-2 text-xs">
                                    <button type="submit" class="text-amber-700 font-medium">Save</button>
                                    <button type="button" wire:click="cancelRename" class="text-gray-500">Cancel</button>
                                </div>
                            </form>
                        @else
                            @if ($showTrash)
                                <div class="flex items-center gap-2 text-left">
                                    <x-heroicon-o-folder class="w-6 h-6 text-amber-500 shrink-0" />
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $folder->name }}</span>
                                </div>
                            @else
                                <button wire:click="enterFolder({{ $folder->id }})" class="flex items-center gap-2 text-left">
                                    <x-heroicon-o-folder class="w-6 h-6 text-amber-500 shrink-0" />
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $folder->name }}</span>
                                </button>
                            @endif
                            <div class="flex gap-3 text-xs">
                                @if ($showTrash)
                                    <button wire:click="restoreFolder({{ $folder->id }})" class="text-amber-700 font-medium">Restore</button>
                                @else
                                    <button wire:click="startRenameFolder({{ $folder->id }})" class="text-gray-500 hover:text-gray-700">Rename</button>
                                    <button
                                        wire:click="deleteFolder({{ $folder->id }})"
                                        wire:confirm="Move '{{ $folder->name }}' and everything inside it to Trash?"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Files --}}
        @if ($files->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100">
                @foreach ($files as $file)
                    <div class="p-3 flex items-center gap-3">
                        <x-heroicon-o-document class="w-6 h-6 text-gray-400 shrink-0" />

                        @if ($renamingType === 'file' && $renamingId === $file->id)
                            <form wire:submit="saveRename" class="flex items-center gap-2 flex-1">
                                <input
                                    type="text"
                                    wire:model="renameValue"
                                    autofocus
                                    class="rounded-lg border-gray-300 text-sm px-2 py-1 flex-1"
                                >
                                <button type="submit" class="text-xs text-amber-700 font-medium">Save</button>
                                <button type="button" wire:click="cancelRename" class="text-xs text-gray-500">Cancel</button>
                            </form>
                        @else
                            <span class="text-sm font-medium text-gray-800 flex-1 truncate">{{ $file->name }}</span>

                            <div class="flex gap-3 text-xs shrink-0">
                                @if ($showTrash)
                                    <button wire:click="restoreFile({{ $file->id }})" class="text-amber-700 font-medium">Restore</button>
                                @else
                                    <button wire:click="downloadFile({{ $file->id }})" class="text-gray-500 hover:text-gray-700">Download</button>
                                    <button wire:click="startRenameFile({{ $file->id }})" class="text-gray-500 hover:text-gray-700">Rename</button>
                                    <button
                                        wire:click="deleteFile({{ $file->id }})"
                                        wire:confirm="Move '{{ $file->name }}' to Trash?"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($folders->isEmpty() && $files->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-sm">{{ $showTrash ? 'Trash is empty.' : 'This folder is empty. Create a folder or upload a file to get started.' }}</p>
            </div>
        @endif
    </main>
</div>
