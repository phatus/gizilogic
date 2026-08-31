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
}
