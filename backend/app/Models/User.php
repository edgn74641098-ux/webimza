<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'entra_id','name','email','username','domain','department_id','title','company','phone','mobile','office','address','website','is_active','source','last_seen_at','password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function addinDevices()
    {
        return $this->hasMany(AddinDevice::class);
    }

    public function signatureAssignments()
    {
        return $this->hasMany(SignatureAssignment::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
