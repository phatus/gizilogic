<?php

namespace App\Services;

use App\Models\EducationModule;

class NutritionEvaluatorService
{
    // Keyword klasifikasi deteksi YOLO (Bilingual)
    private array $proteinKeywords = ['telur', 'ikan', 'ayam', 'daging', 'tahu', 'tempe', 'egg', 'fish', 'chicken', 'meat', 'tofu', 'tempeh', 'beef'];
    private array $vegetableKeywords = ['sayur', 'bayam', 'kangkung', 'wortel', 'tomat', 'brokoli', 'kubis', 'vegetable', 'spinach', 'carrot', 'tomato', 'broccoli', 'cabbage'];
    private array $carbKeywords = ['nasi', 'mie', 'kentang', 'roti', 'jagung', 'singkong', 'rice', 'noodle', 'potato', 'bread', 'corn', 'cassava'];

    /**
     * Evaluasi hasil deteksi dan kembalikan status serta modul edukasi (jika ada).
     */
    public function evaluate(array $detectionResults): array
    {
        $detections = $detectionResults['detections'] ?? [];
        $classes = array_column($detections, 'class');
        
        // Coba gunakan Gemini AI jika API key tersedia
        if (config('services.gemini.key') && !empty($classes)) {
            $geminiEvaluation = $this->evaluateWithGemini($classes);
            if ($geminiEvaluation) {
                $combinedModule = null;
                // Selalu kembalikan modul jika status tidak seimbang atau ada konten edukasi
                if (!empty($geminiEvaluation['content']) && ($geminiEvaluation['status'] ?? 'seimbang') !== 'seimbang') {
                    $combinedModule = new EducationModule([
                        'title' => $geminiEvaluation['title'] ?? 'Pesan Gizi',
                        'content' => $geminiEvaluation['content'] ?? '',
                        'substitution_recipe' => $geminiEvaluation['recipe'] ?? ''
                    ]);
                }
                
                return [
                    'status' => $geminiEvaluation['status'] ?? 'seimbang',
                    'module' => $combinedModule,
                    'nutrition_facts' => $geminiEvaluation['nutrition_facts'] ?? []
                ];
            }
        }
        
        $hasProtein = false;
        $hasVeggie = false;
        $hasCarb = false;

        foreach ($detections as $item) {
            $class = strtolower($item['class'] ?? '');
            
            foreach ($this->proteinKeywords as $keyword) {
                if (str_contains($class, $keyword)) $hasProtein = true;
            }
            foreach ($this->vegetableKeywords as $keyword) {
                if (str_contains($class, $keyword)) $hasVeggie = true;
            }
            foreach ($this->carbKeywords as $keyword) {
                if (str_contains($class, $keyword)) $hasCarb = true;
            }
        }

        $status = 'seimbang';
        $targets = [];

        if (!$hasProtein) {
            $targets[] = 'kurang_protein';
        }
        if (!$hasVeggie) {
            $targets[] = 'kurang_sayur';
        }

        if (count($targets) == 2) {
            $status = 'kurang_protein_dan_sayur';
        } elseif (count($targets) == 1) {
            $status = $targets[0];
        }

        $combinedModule = null;
        if (!empty($targets)) {
            $titles = [];
            $contents = [];
            $recipes = [];

            foreach ($targets as $target) {
                $mod = EducationModule::where('target_nutrition', $target)->inRandomOrder()->first();
                if ($mod) {
                    $titles[] = $mod->title;
                    $contents[] = $mod->content;
                    if ($mod->substitution_recipe) {
                        $recipes[] = $mod->substitution_recipe;
                    }
                }
            }

            if (!empty($contents)) {
                $combinedModule = new EducationModule([
                    'title' => count($titles) > 1 ? 'Pentingnya Protein & Sayur' : $titles[0],
                    'content' => implode("\n\n---\n\n", $contents),
                    'substitution_recipe' => implode("\n\n---\n\n", $recipes)
                ]);
            }
        }

        return [
            'status' => $status,
            'module' => $combinedModule
        ];
    }

