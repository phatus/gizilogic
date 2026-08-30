<div class="p-4 space-y-6" x-data="{ showModal: false, modalTitle: '', modalContent: '' }"
     @show-education-modal.window="showModal = true; modalTitle = $event.detail.title; modalContent = $event.detail.content">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center justify-between mt-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Halo, {{ $user->name ?? 'Siswa' }}! 👋</h1>
            <p class="text-sm text-gray-500 mt-1">Siap untuk memindai piring makanmu hari ini?</p>
        </div>
        <div class="bg-indigo-100 p-3 rounded-full text-primary">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
        </div>
    </div>

    <!-- Upload Form Component -->
    <livewire:food-upload-form />

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
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- AlpineJS Modal Pop-up -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-50 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl transform transition-all" @click.away="showModal = false">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-800 mb-2" x-text="modalTitle"></h3>
            <p class="text-gray-600 text-center text-sm mb-6" x-text="modalContent"></p>
            <button @click="showModal = false" class="w-full bg-primary hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow transition">
                Mengerti, Terima Kasih!
            </button>
        </div>
    </div>
</div>
