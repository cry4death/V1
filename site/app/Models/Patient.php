<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Patient extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'phone', 'espo_contact_id', 'last_name', 'first_name', 'middle_name',
        'birth_date', 'gender', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'password' => 'hashed',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function displayName(): string
    {
        $parts = array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ], fn (?string $p) => filled($p));

        return implode(' ', $parts);
    }
}
