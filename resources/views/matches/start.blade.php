@extends('layouts.app')

@section('title', 'جلسة جديدة — دومينو')

@section('content')
    <main class="mx-auto flex min-h-[70vh] max-w-lg items-center">
        <section class="glass w-full rounded-[2rem] p-6 sm:p-8">
            <p class="text-sm font-bold text-[#e7c98a]">فريقان على الطاولة</p>
            <h1 class="mt-2 text-3xl font-extrabold leading-tight sm:text-4xl">ابدأ جلسة الدومينو</h1>
            <p class="mt-3 text-sm leading-7 text-white/70">
                سجّل نقاط كل يد لفريق واحد. أول فريق يوصل {{ \App\Services\ScoringService::WIN_SCORE }} يفوز بالجولة. وإذا وصل 100 والفريق الثاني لسه صفر، الثاني يصير ملص والجولة تخلص.
            </p>

            <form method="POST" action="{{ route('matches.store') }}" class="mt-8 space-y-4">
                @csrf
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">الفريق الأول</span>
                    <input
                        type="text"
                        name="team_one_name"
                        value="{{ old('team_one_name', 'الفريق الأول') }}"
                        maxlength="40"
                        required
                        autocomplete="off"
                        class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none focus:border-[#e7c98a]"
                    >
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-bold text-white/70">الفريق الثاني</span>
                    <input
                        type="text"
                        name="team_two_name"
                        value="{{ old('team_two_name', 'الفريق الثاني') }}"
                        maxlength="40"
                        required
                        autocomplete="off"
                        class="glass min-h-14 w-full rounded-2xl px-4 text-base font-bold outline-none focus:border-[#e7c98a]"
                    >
                </label>
                <button type="submit" class="min-h-14 w-full rounded-2xl bg-[#e7c98a] text-lg font-extrabold text-[#1a1408]">
                    ابدأ الجلسة
                </button>
            </form>
        </section>
    </main>
@endsection
