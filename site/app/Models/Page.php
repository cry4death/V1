<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'content'];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }
}
