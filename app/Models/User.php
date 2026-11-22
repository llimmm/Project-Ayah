<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE = [
        'admin' => 'Admin',
        'verifikator' => 'Verifikator',
        'enumerator' => 'Enumerator',
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'pasar_id', 'aktif'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function pasar()
    {
        return $this->belongsTo(Pasar::class);
    }

    public function punya(string ...$role): bool
    {
        return in_array($this->role, $role, true);
    }
}
