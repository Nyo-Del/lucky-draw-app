@extends('layouts.app')

@section('title', 'Lucky Draw - Overview')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="border-b border-[#1f1f26] pb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            Lucky Draw
        </h1>
        <p class="mt-1 text-sm text-zinc-400">
            Select a randomizer mode below to configure prizes, roll items, or simulate draw probabilities.
        </p>
    </div>

    <!-- 4 Clean Interactive Mode Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- 01: Spin Wheel -->
        <a href="{{ route('spinwheel') }}" class="ui-card ui-interactive rounded-xl p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="font-mono text-xs text-zinc-400">#01 &bull; Prize Wheel</span>
                    <i class="fa-solid fa-arrow-right text-xs text-zinc-600 group-hover:text-zinc-200 transition-colors"></i>
                </div>
                <h2 class="text-lg font-medium text-white group-hover:text-zinc-100">
                    Spin Wheel
                </h2>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Customizable wheel with deceleration physics, custom slices, weights, sound ticks, and prize presets.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#1f1f26] flex items-center justify-between text-xs font-mono text-zinc-500">
                <span>Interactive Canvas</span>
                <span class="text-zinc-400 group-hover:text-white transition-colors">Open &rarr;</span>
            </div>
        </a>

        <!-- 02: Dice Chooser -->
        <a href="{{ route('dice') }}" class="ui-card ui-interactive rounded-xl p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="font-mono text-xs text-zinc-400">#02 &bull; Random Chooser</span>
                    <i class="fa-solid fa-arrow-right text-xs text-zinc-600 group-hover:text-zinc-200 transition-colors"></i>
                </div>
                <h2 class="text-lg font-medium text-white group-hover:text-zinc-100">
                    Dice (1 Item Chooser)
                </h2>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Roll a 3D tumbling dice to fairly select exactly 1 item or participant from a custom list.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#1f1f26] flex items-center justify-between text-xs font-mono text-zinc-500">
                <span>CSS 3D Motion</span>
                <span class="text-zinc-400 group-hover:text-white transition-colors">Open &rarr;</span>
            </div>
        </a>

        <!-- 03: The Balancer -->
        <a href="{{ route('balancer') }}" class="ui-card ui-interactive rounded-xl p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="font-mono text-xs text-zinc-400">#03 &bull; Budget Capped</span>
                    <i class="fa-solid fa-arrow-right text-xs text-zinc-600 group-hover:text-zinc-200 transition-colors"></i>
                </div>
                <h2 class="text-lg font-medium text-white group-hover:text-zinc-100">
                    The Balancer (Total Pool Capped)
                </h2>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Declare a hard ceiling (e.g. 10,000 max). Drawers randomly receive shares based on probability without ever exceeding the declared cap.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#1f1f26] flex items-center justify-between text-xs font-mono text-zinc-500">
                <span>Depletion Ledger</span>
                <span class="text-zinc-400 group-hover:text-white transition-colors">Open &rarr;</span>
            </div>
        </a>

        <!-- 04: The Unbalancer -->
        <a href="{{ route('unbalancer') }}" class="ui-card ui-interactive rounded-xl p-6 flex flex-col justify-between group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="font-mono text-xs text-zinc-400">#04 &bull; Independent Tiers</span>
                    <i class="fa-solid fa-arrow-right text-xs text-zinc-600 group-hover:text-zinc-200 transition-colors"></i>
                </div>
                <h2 class="text-lg font-medium text-white group-hover:text-zinc-100">
                    The Unbalancer (1 - 10,000 Draw)
                </h2>
                <p class="mt-2 text-xs leading-relaxed text-zinc-400">
                    Each drawer independently draws a random reward from 1 to 10,000, governed by real probability curves (Common, Rare, Jackpot).
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#1f1f26] flex items-center justify-between text-xs font-mono text-zinc-500">
                <span>Probability Curves</span>
                <span class="text-zinc-400 group-hover:text-white transition-colors">Open &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Clean Info Bar -->
    <div class="ui-card-subtle rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-zinc-400">
        <div class="flex items-center space-x-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
            <span>Cryptographic random generator enabled (CSPRNG)</span>
        </div>
        <div class="flex items-center space-x-4 font-mono text-[11px] text-zinc-500">
            <span>No authentication required</span>
            <span>Exportable CSV logs</span>
        </div>
    </div>
</div>
@endsection
