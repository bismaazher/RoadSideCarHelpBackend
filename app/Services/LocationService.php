<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LocationService
{
    /**
     * Get nearby services using LocationIQ's Nearby API
     *
     * @param float $latitude
     * @param float $longitude
     * @param string $category
     * @return array|null
     */
    public static function getNearbyServices(float $latitude, float $longitude, string $category): ?array
    {

        $apiKey = "pk.7f84b1c923e0c626c39a02f2fadb0631";
        $endpoint = 'https://us1.locationiq.com/v1/nearby.php';

        $response = Http::get($endpoint, [
            'key' => $apiKey,
            'lat' => $latitude,
            'lon' => $longitude,
            'tag' => $category,
            'radius' => 5000,
            'limit' => 10,
            'format' => 'json'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('LocationIQ API error:', $response->json());

        return null;
    }
}
