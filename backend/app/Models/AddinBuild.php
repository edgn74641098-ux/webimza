<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddinBuild extends Model
{
    use HasFactory;

    protected $fillable = ['config_id', 'version', 'status', 'manifest_path', 'package_path', 'notes', 'created_by'];

    public function config()
    {
        return $this->belongsTo(AddinConfig::class, 'config_id');
    }
}
