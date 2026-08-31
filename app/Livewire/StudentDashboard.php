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

        $statuses = $logs->pluck('nutrition_status');
        
        $counts = [
            'kurang_protein' => 0,
            'kurang_sayur' => 0,
            'seimbang' => 0,
        ];

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
    }
}
