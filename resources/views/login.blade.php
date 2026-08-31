<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk & Bermain | Gizi-Logic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#f59e0b',
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f0fdf4;
            background-image: radial-gradient(#bbf7d0 2px, transparent 2px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 overflow-hidden relative">
    
    <!-- Floating Background Elements -->
    <div class="absolute top-10 left-10 text-6xl animate-float opacity-50" style="animation-delay: 0s;">🥦</div>
    <div class="absolute bottom-20 left-20 text-5xl animate-float opacity-50" style="animation-delay: 1s;">🥕</div>
    <div class="absolute top-20 right-20 text-6xl animate-float opacity-50" style="animation-delay: 2s;">🍎</div>
    <div class="absolute bottom-10 right-10 text-7xl animate-float opacity-50" style="animation-delay: 1.5s;">🍳</div>

    <div class="w-full max-w-md" x-data="{ tab: 'login' }">
        
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="inline-block bg-white p-4 rounded-full shadow-lg mb-4 animate-bounce-slow border-4 border-green-200">
                <span class="text-5xl">🍽️</span>
            </div>
            <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-teal-500 tracking-tight">Gizi-Logic</h1>
            <p class="text-gray-500 font-semibold mt-1">Petualangan Gizi Seimbangmu Dimulai!</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-2xl overflow-hidden border border-white/50 ring-4 ring-white/30">
            
            <!-- Tab Switcher -->
            <div class="flex p-2 bg-gray-100/50 m-2 rounded-[1.5rem]">
                <button @click="tab = 'login'" 
                        :class="tab === 'login' ? 'bg-white shadow-sm text-green-600' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-3 px-4 rounded-2xl font-bold text-sm transition-all duration-300">
                    Masuk
                </button>
                <button @click="tab = 'register'" 
                        :class="tab === 'register' ? 'bg-white shadow-sm text-secondary' : 'text-gray-500 hover:text-gray-700'"
                        class="flex-1 py-3 px-4 rounded-2xl font-bold text-sm transition-all duration-300">
                    Daftar Baru
                </button>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="mx-6 mt-4 p-4 bg-red-50 rounded-2xl border border-red-100">
                    <ul class="text-sm text-red-500 font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-center gap-2"><span>⚠️</span> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Login Form -->
            <form x-show="tab === 'login'" action="{{ route('login.submit') }}" method="POST" class="p-6 space-y-5" x-transition.opacity>
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Email Sekolahmu</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">📧</span>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl pl-12 pr-4 py-3.5 focus:ring-2 focus:ring-green-400 focus:bg-white transition-all font-semibold text-gray-700 placeholder-gray-400"
                               placeholder="contoh@siswa.com">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Password Rahasia</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">🔑</span>
                        <input type="password" name="password" required
                               class="w-full bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl pl-12 pr-4 py-3.5 focus:ring-2 focus:ring-green-400 focus:bg-white transition-all font-semibold text-gray-700 placeholder-gray-400"
                               placeholder="********">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-black text-lg py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2">
                    Mulai Main! 🚀
                </button>
            </form>

            <!-- Register Form -->
            <form x-show="tab === 'register'" style="display: none;" action="{{ route('register.submit') }}" method="POST" class="p-6 space-y-4" x-transition.opacity>
                @csrf
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 ml-1">Nama Panggilan</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-secondary focus:bg-white transition-all font-semibold text-gray-700 text-sm"
                               placeholder="Namamu">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 ml-1">NISN / Kelas</label>
                        <div class="flex gap-2">
                            <input type="text" name="nisn" value="{{ old('nisn') }}"
                                   class="w-2/3 bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl px-3 py-3 focus:ring-2 focus:ring-secondary focus:bg-white transition-all font-semibold text-gray-700 text-sm"
                                   placeholder="NISN">
                            <input type="text" name="kelas" value="{{ old('kelas') }}"
                                   class="w-1/3 bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl px-3 py-3 focus:ring-2 focus:ring-secondary focus:bg-white transition-all font-semibold text-gray-700 text-sm text-center"
                                   placeholder="Kls">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1 ml-1">Email Sekolahmu</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-secondary focus:bg-white transition-all font-semibold text-gray-700 text-sm"
                           placeholder="contoh@siswa.com">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 ml-1">Password</label>
                        <input type="password" name="password" required minlength="8"
                               class="w-full bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-secondary focus:bg-white transition-all font-semibold text-gray-700 text-sm"
                               placeholder="********">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 ml-1">Ulangi</label>
                        <input type="password" name="password_confirmation" required minlength="8"
                               class="w-full bg-gray-50 border-0 ring-1 ring-gray-200 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-secondary focus:bg-white transition-all font-semibold text-gray-700 text-sm"
                               placeholder="********">
                    </div>
                </div>

                <button type="submit" class="w-full mt-2 bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-black text-lg py-4 px-6 rounded-2xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 active:translate-y-0 flex items-center justify-center gap-2">
                    Buat Akun! 🎮
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-8 font-semibold">
            Didukung oleh MTsN 1 Pacitan & Google Gemini AI
        </p>
    </div>
</body>
</html>
