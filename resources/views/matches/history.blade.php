@extends('layouts.app')

@section('title', 'السجل — دومينو')

@section('content')
    <main class="min-w-0 space-y-4">
        <div>
            <h1 class="text-3xl font-extrabold">السجل</h1>
            <p class="mt-2 text-sm text-white/65">كل الجلسات والجولات، والفوز عند {{ $winScore }}.</p>
        </div>

        @if ($matches->isEmpty())
            <section class="glass rounded-[1.75rem] px-6 py-12 text-center">
                <p class="text-lg font-bold">لحد الآن ماكو جلسات.</p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-12 items-center rounded-2xl bg-[#e7c98a] px-5 font-extrabold text-[#1a1408]">ابدأ الجلسة</a>
            </section>
        @else
            @foreach ($matches as $match)
                <article class="glass rounded-[1.75rem] p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-extrabold break-words">{{ $match->team_one_name }} ضد {{ $match->team_two_name }}</h2>
                            <p class="mt-1 text-sm text-white/55">{{ $match->created_at?->format('Y/m/d H:i') }}</p>
                        </div>
                        <span @class([
                            'rounded-full px-3 py-1 text-xs font-bold',
                            'bg-emerald-300/15 text-emerald-100' => $match->status === \App\Enums\MatchStatus::Active,
                            'bg-white/10 text-white/70' => $match->status === \App\Enums\MatchStatus::Closed,
                        ])>
                            {{ $match->status === \App\Enums\MatchStatus::Active ? 'جارية' : 'منتهية' }}
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-3">
                        <div class="min-w-0 rounded-2xl bg-black/20 p-4">
                            <p class="text-sm break-words text-white/60">{{ $match->team_one_name }}</p>
                            <p class="mt-1 text-3xl font-extrabold tabular-nums">{{ $match->team_one_wins }}</p>
                        </div>
                        <div class="min-w-0 rounded-2xl bg-black/20 p-4">
                            <p class="text-sm break-words text-white/60">{{ $match->team_two_name }}</p>
                            <p class="mt-1 text-3xl font-extrabold tabular-nums">{{ $match->team_two_wins }}</p>
                        </div>
                    </div>

                    <ol class="mt-5 space-y-2">
                        @foreach ($match->rounds as $round)
                            <li class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-white/10 px-4 py-3 text-sm">
                                <span class="font-bold">الجولة {{ $round->round_number }}</span>
                                <span class="tabular-nums text-white/80">{{ $round->team_one_score }} — {{ $round->team_two_score }}</span>
                                <span class="font-bold text-[#e7c98a]">
                                    @if ($round->mils_team)
                                        ملص {{ $match->nameFor($round->mils_team) }} —
                                    @endif
                                    @if ($round->winner)
                                        فاز {{ $match->nameFor($round->winner) }}
                                    @else
                                        جارية
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </article>
            @endforeach
        @endif
    </main>
@endsection
