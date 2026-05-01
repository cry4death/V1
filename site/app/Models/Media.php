<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'filename', 'original_name', 'path',
        'size', 'mime_type', 'folder',
    ];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
