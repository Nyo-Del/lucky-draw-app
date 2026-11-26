<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Lucky Draw')</title>

    <!-- Inter & JetBrains Mono Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        surface: {
                            DEFAULT: '#121215',
                            raised: '#18181c',
                            subtle: '#222227',
                            border: '#27272e',
                            'border-subtle': '#1f1f24'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Canvas Confetti CDN -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            color-scheme: dark;
        }

        body {
            background-color: #0b0b0e;
            color: #e4e4e7;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Clean elevated surface without heavy shadows */
        .ui-card {
            background-color: #131317;
            border: 1px solid #23232a;
        }

        .ui-card-subtle {
            background-color: #0f0f13;
            border: 1px solid #1c1c22;
        }

        .ui-interactive {
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }
        .ui-interactive:hover {
            border-color: #3b3b46;
            background-color: #17171d;
        }

        /* Minimal scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #272730;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #3a3a46;
        }
    </style>

    @stack('styles')
</head>
<body class="flex flex-col min-h-screen">
    <!-- Clean Minimalist Header -->
    <header class="sticky top-0 z-50 bg-[#0b0b0e]/90 backdrop-blur-md border-b border-[#1f1f26]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14">
                <!-- Brand -->
                <a href="{{ route('home') }}" class="flex items-center space-x-2.5">
                    <span class="w-7 h-7 rounded-lg bg-zinc-800 border border-zinc-700/80 flex items-center justify-center text-zinc-100 text-xs font-semibold">
                        LD
                    </span>
                    <span class="text-sm font-semibold tracking-tight text-white">
                        LuckyDraw
                    </span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1 text-xs">
                    <a href="{{ route('home') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('home') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}">
                        Overview
                    </a>
                    <a href="{{ route('spinwheel') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('spinwheel') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}">
                        Spin Wheel
                    </a>
                    <a href="{{ route('dice') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('dice') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}">
                        Dice
                    </a>
                    <a href="{{ route('balancer') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('balancer') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}">
                        Balancer
                    </a>
                    <a href="{{ route('unbalancer') }}" class="px-3 py-1.5 rounded-md font-medium transition-colors {{ request()->routeIs('unbalancer') ? 'bg-zinc-800 text-white' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900' }}">
                        Unbalancer
                    </a>
                </nav>

                <!-- Actions -->
                <div class="flex items-center space-x-2.5">
                    <button id="soundToggleBtn" onclick="SoundEffects.toggleMute()" class="flex items-center space-x-1.5 px-2.5 py-1 rounded-md border border-[#26262e] bg-[#141418] hover:bg-[#1a1a20] text-zinc-400 hover:text-zinc-200 text-xs transition-colors cursor-pointer">
                        <i id="soundIcon" class="fa-solid fa-volume-high text-[11px]"></i>
                        <span id="soundText" class="font-mono text-[11px]">SFX</span>
                    </button>

                    <button onclick="document.getElementById('mobileMenu').classList.toggle('hidden')" class="md:hidden p-1.5 rounded-md border border-[#26262e] bg-[#141418] text-zinc-400 hover:text-white">
                        <i class="fa-solid fa-bars text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden py-3 border-t border-[#1f1f26] space-y-1 text-xs">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('home') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">Overview</a>
                <a href="{{ route('spinwheel') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('spinwheel') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">Spin Wheel</a>
                <a href="{{ route('dice') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('dice') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">Dice</a>
                <a href="{{ route('balancer') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('balancer') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">Balancer</a>
                <a href="{{ route('unbalancer') }}" class="block px-3 py-2 rounded-md {{ request()->routeIs('unbalancer') ? 'bg-zinc-800 text-white' : 'text-zinc-400' }}">Unbalancer</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 py-8 px-4 sm:px-6 max-w-6xl mx-auto w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-[#1b1b22] py-5 text-xs text-zinc-500">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between">
            <span>Lucky Draw App</span>
            <span class="font-mono text-[11px] text-zinc-600">Laravel 12 &bull; Blade</span>
        </div>
    </footer>

    <!-- Clean Audio Synthesizer -->
    <script>
        const SoundEffects = (() => {
            let muted = false;
            let audioCtx = null;

            function initContext() {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            }

            return {
                isMuted: () => muted,
                toggleMute: () => {
                    muted = !muted;
                    const icon = document.getElementById('soundIcon');
                    const text = document.getElementById('soundText');
                    if (muted) {
                        icon.className = 'fa-solid fa-volume-xmark text-[11px] text-zinc-500';
                        text.innerText = 'Muted';
                    } else {
                        icon.className = 'fa-solid fa-volume-high text-[11px] text-zinc-300';
                        text.innerText = 'SFX';
                    }
                },
                tick: () => {
                    if (muted) return;
                    try {
                        initContext();
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(500, audioCtx.currentTime);
                        gain.gain.setValueAtTime(0.04, audioCtx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.03);
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.03);
                    } catch (e) {}
                },
                diceRoll: () => {
                    if (muted) return;
                    try {
                        initContext();
                        const now = audioCtx.currentTime;
                        for (let i = 0; i < 3; i++) {
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(200 + i * 30, now + i * 0.05);
                            gain.gain.setValueAtTime(0.04, now + i * 0.05);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.05 + 0.03);
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start(now + i * 0.05);
                            osc.stop(now + i * 0.05 + 0.03);
                        }
                    } catch (e) {}
                },
                ding: () => {
                    if (muted) return;
                    try {
                        initContext();
                        const now = audioCtx.currentTime;
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(660, now);
                        osc.frequency.exponentialRampToValueAtTime(880, now + 0.12);
                        gain.gain.setValueAtTime(0.08, now);
                        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start(now);
                        osc.stop(now + 0.35);
                    } catch (e) {}
                },
                winFanfare: () => {
                    if (muted) return;
                    try {
                        initContext();
                        const notes = [523.25, 659.25, 783.99];
                        const now = audioCtx.currentTime;
                        notes.forEach((freq, idx) => {
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(freq, now + idx * 0.09);
                            gain.gain.setValueAtTime(0.08, now + idx * 0.09);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.09 + 0.25);
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start(now + idx * 0.09);
                            osc.stop(now + idx * 0.09 + 0.25);
                        });
                    } catch (e) {}
                },
                jackpot: () => {
                    if (muted) return;
                    try {
                        initContext();
                        const notes = [523.25, 659.25, 783.99, 1046.50];
                        const now = audioCtx.currentTime;
                        notes.forEach((freq, idx) => {
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(freq, now + idx * 0.08);
                            gain.gain.setValueAtTime(0.09, now + idx * 0.08);
                            gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.08 + 0.3);
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start(now + idx * 0.08);
                            osc.stop(now + idx * 0.08 + 0.3);
                        });
                    } catch (e) {}
                }
            };
        })();

        function triggerConfetti(count = 50) {
            if (typeof confetti === 'function') {
                confetti({
                    particleCount: count,
                    spread: 60,
                    origin: { y: 0.65 },
                    colors: ['#ffffff', '#a1a1aa', '#38bdf8', '#34d399', '#fbbf24']
                });
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
