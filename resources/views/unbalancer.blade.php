@extends('layouts.app')

@section('title', 'The Unbalancer - Lucky Draw')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f1f26] pb-4">
        <div>
            <div class="font-mono text-xs text-zinc-500 uppercase tracking-wider">Mode 04</div>
            <h1 class="text-xl font-semibold text-white tracking-tight">The Unbalancer (1 - 10,000 Draw)</h1>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="clearUnbalancerHistory()" class="px-2.5 py-1.5 rounded-md text-xs ui-card-subtle text-zinc-300 hover:text-white border border-[#23232b] transition-colors cursor-pointer">
                Clear History
            </button>
            <button onclick="exportUnbalancerCSV()" class="px-2.5 py-1.5 rounded-md text-xs text-zinc-400 hover:text-zinc-200 border border-[#23232b] transition-colors cursor-pointer">
                Export CSV
            </button>
        </div>
    </div>

    <!-- Configuration Bar -->
    <div class="ui-card rounded-xl p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-mono text-zinc-400 mb-1">Max Ceiling Amount</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-500 font-mono text-xs">$</span>
                    <input type="number" id="inputUnbalancerMax" value="10000" min="10" step="100" class="w-full pl-7 pr-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-white font-mono text-xs focus:outline-none focus:border-zinc-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-mono text-zinc-400 mb-1">Probability Curve</label>
                <select id="selectProbModel" onchange="updateTierLegend()" class="w-full px-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-white text-xs focus:outline-none focus:border-zinc-500">
                    <option value="tiered">Casino Tiers (70% Low, 20% Med, 8% Epic, 2% Jackpot)</option>
                    <option value="normal">Gaussian Bell Curve (Mean ~5,000)</option>
                    <option value="uniform">Flat Uniform (Equal 1 in 10,000)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-mono text-zinc-400 mb-1">Drawer Name (Optional)</label>
                <input type="text" id="unbalancerDrawerName" placeholder="e.g. Player #1" class="w-full px-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-white text-xs focus:outline-none focus:border-zinc-500">
            </div>
        </div>

        <!-- Clean Probability Tiers Breakdown -->
        <div id="tierLegend" class="mt-4 pt-3 border-t border-[#1f1f26] grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs font-mono"></div>
    </div>

    <!-- Workspace: Stage & Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Display Stage (7 Cols) -->
        <div class="lg:col-span-7 ui-card rounded-xl p-8 flex flex-col items-center">
            <!-- Tier Badge -->
            <div id="unbalancerRarityBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800 text-[11px] font-mono text-zinc-400 uppercase tracking-wider mb-6">
                Ready to Draw
            </div>

            <!-- Digital Readout Screen -->
            <div class="w-full max-w-md py-8 px-6 rounded-xl bg-[#0c0c10] border border-[#202027] text-center my-2">
                <span class="text-[11px] font-mono text-zinc-500 uppercase tracking-widest block mb-2">Generated Reward</span>
                <div id="odometerDisplay" class="text-5xl sm:text-6xl font-semibold text-white font-mono tracking-tight py-1">
                    $00,000
                </div>
                <span id="odometerDrawerLabel" class="text-xs text-zinc-400 font-mono mt-2 block">
                    Click "Draw Reward"
                </span>
            </div>

            <!-- Controls -->
            <div class="w-full max-w-sm mt-6 flex flex-col items-center gap-2">
                <button id="unbalancerDrawBtn" onclick="drawUnbalancer()" class="w-full py-2.5 px-4 rounded-lg bg-zinc-100 hover:bg-white text-zinc-900 font-medium text-sm transition cursor-pointer">
                    Draw Reward
                </button>
                <span class="text-[11px] font-mono text-zinc-500">Or press <kbd class="px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-400">Space</kbd></span>
            </div>
        </div>

        <!-- Sidebar: Stats & History (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Stats -->
            <div class="ui-card rounded-xl p-5">
                <span class="font-medium text-white text-xs block mb-3">Summary Metrics</span>
                <div class="grid grid-cols-2 gap-3 text-xs font-mono">
                    <div class="p-2.5 rounded-lg bg-[#0e0e12] border border-[#202027]">
                        <span class="text-zinc-500 text-[11px]">Total Draws</span>
                        <div id="statTotalDraws" class="text-lg font-semibold text-white mt-0.5">0</div>
                    </div>
                    <div class="p-2.5 rounded-lg bg-[#0e0e12] border border-[#202027]">
                        <span class="text-zinc-500 text-[11px]">Average Won</span>
                        <div id="statAvgWin" class="text-lg font-semibold text-zinc-300 mt-0.5">$0</div>
                    </div>
                    <div class="p-2.5 rounded-lg bg-[#0e0e12] border border-[#202027]">
                        <span class="text-zinc-500 text-[11px]">Highest Pick</span>
                        <div id="statMaxWin" class="text-lg font-semibold text-emerald-400 mt-0.5">$0</div>
                    </div>
                    <div class="p-2.5 rounded-lg bg-[#0e0e12] border border-[#202027]">
                        <span class="text-zinc-500 text-[11px]">Total Distributed</span>
                        <div id="statTotalPayout" class="text-lg font-semibold text-zinc-300 mt-0.5">$0</div>
                    </div>
                </div>
            </div>

            <!-- History -->
            <div class="ui-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <span class="font-medium text-white">Recent Draws</span>
                    <button onclick="exportUnbalancerCSV()" class="text-zinc-500 hover:text-zinc-300 font-mono text-[11px] cursor-pointer">
                        Export CSV
                    </button>
                </div>
                <div id="unbalancerHistoryList" class="space-y-1.5 max-h-52 overflow-y-auto pr-1 text-xs font-mono">
                    <div class="text-[11px] text-zinc-600 py-3 text-center">No draws logged yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let unbalancerHistory = [];
    let isDrawing = false;
    let drawCounter = 0;

    function updateTierLegend() {
        const model = document.getElementById('selectProbModel').value;
        const legend = document.getElementById('tierLegend');
        const max = parseInt(document.getElementById('inputUnbalancerMax').value) || 10000;

        if (model === 'tiered') {
            const p10 = Math.round(max * 0.10);
            const p35 = Math.round(max * 0.35);
            const p75 = Math.round(max * 0.75);

            legend.innerHTML = `
                <div class="p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-zinc-400 flex items-center justify-between text-[11px]">
                    <span>Common (70%)</span>
                    <span class="text-zinc-200">1 - ${p10.toLocaleString()}</span>
                </div>
                <div class="p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-zinc-400 flex items-center justify-between text-[11px]">
                    <span>Uncommon (20%)</span>
                    <span class="text-zinc-200">${(p10 + 1).toLocaleString()} - ${p35.toLocaleString()}</span>
                </div>
                <div class="p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-zinc-400 flex items-center justify-between text-[11px]">
                    <span>Epic (8%)</span>
                    <span class="text-zinc-200">${(p35 + 1).toLocaleString()} - ${p75.toLocaleString()}</span>
                </div>
                <div class="p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-zinc-400 flex items-center justify-between text-[11px]">
                    <span>Jackpot (2%)</span>
                    <span class="text-emerald-400">${(p75 + 1).toLocaleString()} - ${max.toLocaleString()}</span>
                </div>
            `;
        } else if (model === 'normal') {
            const mid = Math.round(max / 2);
            legend.innerHTML = `
                <div class="col-span-2 sm:col-span-4 p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-zinc-400 flex items-center justify-between text-[11px]">
                    <span>Gaussian: Concentrated around mean (~$${mid.toLocaleString()})</span>
                    <span class="text-zinc-200">Rare extremes (&lt;15% or &gt;85%)</span>
                </div>
            `;
        } else {
            legend.innerHTML = `
                <div class="col-span-2 sm:col-span-4 p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-zinc-400 flex items-center justify-between text-[11px]">
                    <span>Flat Uniform: Equal 1 in ${max.toLocaleString()} chance for any number</span>
                    <span class="text-zinc-200">1 to ${max.toLocaleString()}</span>
                </div>
            `;
        }
    }

    async function drawUnbalancer() {
        if (isDrawing) return;

        const max = parseInt(document.getElementById('inputUnbalancerMax').value) || 10000;
        const model = document.getElementById('selectProbModel').value;
        const nameInput = document.getElementById('unbalancerDrawerName');
        const customName = nameInput.value.trim() || `Player #${drawCounter + 1}`;

        isDrawing = true;
        const btn = document.getElementById('unbalancerDrawBtn');
        const display = document.getElementById('odometerDisplay');
        const label = document.getElementById('odometerDrawerLabel');
        const badge = document.getElementById('unbalancerRarityBadge');

        btn.disabled = true;
        btn.classList.add('opacity-50');

        try {
            const response = await fetch("{{ route('api.unbalancer.draw') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    max: max,
                    drawer_name: customName,
                    model: model,
                    draw_count: drawCounter
                })
            });

            const data = await response.json();

            if (data.success) {
                let duration = 900;
                let startTime = performance.now();

                function rollCounter(now) {
                    let elapsed = now - startTime;
                    if (elapsed < duration) {
                        let interim = Math.floor(Math.random() * max) + 1;
                        display.innerText = `$${interim.toLocaleString()}`;
                        display.classList.add('text-zinc-500');
                        SoundEffects.tick();
                        requestAnimationFrame(rollCounter);
                    } else {
                        drawCounter++;
                        const finalAmount = data.amount;
                        display.innerText = `$${finalAmount.toLocaleString()}`;
                        display.classList.remove('text-zinc-500');

                        if (data.tier.includes('JACKPOT')) {
                            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-950/40 border border-emerald-500/50 text-[11px] font-mono text-emerald-300 uppercase tracking-wider';
                            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> ${data.tier}`;
                            SoundEffects.jackpot();
                            triggerConfetti(70);
                        } else if (data.tier.includes('Epic')) {
                            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-950/40 border border-purple-500/50 text-[11px] font-mono text-purple-300 uppercase tracking-wider';
                            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span> ${data.tier}`;
                            SoundEffects.winFanfare();
                            triggerConfetti(35);
                        } else if (data.tier.includes('Uncommon')) {
                            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-950/40 border border-blue-500/50 text-[11px] font-mono text-blue-300 uppercase tracking-wider';
                            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> ${data.tier}`;
                            SoundEffects.ding();
                        } else {
                            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-zinc-800 text-[11px] font-mono text-zinc-300 uppercase tracking-wider';
                            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span> ${data.tier}`;
                            SoundEffects.ding();
                        }

                        label.innerText = `${data.drawer_name} received $${finalAmount.toLocaleString()} (${data.percentage_of_max}% of max)`;

                        unbalancerHistory.unshift({
                            id: drawCounter,
                            drawer: data.drawer_name,
                            amount: data.amount,
                            tier: data.tier,
                            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                        });

                        nameInput.value = '';
                        updateUnbalancerUI();

                        isDrawing = false;
                        btn.disabled = false;
                        btn.classList.remove('opacity-50');
                    }
                }

                requestAnimationFrame(rollCounter);
            }
        } catch (e) {
            console.error(e);
            alert("Error running draw.");
            isDrawing = false;
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        }
    }

    function updateUnbalancerUI() {
        document.getElementById('statTotalDraws').innerText = unbalancerHistory.length;

        if (unbalancerHistory.length === 0) {
            document.getElementById('statAvgWin').innerText = "$0";
            document.getElementById('statMaxWin').innerText = "$0";
            document.getElementById('statTotalPayout').innerText = "$0";
            document.getElementById('unbalancerHistoryList').innerHTML = `<div class="text-[11px] text-zinc-600 py-3 text-center">No draws logged yet.</div>`;
            return;
        }

        const total = unbalancerHistory.reduce((sum, item) => sum + item.amount, 0);
        const max = Math.max(...unbalancerHistory.map(item => item.amount));
        const avg = Math.round(total / unbalancerHistory.length);

        document.getElementById('statTotalPayout').innerText = `$${total.toLocaleString()}`;
        document.getElementById('statMaxWin').innerText = `$${max.toLocaleString()}`;
        document.getElementById('statAvgWin').innerText = `$${avg.toLocaleString()}`;

        const list = document.getElementById('unbalancerHistoryList');
        list.innerHTML = '';

        unbalancerHistory.forEach(item => {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-2 rounded-lg bg-[#0e0e12] border border-[#1f1f26] text-xs';
            div.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[11px] text-zinc-500">${item.id}.</span>
                    <span class="font-medium text-zinc-200">${item.drawer}</span>
                    <span class="font-mono text-[10px] text-zinc-400 bg-zinc-800 px-1 rounded">${item.tier}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-zinc-200">+$${item.amount.toLocaleString()}</span>
                    <span class="text-zinc-600 text-[10px] font-mono">${item.time}</span>
                </div>
            `;
            list.appendChild(div);
        });
    }

    function clearUnbalancerHistory() {
        unbalancerHistory = [];
        drawCounter = 0;
        updateUnbalancerUI();
    }

    function exportUnbalancerCSV() {
        if (unbalancerHistory.length === 0) {
            alert("No history to export.");
            return;
        }

        let csv = "Draw #,Drawer,Amount,Tier,Timestamp\n";
        unbalancerHistory.forEach(item => {
            csv += `"${item.id}","${item.drawer}","${item.amount}","${item.tier}","${item.time}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `unbalancer_${Date.now()}.csv`;
        a.click();
    }

    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT') {
            e.preventDefault();
            drawUnbalancer();
        }
    });

    updateTierLegend();
    updateUnbalancerUI();
</script>
@endpush
