<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['file_id', 'version_no', 'disk', 'path', 'size', 'mime', 'uploaded_by_user_id'])]
class FileVersion extends Model
{
    use HasFactory;

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
