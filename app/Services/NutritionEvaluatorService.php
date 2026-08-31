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
