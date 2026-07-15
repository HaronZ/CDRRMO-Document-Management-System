<?php

namespace Tests\Feature;

use App\Livewire\MyFiles;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MyFilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_folder(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->set('newFolderName', 'Incident Reports')
            ->call('createFolder')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('folders', [
            'owner_id' => $user->id,
            'parent_id' => null,
            'name' => 'Incident Reports',
        ]);
    }

    public function test_duplicate_folder_name_in_same_parent_is_rejected(): void
    {
        $user = User::factory()->create();
        Folder::factory()->create(['owner_id' => $user->id, 'parent_id' => null, 'name' => 'Reports']);

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->set('newFolderName', 'Reports')
            ->call('createFolder')
            ->assertHasErrors('newFolderName');

        $this->assertEquals(1, Folder::where('name', 'Reports')->count());
    }

    public function test_user_can_navigate_into_a_folder(): void
    {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->call('enterFolder', $folder->id)
            ->assertSet('currentFolderId', $folder->id);
    }

    public function test_user_can_upload_a_file(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->set('upload', UploadedFile::fake()->create('incident-report.pdf', 100))
            ->call('uploadFile')
            ->assertHasNoErrors();

        $file = File::where('owner_id', $user->id)->first();
        $this->assertNotNull($file);
        $this->assertEquals('incident-report.pdf', $file->name);
        $this->assertNotNull($file->currentVersion);
        Storage::disk('local')->assertExists($file->currentVersion->path);
    }

    public function test_uploading_a_duplicate_name_auto_suffixes(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(MyFiles::class);

        $component->set('upload', UploadedFile::fake()->create('report.pdf', 50))->call('uploadFile');
        $component->set('upload', UploadedFile::fake()->create('report.pdf', 50))->call('uploadFile');

        $this->assertDatabaseHas('files', ['owner_id' => $user->id, 'name' => 'report.pdf']);
        $this->assertDatabaseHas('files', ['owner_id' => $user->id, 'name' => 'report (1).pdf']);
    }

    public function test_user_can_rename_a_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['owner_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->call('startRenameFile', $file->id)
            ->set('renameValue', 'Updated Name.pdf')
            ->call('saveRename');

        $this->assertEquals('Updated Name.pdf', $file->fresh()->name);
    }

    public function test_deleting_a_folder_soft_deletes_its_contents(): void
    {
        $user = User::factory()->create();
        $parent = Folder::factory()->create(['owner_id' => $user->id]);
        $child = Folder::factory()->create(['owner_id' => $user->id, 'parent_id' => $parent->id]);
        $file = File::factory()->create(['owner_id' => $user->id, 'folder_id' => $child->id]);

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->call('deleteFolder', $parent->id);

        $this->assertSoftDeleted($parent);
        $this->assertSoftDeleted($child);
        $this->assertSoftDeleted($file);
    }

    public function test_deleted_folder_can_be_restored_with_its_contents(): void
    {
        $user = User::factory()->create();
        $parent = Folder::factory()->create(['owner_id' => $user->id]);
        $file = File::factory()->create(['owner_id' => $user->id, 'folder_id' => $parent->id]);
        $parent->delete();
        $file->delete();

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->set('showTrash', true)
            ->call('restoreFolder', $parent->id);

        $this->assertNull($parent->fresh()->deleted_at);
        $this->assertNull($file->fresh()->deleted_at);
    }

    public function test_user_cannot_enter_another_users_folder(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $folder = Folder::factory()->create(['owner_id' => $owner->id]);

        Livewire::actingAs($intruder)
            ->test(MyFiles::class)
            ->call('enterFolder', $folder->id)
            ->assertForbidden();
    }

    public function test_user_cannot_rename_another_users_file(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $file = File::factory()->create(['owner_id' => $owner->id]);

        Livewire::actingAs($intruder)
            ->test(MyFiles::class)
            ->call('startRenameFile', $file->id)
            ->assertForbidden();
    }

    public function test_users_only_see_their_own_files_and_folders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Folder::factory()->create(['owner_id' => $user->id, 'name' => 'Mine']);
        Folder::factory()->create(['owner_id' => $other->id, 'name' => 'Not Mine']);

        Livewire::actingAs($user)
            ->test(MyFiles::class)
            ->assertSee('Mine')
            ->assertDontSee('Not Mine');
    }

    public function test_user_can_download_their_own_file(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(MyFiles::class);
        $component->set('upload', UploadedFile::fake()->create('report.pdf', 50))->call('uploadFile');

        $file = File::where('owner_id', $user->id)->first();

        $component->call('downloadFile', $file->id)
            ->assertFileDownloaded('report.pdf');
    }
}
