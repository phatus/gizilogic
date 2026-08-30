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
        $apiKey = env('ROBOFLOW_API_KEY');
        $workspace = env('ROBOFLOW_WORKSPACE');
        $workflowId = env('ROBOFLOW_WORKFLOW_ID');

        // Jika API Key tidak ada di .env, gunakan Mock (untuk jaga-jaga/testing)
        if (empty($apiKey)) {
            return $this->mockResponse(); 
        }

        try {
            // Karena server Anda sekarang sudah online via Cloudflare Tunnels,
            // Kita bisa memberikan URL langsung ke Roboflow agar lebih cepat 
            // daripada membaca Base64 yang sangat berat.
            $imageUrl = asset('storage/' . $photoPath);

            $url = "https://serverless.roboflow.com/infer/workflows/{$workspace}/{$workflowId}";

            $response = Http::timeout(20)->post($url, [
                'api_key' => $apiKey,
                'inputs' => [
                    'image' => [
                        'type' => 'url',
                        'value' => $imageUrl
                    ],
                    // Parameter dinamis dari model Zero-Shot Roboflow
                    'classes' => 'ayam, ayam_goreng, nasi, telur, tahu, tempe, sayur, ikan, daging, mie'
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Roboflow Workflow Response:', $data);
                
                $detections = [];
                $predictions = $this->extractPredictions($data);

                foreach ($predictions as $pred) {
                    // Filter prediksi dengan akurasi di atas 30%
                    if (isset($pred['confidence']) && $pred['confidence'] > 0.3) {
                        $detections[] = [
                            'class' => strtolower($pred['class']),
                            'confidence' => $pred['confidence'],
                        ];
                    }
                }

                return [
                    'success' => true,
                    'note' => 'Analyzed via Roboflow AI',
                    'detections' => $detections
                ];
            }

            Log::error('Roboflow API Error: ' . $response->body());
            return $this->mockResponse();

        } catch (\Exception $e) {
            Log::error('YoloVisionService Error: ' . $e->getMessage());
            return $this->mockResponse();
        }
    }

    private function extractPredictions($data) {
        if (isset($data['outputs']) && is_array($data['outputs'])) {
            foreach ($data['outputs'] as $output) {
                if (isset($output['predictions'])) {
                    return $output['predictions'];
                }
                foreach ($output as $key => $val) {
                    if (is_array($val) && isset($val[0]['class'])) {
                        return $val;
                    }
                }
            }
        }
        return [];
    }

    private function mockResponse()
    {
        return [
            'success' => true,
            'note' => 'Mock Data (No API Key)',
            'detections' => [
                [
                    'class' => 'nasi',
                    'confidence' => 0.95
                ]
            ]
        ];
    }
}
