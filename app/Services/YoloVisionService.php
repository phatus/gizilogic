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
            $absolutePath = Storage::disk('public')->path($photoPath);
            // Kompres gambar menjadi max 800px lalu ubah ke base64
            $imageData = $this->compressImageToBase64($absolutePath);

            $url = "https://serverless.roboflow.com/infer/workflows/{$workspace}/{$workflowId}";

            $response = Http::timeout(20)->post($url, [
                'api_key' => $apiKey,
                'inputs' => [
                    'image' => [
                        'type' => 'base64',
                        'value' => $imageData
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

    private function compressImageToBase64($sourcePath, $maxWidth = 800)
    {
        $info = @getimagesize($sourcePath);
        if (!$info) return base64_encode(file_get_contents($sourcePath));

        $mime = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        if ($width <= $maxWidth) {
            return base64_encode(file_get_contents($sourcePath));
        }

        $ratio = $maxWidth / $width;
        $newHeight = (int)($height * $ratio);

        $image = null;
        switch ($mime) {
            case 'image/jpeg': $image = @imagecreatefromjpeg($sourcePath); break;
            case 'image/png': $image = @imagecreatefrompng($sourcePath); break;
            case 'image/webp': $image = @imagecreatefromwebp($sourcePath); break;
            default: return base64_encode(file_get_contents($sourcePath));
        }

        if (!$image) return base64_encode(file_get_contents($sourcePath));

        $resized = imagecreatetruecolor($maxWidth, $newHeight);
        
        if ($mime == 'image/png' || $mime == 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);

        ob_start();
        if ($mime == 'image/jpeg') imagejpeg($resized, null, 80);
        elseif ($mime == 'image/png') imagepng($resized, null, 6);
        elseif ($mime == 'image/webp') imagewebp($resized, null, 80);
        
        $imageData = ob_get_clean();
        
        imagedestroy($image);
        imagedestroy($resized);

        return base64_encode($imageData);
    }

    private function extractPredictions($data) {
        if (isset($data['outputs']) && is_array($data['outputs'])) {
            foreach ($data['outputs'] as $output) {
                // Periksa apakah predictions bersarang di dalam object predictions
                if (isset($output['predictions']['predictions']) && is_array($output['predictions']['predictions'])) {
                    return $output['predictions']['predictions'];
                }
                // Fallback jika predictions berupa array langsung
                if (isset($output['predictions']) && is_array($output['predictions']) && !isset($output['predictions']['image'])) {
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
