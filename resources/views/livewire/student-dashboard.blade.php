<div class="p-4 space-y-6" x-data="{ showModal: false, modalTitle: '', modalContent: '', modalRecipe: '', showLevelUp: false, newLevel: 0, pointsGained: 0 }"
     @show-education-modal.window="showModal = true; modalTitle = $event.detail.title; modalContent = $event.detail.content; modalRecipe = $event.detail.recipe"
     @level-up-modal.window="showLevelUp = true; newLevel = $event.detail.level; pointsGained = $event.detail.points">
    
    <!-- User Profile & Gamification Card -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mt-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-4 -mt-4 w-24 h-24 bg-yellow-100 rounded-full opacity-50"></div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Halo, {{ $user->name ?? 'Siswa' }}! 👋</h1>
                <p class="text-sm font-semibold text-yellow-600 mt-1 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    Level {{ $user->level ?? 1 }}
                </p>
            </div>
            <div class="bg-indigo-100 p-3 rounded-full text-primary shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
        </div>
        
        <!-- XP Progress Bar -->
        @php
            $currentPoints = $user->points ?? 0;
            $pointsInCurrentLevel = $currentPoints % 100;
            $progressPercent = $pointsInCurrentLevel;
        @endphp
        <div class="relative z-10">
            <div class="flex justify-between text-xs text-gray-500 mb-1 font-semibold">
                <span>XP: {{ $currentPoints }}</span>
                <span>Butuh {{ 100 - $pointsInCurrentLevel }} XP lagi</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner">
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 h-3 rounded-full transition-all duration-1000 ease-out" style="width: {{ $progressPercent }}%"></div>
            </div>
        </div>
    </div>

    <!-- Upload Form Component -->
    <livewire:food-upload-form />

    <!-- Rekomendasi Khusus Section -->
    @if($this->personalizedRecommendation)
        <div class="space-y-4 mt-8">
            <h2 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                <span>💡</span> Rekomendasi Khusus Untukmu
            </h2>
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-2xl p-5 border border-yellow-200 shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-gray-800 text-md mb-1">{{ $this->personalizedRecommendation->title }}</h3>
                    <p class="text-sm text-gray-600 line-clamp-2 mb-4">{{ Str::limit($this->personalizedRecommendation->content, 100) }}</p>
                    
                    <button @click="let data = {{ \Illuminate\Support\Js::from([
                        'title' => $this->personalizedRecommendation->title,
                        'content' => $this->personalizedRecommendation->content,
                        'recipe' => $this->personalizedRecommendation->substitution_recipe
                    ]) }}; modalTitle = data.title; modalContent = data.content; modalRecipe = data.recipe; showModal = true;" class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-2 px-4 rounded-xl transition shadow">
                        Baca Selengkapnya & Lihat Resep
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- History Section -->
    <div class="space-y-4 mt-8">
        <h2 class="text-lg font-bold text-gray-700">Histori Makananmu</h2>
        @if($foodLogs->isEmpty())
            <div class="bg-gray-100 rounded-2xl p-6 text-center text-gray-500 text-sm">
                Belum ada foto yang diunggah. Mulai ambil foto piringmu!
            </div>
        @else
            <div class="grid grid-cols-2 gap-4">
                @foreach($foodLogs as $log)
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition">
                        <img src="{{ Storage::url($log->photo_path) }}" alt="Food" class="w-full h-32 object-cover">
                        <div class="p-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $log->nutrition_status === 'seimbang' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ str_replace('_', ' ', Str::title($log->nutrition_status)) ?? 'Menunggu...' }}
                            </span>
                            
                            @if($log->detection_results && isset($log->detection_results['detections']))
                                <div class="mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded-lg border border-gray-100">
                                    <span class="font-bold text-indigo-600 mb-1 inline-block">Fakta Gizi Terdeteksi:</span>
                                    @php
                                        $detectedClasses = collect($log->detection_results['detections'])->pluck('class')->unique()->toArray();
                                        $nutritionFacts = app(\App\Services\NutritionEvaluatorService::class)->getNutritionFacts($detectedClasses);
                                    @endphp
                                    
                                    @if(empty($nutritionFacts))
                                        <div>Tidak ada makanan terdeteksi</div>
                                    @else
                                        <ul class="space-y-1.5 mt-1">
                                            @foreach($nutritionFacts as $fact)
                                                <li class="flex items-start gap-1.5">
                                                    <span class="text-sm leading-none mt-0.5">{{ $fact['icon'] }}</span>
                                                    <span class="leading-snug"><strong>{{ $fact['name'] }}:</strong> {{ $fact['desc'] }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    
                                    @if(isset($log->detection_results['note']) && $log->detection_results['note'] !== 'Analyzed via Roboflow AI')
                                        <div class="mt-2 text-red-500 italic text-[10px]">({{ $log->detection_results['note'] }})</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- AlpineJS Modal Pop-up (Edukasi) -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl transform transition-all max-h-[90vh] overflow-y-auto" @click.away="showModal = false">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-800 mb-2" x-text="modalTitle"></h3>
            <div class="text-gray-600 text-sm mb-4 space-y-2 whitespace-pre-line" x-text="modalContent"></div>
            
            <template x-if="modalRecipe">
                <div class="mt-4 p-4 bg-yellow-50 rounded-2xl border border-yellow-100">
                    <h4 class="font-bold text-yellow-800 text-sm mb-2 flex items-center gap-2">
                        <span>💡</span> Rekomendasi Resep Substitusi Lokal
                    </h4>
                    <div class="text-gray-700 text-xs whitespace-pre-line" x-text="modalRecipe"></div>
                </div>
            </template>

            <button @click="showModal = false" class="w-full mt-6 bg-primary hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow transition">
                Mengerti, Terima Kasih!
            </button>
        </div>
    </div>

    <!-- AlpineJS Modal Pop-up (Level Up) -->
    <div x-show="showLevelUp" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-80 backdrop-blur-sm" style="display: none;" x-transition.opacity>
        <div class="bg-gradient-to-b from-yellow-300 to-orange-400 rounded-3xl p-1 w-full max-w-sm shadow-2xl transform transition-all scale-105">
            <div class="bg-white rounded-[22px] p-6 text-center">
                <div class="text-6xl mb-2 animate-bounce">🎉</div>
                <h3 class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-500 to-orange-600 mb-1">LEVEL UP!</h3>
                <p class="text-gray-500 text-sm font-semibold mb-4">Luar biasa! Kamu telah mencapai:</p>
                <div class="inline-block bg-yellow-100 text-yellow-800 text-4xl font-black px-6 py-3 rounded-full mb-6 shadow-inner">
                    Level <span x-text="newLevel"></span>
                </div>
                <p class="text-sm text-gray-600 mb-6">Kamu mendapatkan <span class="font-bold text-green-600" x-text="'+' + pointsGained + ' XP'"></span> karena telah mengecek gizi makananmu.</p>
                <button @click="showLevelUp = false" class="w-full bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition active:scale-95">
                    Lanjutkan Berpetualang!
                </button>
            </div>
        </div>
    </div>
</div>
