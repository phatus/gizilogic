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
        $apiKey = config('services.gemini.key');
        if (empty($apiKey)) {
            $this->error('ERROR: API Key kosong!');
            return;
        }
        
        $this->info('Mengambil daftar model yang didukung oleh API Key Anda...');
        $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
        
        try {
            $response = Http::timeout(15)->get($url);
            if ($response->successful()) {
                $models = $response->json('models');
                if ($models) {
                    $this->info('Model yang didukung (Pilih salah satu untuk dipakai):');
                    foreach ($models as $model) {
                        if (str_contains($model['name'], 'gemini')) {
                            $this->line('- ' . $model['name']);
                        }
                    }
                } else {
                    $this->error('Tidak ada model yang ditemukan!');
                }
            } else {
                $this->error('Gagal mengambil daftar model! HTTP ' . $response->status());
                $this->error($response->body());
            }
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
        }
    }
}
