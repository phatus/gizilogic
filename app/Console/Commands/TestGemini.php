<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGemini extends Command
{
    protected $signature = 'test:gemini';
    protected $description = 'Test Gemini API integration';

    public function handle()
    {
        $this->info('Starting Gemini API Test...');

        $apiKey = config('services.gemini.key');
        
        if (empty($apiKey)) {
            $this->error('ERROR: API Key kosong/tidak terbaca oleh Laravel!');
            return;
        }
        
        $this->info('API Key Terbaca: ' . substr($apiKey, 0, 5) . '***');
        $this->info('Menguji koneksi ke Google Gemini...');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        $prompt = "Jawab dengan JSON: {\"status\":\"ok\"}";

        try {
            $response = Http::timeout(15)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            $this->info('Status HTTP: ' . $response->status());
            $this->info('Respons: ' . $response->body());
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}
