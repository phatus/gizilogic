<?php

namespace App\Services;

use App\Models\EducationModule;

class NutritionEvaluatorService
{
    // Keyword klasifikasi deteksi YOLO (Bisa diperluas)
    private array $proteinKeywords = ['telur', 'ikan', 'ayam', 'daging', 'tahu', 'tempe'];
    private array $vegetableKeywords = ['sayur', 'bayam', 'kangkung', 'wortel', 'tomat', 'brokoli', 'kubis'];
    private array $carbKeywords = ['nasi', 'mie', 'kentang', 'roti', 'jagung', 'singkong'];

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
            
            if (in_array($class, $this->proteinKeywords)) $hasProtein = true;
            if (in_array($class, $this->vegetableKeywords)) $hasVeggie = true;
            if (in_array($class, $this->carbKeywords)) $hasCarb = true;
        }

        $status = 'seimbang';
        $targetNutrition = null;

        if (!$hasProtein && !$hasVeggie) {
            $status = 'kurang_protein_dan_sayur';
            $targetNutrition = 'kurang_protein'; // Prioritas edukasi
        } elseif (!$hasProtein) {
            $status = 'kurang_protein';
            $targetNutrition = 'kurang_protein';
        } elseif (!$hasVeggie) {
            $status = 'kurang_sayur';
            $targetNutrition = 'kurang_sayur';
        }

        $module = null;
        if ($status !== 'seimbang' && $targetNutrition) {
            // Ambil rekomendasi modul edukasi dari database secara acak sesuai target
            $module = EducationModule::where('target_nutrition', $targetNutrition)->inRandomOrder()->first();
        }

        return [
            'status' => $status,
            'module' => $module
        ];
    }
}
