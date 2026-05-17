<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddinConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','api_base_url','manifest_id','manifest_version','provider_name','display_name','support_url','taskpane_url','autorun_url','icon_url','highres_icon_url','allowed_domains','tenant_mode','is_active','updated_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function builds()
    {
        return $this->hasMany(AddinBuild::class, 'config_id');
    }
}
