<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\FileVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FileVersion>
 */
class FileVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => File::factory(),
            'version_no' => 1,
            'disk' => 'local',
            'path' => 'files/' . fake()->uuid() . '.pdf',
            'size' => fake()->numberBetween(1024, 1024 * 1024),
            'mime' => 'application/pdf',
            'uploaded_by_user_id' => User::factory(),
        ];
    }
}
