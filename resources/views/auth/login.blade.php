@extends('layouts.app')

@section('title', 'دخول — دومينو')

@section('content')
    <main class="mx-auto flex min-h-[70vh] max-w-lg items-center">
        <section class="glass w-full rounded-[2rem] p-6 sm:p-8">
            <p class="text-sm font-bold text-[#e7c98a]">حسابك</p>
            <h1 class="mt-2 text-3xl font-extrabold">ادخل للوحة</h1>
            <p class="mt-3 text-sm leading-7 text-white/70">كل شخص يشوف جلساته بس. اكتب اسم المستخدم وكلمة السر، بدون تأكيد إيميل.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">اسم المستخدم</span>
                    <input type="text" name="username" value="{{ old('username') }}" required autocomplete="username" class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">كلمة السر</span>
                    <input type="password" name="password" required autocomplete="current-password" class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none">
                </label>
                <button type="submit" class="min-h-14 w-full rounded-2xl bg-[#e7c98a] text-lg font-extrabold text-[#1a1408]">دخول</button>
            </form>
        </section>
    </main>
@endsection
