<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddinDevice extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','email','display_name','client_type','platform','host','office_version','addin_version','device_id','last_ip','last_seen_at','last_check_at','last_signature_version','status'];

    protected $casts = ['last_seen_at' => 'datetime', 'last_check_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
