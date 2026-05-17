<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['assignment_type', 'user_id', 'department_id', 'group_id', 'template_id', 'priority', 'is_active', 'starts_at', 'ends_at'];

    protected $casts = ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function template()
    {
        return $this->belongsTo(SignatureTemplate::class, 'template_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
