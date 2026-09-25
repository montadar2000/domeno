@props([
    'name',
    'wins',
    'score',
    'winScore',
    'accent' => 'gold',
    'winner' => false,
    'mils' => false,
])

@php
    $percent = min(100, (int) round(($score / max(1, $winScore)) * 100));
    $remaining = max(0, $winScore - $score);
@endphp

<article @class([
    'glass relative min-w-0 overflow-hidden rounded-2xl p-3',
    'glass-gold' => $winner || $accent === 'gold',
])>
    <div @class([
        'absolute inset-x-0 top-0 h-1',
        'bg-[#e7c98a]' => $accent === 'gold',
        'bg-emerald-300' => $accent === 'emerald',
    ])></div>

    <h2 class="truncate text-sm font-extrabold">{{ $name }}</h2>
    <p class="mt-1 text-3xl font-extrabold tabular-nums leading-none sm:text-4xl">{{ $score }}</p>
    <p class="mt-2 text-xs font-bold break-words text-white/65">
        @if ($mils)
            ملص
        @elseif ($remaining === 0)
            وصل {{ $winScore }}
        @else
            باقي {{ $remaining }}
        @endif
        <span class="text-white/40">· {{ $wins }} فوز</span>
    </p>

    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-black/30">
        <div @class([
            'h-full rounded-full',
            'bg-[#e7c98a]' => $accent === 'gold',
            'bg-emerald-300' => $accent === 'emerald',
        ]) style="width: {{ $percent }}%"></div>
    </div>
</article>
