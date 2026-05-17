<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignatureVersion extends Model
{
    use HasFactory;

    protected $fillable = ['version', 'description', 'created_by'];
}
