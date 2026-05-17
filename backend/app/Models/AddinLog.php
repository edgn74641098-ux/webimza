<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddinLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'device_id', 'event_type', 'status', 'message', 'payload_json', 'ip_address'];

    protected $appends = ['payload_data'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function device()
    {
        return $this->hasOne(AddinDevice::class, 'device_id', 'device_id');
    }

    public function getPayloadDataAttribute(): array
    {
        if (! is_string($this->payload_json) || trim($this->payload_json) === '') {
            return [];
        }

        $decoded = json_decode($this->payload_json, true);

        return is_array($decoded) ? $decoded : [];
    }
}
