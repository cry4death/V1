<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'phone', 'email', 'espo_contact_id', 'espo_synced_at', 'espo_sync_status', 'espo_sync_error',
        'last_name', 'first_name', 'middle_name',
        'birth_date', 'gender', 'password',
        'refresh_token', 'refresh_token_expires_at',
        'fcm_token',
    ];

    protected $hidden = [
        'password', 'remember_token', 'refresh_token', 'fcm_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'password' => 'hashed',
            'espo_synced_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
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

    public function displayFirstName(): string
    {
        $name = $this->first_name;

        return is_string($name) ? trim($name) : '';
    }

    public function routeNotificationForMail(): ?string
    {
        $email = $this->email;

        return is_string($email) && $email !== '' ? $email : null;
    }

    public function routeNotificationForSms(): string
    {
        return (string) $this->phone;
    }
}
