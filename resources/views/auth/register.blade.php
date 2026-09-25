@extends('layouts.app')

@section('title', 'حساب جديد — دومينو')

@section('content')
    <main class="mx-auto flex min-h-[70vh] max-w-lg items-center">
        <section class="glass w-full rounded-[2rem] p-6 sm:p-8">
            <p class="text-sm font-bold text-[#e7c98a]">حساب جديد</p>
            <h1 class="mt-2 text-3xl font-extrabold">سجّل وابدأ</h1>
            <p class="mt-3 text-sm leading-7 text-white/70">اسم مستخدم وكلمة سر، وتدخل مباشرة. ماكو تحقق بالإيميل.</p>

            <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">اسم المستخدم</span>
                    <input type="text" name="username" value="{{ old('username') }}" required minlength="3" maxlength="40" autocomplete="username" class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">كلمة السر</span>
                    <input type="password" name="password" required minlength="4" autocomplete="new-password" class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">تأكيد كلمة السر</span>
                    <input type="password" name="password_confirmation" required minlength="4" autocomplete="new-password" class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none">
                </label>
                <button type="submit" class="min-h-14 w-full rounded-2xl bg-[#e7c98a] text-lg font-extrabold text-[#1a1408]">إنشاء الحساب</button>
            </form>
        </section>
    </main>
@endsection
