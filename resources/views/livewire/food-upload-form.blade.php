<div x-data="{ isUploading: false, progress: 0, uploadError: false }"
     x-on:livewire-upload-start="isUploading = true; uploadError = false; progress = 0"
     x-on:livewire-upload-finish="isUploading = false; progress = 100"
     x-on:livewire-upload-error="isUploading = false; uploadError = true; alert('Gagal mengunggah foto! Pastikan ukuran tidak lebih dari 10MB atau cek koneksi internet Anda.')"
     x-on:livewire-upload-progress="progress = $event.detail.progress"
     class="bg-gradient-to-br from-primary to-indigo-800 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
    <!-- Decorative element -->
    <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 rounded-full bg-white opacity-10"></div>
    
    <h2 class="text-xl font-bold mb-2">Cek Gizi Piringmu!</h2>
    <p class="text-indigo-100 text-sm mb-6">Unggah foto piring makanmu dan AI akan mendeteksi kandungan gizinya.</p>
    
    <form wire:submit.prevent="save" class="space-y-4 relative z-10">
        @if ($photo)
            <div class="relative rounded-2xl overflow-hidden shadow-inner">
                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-48 object-cover">
                <button type="button" wire:click="$set('photo', null)" class="absolute top-2 right-2 bg-gray-900 bg-opacity-60 text-white rounded-full p-2 hover:bg-opacity-80 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <button type="submit" class="w-full bg-secondary hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg transform transition active:scale-95 flex items-center justify-center gap-2">
                <span wire:loading wire:target="save" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                <span wire:loading.remove wire:target="save">Mulai Analisis Gizi</span>
                <span wire:loading wire:target="save">Proses AI (Tunggu 2-3 detik)...</span>
            </button>
        @else
            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-indigo-300 border-dashed rounded-2xl cursor-pointer hover:bg-indigo-700 hover:bg-opacity-50 transition">
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <svg class="w-8 h-8 mb-3 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <p class="mb-2 text-sm text-indigo-100"><span class="font-bold">Klik untuk unggah</span> atau ambil foto</p>
                </div>
                <input type="file" wire:model="photo" class="hidden" accept="image/*" capture="environment" />
            </label>
        @endif
        
        @error('photo') <span class="text-red-300 text-xs mt-1 block">{{ $message }}</span> @enderror
        
        <!-- Progress Bar (Upload ke Server) -->
        <div x-show="isUploading" class="mt-4">
            <div class="flex justify-between text-xs text-indigo-200 mb-1">
                <span>Mengunggah foto ke server...</span>
                <span x-text="progress + '%'"></span>
            </div>
            <div class="w-full bg-indigo-900 rounded-full h-2.5 shadow-inner">
                <div class="bg-emerald-400 h-2.5 rounded-full transition-all duration-300" x-bind:style="'width: ' + progress + '%'"></div>
            </div>
        </div>

        <div x-show="uploadError" class="mt-2 text-xs text-red-300 bg-red-900 bg-opacity-50 p-2 rounded-lg border border-red-500">
            Terjadi kesalahan! Koneksi terputus atau batas server menolak foto berukuran besar.
        </div>
        
        <div wire:loading wire:target="photo" class="text-sm text-indigo-200 text-center w-full mt-2" x-show="!isUploading">
            Memproses pratinjau foto...
        </div>
    </form>
</div>
