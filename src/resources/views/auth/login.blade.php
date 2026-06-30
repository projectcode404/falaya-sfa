<!doctype html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Login — Falaya SFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="flex min-h-full flex-col items-center justify-center px-4 py-12">

    {{-- Brand --}}
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-amber-400">
            F
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Falaya SFA</h1>
        <p class="mt-1 text-sm text-slate-500">Sistem Distribusi & Sales Force Automation</p>
    </div>

    {{-- Card --}}
    <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="mb-6 text-center text-base font-semibold text-slate-900">Masuk ke Akun Anda</h2>

        <form method="POST" action="/login" autocomplete="off">
            @csrf

            <div class="mb-4">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Email</label>
                <input type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="email@falaya.com"
                       autofocus
                       class="w-full rounded-lg border px-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2
                              {{ $errors->has('email') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('email')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">Password</label>
                <input type="password" name="password"
                       placeholder="Password Anda"
                       class="w-full rounded-lg border px-3 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2
                              {{ $errors->has('password') ? 'border-red-300 focus:border-red-400 focus:ring-red-100' : 'border-slate-200 focus:border-amber-400 focus:ring-amber-100' }}">
                @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember"
                       class="h-4 w-4 rounded border-slate-300 accent-amber-500">
                <label for="remember" class="text-xs text-slate-600 cursor-pointer">Ingat saya</label>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800">
                Masuk
            </button>
        </form>
    </div>

</div>

</body>
</html>
