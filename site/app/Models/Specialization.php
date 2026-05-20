<?php

namespace App\Models;

use Database\Factories\SpecializationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialization extends Model
{
    /** @use HasFactory<SpecializationFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
