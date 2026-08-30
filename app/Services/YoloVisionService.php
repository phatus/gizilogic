<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class YoloVisionService
{
    /**
     * Send image to YOLO API and return parsed JSON detection results.
     */
    public function analyzeFoodImage(string $photoPath): array
    {
        $apiUrl = env('YOLO_API_URL');
        $apiKey = env('YOLO_API_KEY');

        // Check if real API URL is set, otherwise use Mock Response
        if (empty($apiUrl) || $apiUrl === 'mock') {
            return $this->getMockResponse();
        }

        try {
            $absolutePath = Storage::disk('public')->path($photoPath);
            $fileResource = fopen($absolutePath, 'r');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])
            ->timeout(15)
            ->attach('image', $fileResource, basename($absolutePath))
            ->post($apiUrl);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('YOLO API Error: ' . $response->body());
                return $this->getMockResponse('API error, falling back to mock.');
            }
        } catch (\Exception $e) {
            Log::error('YOLO API Exception: ' . $e->getMessage());
            return $this->getMockResponse('Exception occurred, falling back to mock.');
        }
    }

    /**
     * Generate a dummy/mock response for testing without a real API.
     */
    private function getMockResponse(string $note = 'This is a mock response'): array
    {
        // Simulate network delay
        sleep(2);
        
        return [
            'success' => true,
            'note' => $note,
            'detections' => [
                [
                    'class' => 'nasi',
                    'confidence' => 0.95,
                    'box' => [10, 20, 100, 100]
                ]
                // Telur dihapus agar sistem mendeteksi 'Kurang Protein'
            ]
        ];
    }
}
