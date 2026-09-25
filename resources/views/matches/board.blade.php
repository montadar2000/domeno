@extends('layouts.app')

@section('title', $match->team_one_name.' ضد '.$match->team_two_name)

@section('content')
    @php
        $playing = $round->status === \App\Enums\RoundStatus::Playing;
    @endphp

    <main class="min-w-0">
        <div class="mb-3 flex items-center justify-between gap-3">
            <p class="text-sm font-extrabold text-white/80">الجولة {{ $round->round_number }}</p>
            <details class="glass rounded-2xl">
                <summary class="min-h-11 cursor-pointer list-none px-4 py-2.5 text-sm font-bold">خيارات</summary>
                <div class="space-y-4 border-t border-white/10 p-4">
                    <form method="POST" action="{{ route('matches.update', $match) }}" class="space-y-2">
                        @csrf
                        @method('PATCH')
                        <p class="text-xs font-bold text-white/60">أسماء الفريقين</p>
                        <input type="text" name="team_one_name" value="{{ $match->team_one_name }}" maxlength="40" required aria-label="الفريق الأول" class="min-h-11 w-full rounded-xl border border-white/15 bg-black/20 px-3" autocomplete="off">
                        <input type="text" name="team_two_name" value="{{ $match->team_two_name }}" maxlength="40" required aria-label="الفريق الثاني" class="min-h-11 w-full rounded-xl border border-white/15 bg-black/20 px-3" autocomplete="off">
                        <button type="submit" class="min-h-11 w-full rounded-xl bg-white/15 text-sm font-bold">حفظ الأسماء</button>
                    </form>
                    <form method="POST" action="{{ route('matches.store') }}" class="space-y-2">
                        @csrf
                        <p class="text-xs font-bold text-white/60">جلسة جديدة</p>
                        <input type="text" name="team_one_name" value="الفريق الأول" maxlength="40" required aria-label="الفريق الأول" class="min-h-11 w-full rounded-xl border border-white/15 bg-black/20 px-3" autocomplete="off">
                        <input type="text" name="team_two_name" value="الفريق الثاني" maxlength="40" required aria-label="الفريق الثاني" class="min-h-11 w-full rounded-xl border border-white/15 bg-black/20 px-3" autocomplete="off">
                        <button type="submit" class="min-h-11 w-full rounded-xl bg-[#e7c98a] text-sm font-extrabold text-[#1a1408]">ابدأ</button>
                    </form>
                    <form method="POST" action="{{ route('matches.close', $match) }}" onsubmit="return confirm('تنهي الجلسة؟ النقاط تبقى بالسجل.')">
                        @csrf
                        <button type="submit" class="min-h-11 w-full rounded-xl border border-white/20 text-sm font-bold">إنهاء الجلسة</button>
                    </form>
                </div>
            </details>
        </div>

        <div class="grid min-w-0 items-start gap-5 md:grid-cols-[minmax(0,1.15fr)_minmax(18rem,0.85fr)]">
            <section class="min-w-0 space-y-4">
                <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2">
                    <x-score-card
                        :name="$match->team_one_name"
                        :wins="$match->team_one_wins"
                        :score="$round->team_one_score"
                        :win-score="$winScore"
                        accent="gold"
                        :winner="$round->winner === \App\Enums\TeamSide::TeamOne"
                        :mils="$round->mils_team === \App\Enums\TeamSide::TeamOne"
                    />
                    <x-score-card
                        :name="$match->team_two_name"
                        :wins="$match->team_two_wins"
                        :score="$round->team_two_score"
                        :win-score="$winScore"
                        accent="emerald"
                        :winner="$round->winner === \App\Enums\TeamSide::TeamTwo"
                        :mils="$round->mils_team === \App\Enums\TeamSide::TeamTwo"
                    />
                </div>

                @unless ($playing)
                    <section class="winner-card glass glass-gold rounded-2xl p-4 text-center" aria-labelledby="winner-title">
                        <h2 id="winner-title" class="text-2xl font-extrabold">فاز {{ $match->nameFor($round->winner) }}</h2>
                        @if ($round->mils_team)
                            <p class="mt-1 text-lg font-extrabold text-red-200">ملص {{ $match->nameFor($round->mils_team) }}</p>
                        @else
                            <p class="mt-1 text-sm text-white/70">{{ $round->scoreFor($round->winner) }} من {{ $winScore }}</p>
                        @endif
                        <div class="mt-4 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2">
                            <form method="POST" action="{{ route('rounds.store', $match) }}">
                                @csrf
                                <button type="submit" class="min-h-12 w-full rounded-2xl bg-[#e7c98a] text-sm font-extrabold text-[#1a1408]">جولة جديدة</button>
                            </form>
                            <form method="POST" action="{{ route('scores.undo', $match) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="min-h-12 w-full rounded-2xl border border-white/20 text-sm font-bold">تراجع</button>
                            </form>
                        </div>
                    </section>
                @endunless

                @if ($playing)
                    <form id="score-form" method="POST" action="{{ route('scores.store', $match) }}" class="glass rounded-2xl p-3">
                        @csrf
                        <label for="points" class="mb-2 block text-center text-sm font-bold text-white/70">النقاط</label>
                        <div class="mb-3 flex items-stretch gap-2" dir="ltr">
                            <button type="button" id="points-minus" class="grid w-14 shrink-0 place-items-center rounded-2xl bg-white/10 text-3xl font-extrabold leading-none" aria-label="نقص نقطة">−</button>
                            <input id="points" name="points" type="text" inputmode="numeric" enterkeyhint="done" autocomplete="off" maxlength="3" value="{{ old('points') }}" required placeholder="0" class="min-h-14 min-w-0 flex-1 rounded-2xl border border-white/15 bg-black/25 text-center text-3xl font-extrabold tabular-nums outline-none placeholder:text-white/25 focus:border-[#e7c98a]">
                            <button type="button" id="points-plus" class="grid w-14 shrink-0 place-items-center rounded-2xl bg-white/10 text-3xl font-extrabold leading-none" aria-label="زد نقطة">+</button>
                        </div>
                        <div class="grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-2">
                            <button type="submit" name="team" value="team_one" class="min-h-12 min-w-0 rounded-2xl bg-[#f6f1e4] px-2 text-sm font-extrabold leading-tight break-words text-[#1a1408]">
                                {{ $match->team_one_name }}
                            </button>
                            <button type="submit" name="team" value="team_two" class="min-h-12 min-w-0 rounded-2xl bg-emerald-300 px-2 text-sm font-extrabold leading-tight break-words text-[#062116]">
                                {{ $match->team_two_name }}
                            </button>
                        </div>
                    </form>
                @endif
            </section>

            <aside class="glass min-w-0 rounded-2xl p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-sm font-extrabold">آخر التسجيلات</h2>
                    @if ($playing && $entries->isNotEmpty())
                        <form method="POST" action="{{ route('scores.undo', $match) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="min-h-12 rounded-2xl border border-white/20 px-4 text-sm font-bold">تراجع</button>
                        </form>
                    @endif
                </div>

                @if ($entries->isEmpty())
                    <p class="rounded-xl border border-dashed border-white/15 px-3 py-4 text-center text-sm text-white/60">ماكو تسجيل.</p>
                @else
                    <ol class="space-y-2">
                        @foreach ($entries as $entry)
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-black/20 px-3 py-2">
                                <span class="min-w-0 font-bold break-words">{{ $match->nameFor($entry->team) }}</span>
                                <span class="shrink-0 text-lg font-extrabold tabular-nums text-[#e7c98a]" dir="ltr">+{{ $entry->points }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </aside>
        </div>
    </main>
@endsection
