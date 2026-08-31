<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class FoodUploadForm extends Component
{
    use WithFileUploads;

    public $photo;
    public $isUploading = false;

    public function updatedPhoto()
    {
        $this->validate([
            'photo' => 'image|max:10240', // 10MB Max
        ]);
        
        $this->isUploading = true;
    }

    public function save(\App\Services\YoloVisionService $yoloService)
    {
        $this->validate([
            'photo' => 'image|max:10240',
        ]);

        $path = $this->photo->store('food_photos', 'public');

        // Deteksi API YOLO (Fase 4)
        $detectionResults = $yoloService->analyzeFoodImage($path);

        // Evaluasi Gizi (Fase 5 / Gemini AI)
        $evaluator = app(\App\Services\NutritionEvaluatorService::class);
        $evaluation = $evaluator->evaluate($detectionResults);

        if (isset($evaluation['nutrition_facts'])) {
            $detectionResults['nutrition_facts'] = $evaluation['nutrition_facts'];
        }

        Auth::user()->foodLogs()->create([
            'photo_path' => $path,
            'detection_results' => $detectionResults,
            'nutrition_status' => $evaluation['status'],
        ]);

        $this->reset('photo');
        $this->isUploading = false;
        
        // --- GAMIFIKASI LOGIC ---
        $pointsEarned = 10; // Base points for uploading
        if ($evaluation['status'] === 'seimbang') {
            $pointsEarned += 40; // Bonus for balanced diet
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $leveledUp = $user->addPoints($pointsEarned);

        // Beritahu parent (StudentDashboard) untuk update list
        $this->dispatch('food-uploaded');

        // Jika tidak seimbang, panggil popup edukasi
        if ($evaluation['status'] !== 'seimbang' && $evaluation['module']) {
            $this->dispatch('show-education-modal', 
                title: $evaluation['module']->title, 
                content: $evaluation['module']->content,
                recipe: $evaluation['module']->substitution_recipe
            );
        }

        // Panggil notifikasi gamifikasi (jika naik level atau dapat poin)
        if ($leveledUp) {
            $this->dispatch('level-up-modal', level: $user->level, points: $pointsEarned);
        }
    }

    public function render()
    {
        return view('livewire.food-upload-form');
    }
}
