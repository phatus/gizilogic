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
}