    /**
     * Integrasi Gemini AI untuk evaluasi yang dinamis
     */
    private function evaluateWithGemini(array $classes): ?array
    {
        $apiKey = config('services.gemini.key');
        // Gunakan model terbaru yang didukung oleh API Key (Gemini 2.5 Flash)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";
        
        $foodList = implode(', ', $classes);
        $prompt = "Kamu adalah sistem ahli gizi otomatis untuk aplikasi sekolah GiziLogic. Anak sekolah ini memfoto makanannya dan AI vision mendeteksi item ini: {$foodList}. 
Tolong evaluasi gizinya dan berikan balasan dalam format JSON murni persis seperti struktur berikut (TANPA markdown blok ```json, berikan langsung JSON-nya):
{
  \"status\": \"salah satu dari tepat ini: seimbang, kurang_sayur, kurang_protein, kurang_protein_dan_sayur\",
  \"title\": \"Judul edukasi gizi yang memotivasi anak (contoh: Wah, Makananmu Keren!)\",
  \"content\": \"Pesan edukasi atau pujian maksimal 3 kalimat yang ramah untuk anak SMP/SMA.\",
  \"recipe\": \"Jika kurang gizi, berikan rekomendasi resep masakan rumahan lokal Indonesia. Jika sudah seimbang, biarkan string kosong\",
  \"nutrition_facts\": [
     {\"name\": \"Nama makanan\", \"icon\": \"Emoji makanan\", \"desc\": \"Estimasi kalori dan 1 kalimat ringkas manfaat nutrisinya\"}
  ]
}";

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(20)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                if ($text) {
                    // Coba ektrak blok JSON murni menggunakan regex jika Gemini menyelipkan teks lain
                    if (preg_match('/\{.*\}/s', $text, $matches)) {
                        return json_decode($matches[0], true);
                    }
                }
            }
            \Illuminate\Support\Facades\Log::error('Gemini API Error: ' . $response->body());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gemini Exception: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Mengembalikan fakta gizi berdasarkan kelas makanan yang terdeteksi
     */
    public function getNutritionFacts(array $detectedClasses): array
    {
        $dictionary = [
            'nasi' => ['icon' => '🍚', 'desc' => 'Sumber energi utama (Karbohidrat ~130 Kkal/100g).'],
            'mie' => ['icon' => '🍜', 'desc' => 'Karbohidrat cepat saji (~138 Kkal/100g). Konsumsi secukupnya.'],
            'kentang' => ['icon' => '🥔', 'desc' => 'Karbohidrat kompleks yang mengenyangkan, tinggi kalium.'],
            'roti' => ['icon' => '🍞', 'desc' => 'Sumber karbohidrat yang praktis. Pilih gandum utuh lebih baik.'],
            'jagung' => ['icon' => '🌽', 'desc' => 'Karbohidrat kaya serat dan antioksidan lutein.'],
            'singkong' => ['icon' => '🍠', 'desc' => 'Sumber karbohidrat lokal yang mengenyangkan (gaplek/thiwul).'],
            'ayam' => ['icon' => '🍗', 'desc' => 'Protein hewani tinggi (~165 Kkal, 31g Protein/100g).'],
            'daging' => ['icon' => '🥩', 'desc' => 'Tinggi zat besi & protein hewani untuk cegah anemia.'],
            'ikan' => ['icon' => '🐟', 'desc' => 'Kaya protein & Omega-3 yang sangat baik untuk kecerdasan otak.'],
            'telur' => ['icon' => '🥚', 'desc' => 'Protein hewani terbaik & terjangkau, mengandung kolin.'],
            'tahu' => ['icon' => '🟨', 'desc' => 'Protein nabati dari kedelai, rendah kalori (~76 Kkal/100g).'],
            'tempe' => ['icon' => '🟫', 'desc' => 'Protein nabati asli Indonesia, hasil fermentasi yang sangat sehat.'],
            'sayur' => ['icon' => '🥗', 'desc' => 'Sangat kaya serat, mineral, & vitamin. Penting untuk pencernaan!'],
            'bayam' => ['icon' => '🥬', 'desc' => 'Tinggi zat besi dan kalsium alami.'],
            'kangkung' => ['icon' => '🥬', 'desc' => 'Banyak serat dan vitamin A untuk mata.'],
            'wortel' => ['icon' => '🥕', 'desc' => 'Kaya akan beta-karoten (Vitamin A).'],
            'tomat' => ['icon' => '🍅', 'desc' => 'Kaya antioksidan likopen & Vitamin C.'],
            'brokoli' => ['icon' => '🥦', 'desc' => 'Superfood! Tinggi antioksidan dan serat.'],
            'kubis' => ['icon' => '🥬', 'desc' => 'Rendah kalori, kaya vitamin C & K.'],
        ];

        $facts = [];
        foreach ($detectedClasses as $cls) {
            $key = strtolower(trim($cls));
            if (isset($dictionary[$key])) {
                $facts[] = [
                    'name' => ucfirst($key),
                    'icon' => $dictionary[$key]['icon'],
                    'desc' => $dictionary[$key]['desc']
                ];
            } else {
                $facts[] = [
                    'name' => ucfirst($key),
                    'icon' => '🍽️',
                    'desc' => 'Sumber kalori dan nutrisi umum.'
                ];
            }
        }
        
        return $facts;
    }
}
