<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use Illuminate\Http\Request;

class NearbyController extends Controller
{
    public function getNearby(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $category = $request->input('category', 'restaurant'); // default category

        $services = LocationService::getNearbyServices($latitude, $longitude, $category);

        if ($services) {
            return response()->json([
                'status' => 'success',
                'data' => $services,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Could not retrieve services.'], 500);
    }
}
