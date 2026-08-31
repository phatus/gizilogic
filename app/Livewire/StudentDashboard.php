<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

class StudentDashboard extends Component
{
    #[Layout('components.layouts.app')]
    #[On('food-uploaded')]
    public function render()
    {
        $user = Auth::user();
        $foodLogs = $user ? $user->foodLogs()->latest()->get() : collect();
        
        return view('livewire.student-dashboard', [
            'user' => $user,
            'foodLogs' => $foodLogs,
        ]);
    }

    #[\Livewire\Attributes\Computed]
    public function personalizedRecommendation()
    {
        $user = Auth::user();
        if (!$user) return null;

        $logs = $user->foodLogs()->where('created_at', '>=', now()->subDays(7))->get();
        if ($logs->isEmpty()) {
            return \App\Models\EducationModule::inRandomOrder()->first();
        }

        // Cache hasil Gemini selama 1 hari (86400 detik) agar tidak membebani kuota API saat refresh
        $cacheKey = 'gemini_rec_' . $user->id . '_' . now()->format('Y-m-d');
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($logs) {
            
            // 1. Coba Generate Rekomendasi Dinamis Pakai Gemini
            if (config('services.gemini.key')) {
                $allFoods = [];
                foreach($logs as $log) {
                    if (isset($log->detection_results['detections'])) {
                        foreach($log->detection_results['detections'] as $det) {
                            $allFoods[] = $det['class'];
                        }
                    }
                }
                
                if (!empty($allFoods)) {
                    $foodList = implode(', ', array_unique($allFoods));
                    $apiKey = config('services.gemini.key');
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
                    
                    $prompt = "Kamu adalah ahli gizi. Anak sekolah ini dalam 7 hari terakhir rutin memakan: {$foodList}. 
Tolong buatkan rekomendasi gizi mingguan dan berikan 1 resep masakan lokal khas Pacitan/Indonesia (seperti sayur kalakan, tahu tuna, pecel, lodeh) yang paling cocok untuk menutupi kekurangan nutrisinya atau menjaga keseimbangannya.
Balas dalam format JSON murni persis seperti ini (tanpa markdown):
{
  \"title\": \"Judul Rekomendasi Mingguan yang Menarik\",
  \"content\": \"Pesan evaluasi singkat maksimal 3 kalimat untuk anak SMP/SMA.\",
  \"substitution_recipe\": \"Nama resep khas lokal beserta sedikit bahan atau cara singkat membuatnya.\"
}";

                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(15)->post($url, [
                            'contents' => [['parts' => [['text' => $prompt]]]]
                        ]);
                        
                        if ($response->successful()) {
                            $text = $response->json('candidates.0.content.parts.0.text');
                            if ($text && preg_match('/\{.*\}/s', $text, $matches)) {
                                $data = json_decode($matches[0], true);
                                if ($data) {
                                    return (object) [
                                        'title' => '✨ AI: ' . ($data['title'] ?? 'Rekomendasi AI'),
                                        'content' => $data['content'] ?? '',
                                        'substitution_recipe' => $data['substitution_recipe'] ?? ''
                                    ];
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Gemini Recommendation Error: ' . $e->getMessage());
                    }
                }
            }

            // 2. Fallback: Jika Gemini gagal atau API Key kosong, gunakan Logika Template Statis
            $statuses = $logs->pluck('nutrition_status');
            $counts = ['kurang_protein' => 0, 'kurang_sayur' => 0, 'seimbang' => 0];

            foreach ($statuses as $status) {
                if ($status === 'kurang_protein_dan_sayur') {
                    $counts['kurang_protein']++;
                    $counts['kurang_sayur']++;
                } elseif (array_key_exists($status, $counts)) {
                    $counts[$status]++;
                }
            }

            $maxDeficiency = '';
            $maxCount = 0;
            
            foreach (['kurang_sayur', 'kurang_protein'] as $def) {
                if ($counts[$def] > $maxCount) {
                    $maxCount = $counts[$def];
                    $maxDeficiency = $def;
                }
            }

            if ($maxCount > 0) {
                return \App\Models\EducationModule::where('target_nutrition', $maxDeficiency)->inRandomOrder()->first();
            }

            return \App\Models\EducationModule::inRandomOrder()->first();
        });
    }
}
