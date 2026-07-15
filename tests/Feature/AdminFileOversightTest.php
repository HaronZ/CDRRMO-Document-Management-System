<?php

namespace Tests\Feature;

use App\Filament\Resources\Files\Pages\ListFiles;
use App\Filament\Resources\Folders\Pages\ListFolders;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminFileOversightTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_folders_and_files_belonging_to_every_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Folder::factory()->create(['owner_id' => $userA->id, 'name' => 'Folder A']);
        Folder::factory()->create(['owner_id' => $userB->id, 'name' => 'Folder B']);
        File::factory()->create(['owner_id' => $userA->id, 'name' => 'file-a.pdf']);
        File::factory()->create(['owner_id' => $userB->id, 'name' => 'file-b.pdf']);

        $this->actingAs($admin);

        Livewire::test(ListFolders::class)
            ->assertSee('Folder A')
            ->assertSee('Folder B');

        Livewire::test(ListFiles::class)
            ->assertSee('file-a.pdf')
            ->assertSee('file-b.pdf');
    }

    public function test_regular_user_cannot_reach_admin_folder_or_file_resources(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user);

        $this->get('/admin/folders')->assertForbidden();
        $this->get('/admin/files')->assertForbidden();
    }

    public function test_admin_can_delete_and_restore_any_users_folder(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $folder = Folder::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($admin);

        $folder->delete();
        $this->assertSoftDeleted($folder);

        $folder->restore();
        $this->assertNull($folder->fresh()->deleted_at);
    }

    public function test_admin_can_create_a_file_on_behalf_of_a_user_and_it_gets_a_version(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\Files\Pages\CreateFile::class)
            ->fillForm([
                'owner_id' => $owner->id,
                'name' => 'memo.pdf',
                'upload' => UploadedFile::fake()->create('memo.pdf', 80),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $file = File::where('name', 'memo.pdf')->firstOrFail();
        $this->assertEquals($owner->id, $file->owner_id);
        $this->assertNotNull($file->currentVersion);
        Storage::disk('local')->assertExists($file->currentVersion->path);
    }

    public function test_admin_can_download_any_users_file(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(\App\Filament\Resources\Files\Pages\CreateFile::class)
            ->fillForm([
                'owner_id' => $owner->id,
                'name' => 'incident.pdf',
                'upload' => UploadedFile::fake()->create('incident.pdf', 60),
            ])
            ->call('create');

        $file = File::where('name', 'incident.pdf')->firstOrFail();

        Livewire::test(ListFiles::class)
            ->callTableAction('download', $file)
            ->assertFileDownloaded('incident.pdf');
    }
}
