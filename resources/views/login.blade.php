<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gizi-Logic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-3xl shadow-lg w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-primary mb-6">Gizi-Logic 🥦</h1>
        
        <form action="{{ route('register.submit') }}" method="POST" class="mb-4">
            @csrf
            <h2 class="text-sm font-semibold text-gray-500 mb-2">Daftar Cepat (Testing)</h2>
            <input type="text" name="name" placeholder="Nama" value="Siswa Tester" class="w-full border rounded-lg p-2 mb-2" required>
            <input type="email" name="email" placeholder="Email" value="siswa@test.com" class="w-full border rounded-lg p-2 mb-2" required>
            <input type="password" name="password" placeholder="Password" value="password" class="w-full border rounded-lg p-2 mb-2" required>
            <input type="password" name="password_confirmation" placeholder="Konfirmasi" value="password" class="w-full border rounded-lg p-2 mb-2" required>
            <button type="submit" class="w-full bg-primary text-white p-2 rounded-lg font-bold">Daftar & Masuk</button>
        </form>
    </div>
</body>
</html>
