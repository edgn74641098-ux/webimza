<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name','description','type','html_content','text_content','is_default','is_active','version','created_by','updated_by'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function assignments()
    {
        return $this->hasMany(SignatureAssignment::class, 'template_id');
    }
}
