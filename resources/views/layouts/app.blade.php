<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#07140e">
        <title>@yield('title', 'دومينو')</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="felt min-h-dvh antialiased">
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <div class="absolute -top-24 start-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-[#e7c98a]/10 blur-3xl"></div>
            <div class="absolute bottom-0 -start-16 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto w-full min-w-0 max-w-6xl px-4 py-4 sm:px-6 lg:py-8">
            <header class="mb-5 flex min-w-0 flex-wrap items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="flex min-h-12 min-w-0 items-center gap-3">
                    <x-domino-mark />
                    <span>
                        <span class="block text-lg font-extrabold leading-none">دومينو</span>
                        <span class="mt-1 hidden text-xs text-white/60 sm:block">لوحة النقاط</span>
                    </span>
                </a>

                <div class="flex items-center gap-2">
                    @auth
                        <span class="hidden text-sm font-bold text-white/70 sm:inline">{{ auth()->user()->username }}</span>
                        @if (request()->routeIs('history'))
                            <a href="{{ route('home') }}" class="glass inline-flex min-h-12 items-center rounded-2xl px-4 text-sm font-bold">اللوحة</a>
                        @else
                            <a href="{{ route('history') }}" class="glass inline-flex min-h-12 items-center rounded-2xl px-4 text-sm font-bold">السجل</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="glass inline-flex min-h-12 items-center rounded-2xl px-4 text-sm font-bold">خروج</button>
                        </form>
                    @else
                        @unless (request()->routeIs('login'))
                            <a href="{{ route('login') }}" class="glass inline-flex min-h-12 items-center rounded-2xl px-4 text-sm font-bold">دخول</a>
                        @endunless
                        @unless (request()->routeIs('register'))
                            <a href="{{ route('register') }}" class="glass inline-flex min-h-12 items-center rounded-2xl px-4 text-sm font-bold">حساب جديد</a>
                        @endunless
                    @endauth
                </div>
            </header>

            @if (session('status'))
                <p class="glass mb-4 rounded-2xl px-4 py-3 text-sm font-bold text-[#e7c98a]" role="status">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-300/30 bg-red-950/50 px-4 py-3 text-sm text-red-100" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    </body>
</html>
