<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class AddinPingController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'success' => true,
            'service' => 'addin-api',
            'time' => now()->toIso8601String(),
        ]);
    }
}

