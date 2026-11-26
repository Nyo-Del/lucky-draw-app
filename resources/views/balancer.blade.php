@extends('layouts.app')

@section('title', 'The Balancer - Lucky Draw')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f1f26] pb-4">
        <div>
            <div class="font-mono text-xs text-zinc-500 uppercase tracking-wider">Mode 03</div>
            <h1 class="text-xl font-semibold text-white tracking-tight">The Balancer (Total Pool Capped)</h1>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="resetPool()" class="px-2.5 py-1.5 rounded-md text-xs ui-card-subtle text-zinc-300 hover:text-white border border-[#23232b] transition-colors cursor-pointer">
                Reset Pool
            </button>
            <button onclick="exportBalancerCSV()" class="px-2.5 py-1.5 rounded-md text-xs text-zinc-400 hover:text-zinc-200 border border-[#23232b] transition-colors cursor-pointer">
                Export CSV
            </button>
        </div>
    </div>

    <!-- Pool Configuration Bar -->
    <div class="ui-card rounded-xl p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-mono text-zinc-400 mb-1">Total Max Pool</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-500 font-mono text-xs">$</span>
                    <input type="number" id="inputMaxPool" value="10000" min="10" step="10" class="w-full pl-7 pr-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-white font-mono text-xs focus:outline-none focus:border-zinc-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-mono text-zinc-400 mb-1">Expected Drawers</label>
                <input type="number" id="inputExpectedDrawers" value="10" min="2" max="500" class="w-full px-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-white font-mono text-xs focus:outline-none focus:border-zinc-500">
            </div>

            <div>
                <label class="block text-xs font-mono text-zinc-400 mb-1">Probability Model</label>
                <select id="selectAlgorithm" class="w-full px-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-white text-xs focus:outline-none focus:border-zinc-500">
                    <option value="red_envelope">Red Envelope (Fair Variance)</option>
                    <option value="weighted">Tiered Distribution (Jackpot & Slices)</option>
                    <option value="uniform">Uniform Random Slices</option>
                </select>
            </div>

            <div>
                <button onclick="applyPoolSettings()" class="w-full py-2 px-3 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white font-medium text-xs transition cursor-pointer">
                    Apply Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="ui-card rounded-xl p-4">
            <span class="text-xs font-mono text-zinc-500 block">Declared Pool</span>
            <div id="statMaxPool" class="text-2xl font-semibold text-white font-mono mt-1">$10,000.00</div>
            <span class="text-[11px] text-zinc-500 font-mono mt-1 block">Maximum budget cap</span>
        </div>

        <div class="ui-card rounded-xl p-4">
            <span class="text-xs font-mono text-zinc-500 block">Distributed</span>
            <div id="statClaimedPool" class="text-2xl font-semibold text-zinc-300 font-mono mt-1">$0.00</div>
            <span id="statClaimedPct" class="text-[11px] text-zinc-500 font-mono mt-1 block">0% distributed</span>
        </div>

        <div class="ui-card rounded-xl p-4 border-l-2 border-l-emerald-500">
            <span class="text-xs font-mono text-zinc-500 block">Remaining</span>
            <div id="statRemainingPool" class="text-2xl font-semibold text-emerald-400 font-mono mt-1">$10,000.00</div>
            <span id="statDrawsRemaining" class="text-[11px] text-zinc-500 font-mono mt-1 block">10 expected draws left</span>
        </div>
    </div>

    <!-- Clean Depletion Progress Bar -->
    <div class="ui-card rounded-xl p-4 space-y-2">
        <div class="flex items-center justify-between text-xs font-mono">
            <span class="text-zinc-400">Pool Allocation</span>
            <span id="progressBarLabel" class="text-zinc-400">$10,000.00 Remaining</span>
        </div>
        <div class="w-full h-2 bg-[#0e0e12] rounded-full overflow-hidden border border-[#202027]">
            <div id="poolProgressBar" class="h-full bg-emerald-500 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    <!-- Workspace: Action & Ledger -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Draw Action (5 Cols) -->
        <div class="lg:col-span-5 ui-card rounded-xl p-6 flex flex-col items-center">
            <div class="w-12 h-12 rounded-xl bg-zinc-800 border border-zinc-700 flex items-center justify-center text-zinc-200 text-lg mb-4">
                <i class="fa-solid fa-gift"></i>
            </div>

            <div class="w-full mb-4">
                <label class="block text-xs font-mono text-zinc-400 mb-1">Participant / Drawer Name</label>
                <input type="text" id="drawerNameInput" placeholder="e.g. Alex (Drawer #1)" class="w-full px-3 py-2 rounded-lg bg-[#0e0e12] border border-[#23232b] text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-500">
            </div>

            <button id="drawShareBtn" onclick="drawBalancerShare()" class="w-full py-2.5 px-4 rounded-lg bg-zinc-100 hover:bg-white text-zinc-900 font-medium text-sm transition cursor-pointer">
                Draw Share
            </button>
            <span class="text-[11px] font-mono text-zinc-500 mt-2">Or press <kbd class="px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-400">Space</kbd></span>

            <!-- Latest Result Box -->
            <div id="latestDrawCallout" class="w-full mt-5 p-4 rounded-xl ui-card-subtle border border-[#23232b] text-center hidden">
                <span class="text-[10px] font-mono uppercase tracking-widest text-zinc-400">Latest Draw</span>
                <div id="latestDrawerName" class="text-xs font-medium text-zinc-300 mt-1">Drawer #1</div>
                <div id="latestDrawAmount" class="text-2xl font-bold text-white font-mono my-0.5">+$1,450.00</div>
                <div id="latestDrawMeta" class="text-[11px] text-zinc-500 font-mono">14.5% of total pool</div>
            </div>
        </div>

        <!-- Ledger Table (7 Cols) -->
        <div class="lg:col-span-7 ui-card rounded-xl p-5">
            <div class="flex items-center justify-between mb-3 text-xs">
                <span class="font-medium text-white">Distribution Ledger</span>
                <span id="ledgerCount" class="font-mono text-zinc-500">0 Draws</span>
            </div>

            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="text-zinc-500 bg-[#0e0e12] sticky top-0 border-b border-[#202027]">
                        <tr>
                            <th class="py-2 px-3 font-medium">#</th>
                            <th class="py-2 px-3 font-medium">Drawer</th>
                            <th class="py-2 px-3 text-right font-medium">Received</th>
                            <th class="py-2 px-3 text-right font-medium">Share</th>
                            <th class="py-2 px-3 text-right font-medium">Remaining</th>
                        </tr>
                    </thead>
                    <tbody id="balancerLedgerBody" class="divide-y divide-[#1c1c23] text-zinc-300">
                        <tr id="emptyLedgerRow">
                            <td colspan="5" class="py-6 text-center text-zinc-600">
                                No draws yet. Configure pool and click "Draw Share".
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let maxPool = 10000;
    let remainingPool = 10000;
    let expectedDrawers = 10;
    let drawCount = 0;
    let ledger = [];

    function applyPoolSettings() {
        const inputMax = parseFloat(document.getElementById('inputMaxPool').value);
        const inputExp = parseInt(document.getElementById('inputExpectedDrawers').value);

        if (isNaN(inputMax) || inputMax <= 0) {
            alert("Please enter a valid max pool amount.");
            return;
        }

        maxPool = inputMax;
        remainingPool = inputMax;
        expectedDrawers = Math.max(1, inputExp || 10);
        drawCount = 0;
        ledger = [];

        updateUI();
        SoundEffects.ding();
    }

    function resetPool() {
        if (confirm("Reset current pool back to initial declared max?")) {
            remainingPool = maxPool;
            drawCount = 0;
            ledger = [];
            updateUI();
            document.getElementById('latestDrawCallout').classList.add('hidden');
        }
    }

    async function drawBalancerShare() {
        if (remainingPool <= 0) {
            alert(`The entire pool of $${maxPool.toLocaleString()} has been fully distributed.`);
            return;
        }

        const nameInput = document.getElementById('drawerNameInput');
        const customName = nameInput.value.trim() || `Drawer #${drawCount + 1}`;
        const algorithm = document.getElementById('selectAlgorithm').value;

        const btn = document.getElementById('drawShareBtn');
        btn.disabled = true;

        try {
            const response = await fetch("{{ route('api.balancer.draw') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    max_pool: maxPool,
                    remaining_pool: remainingPool,
                    drawer_name: customName,
                    expected_drawers: expectedDrawers,
                    draw_count: drawCount,
                    distribution: algorithm
                })
            });

            const data = await response.json();

            if (data.success) {
                remainingPool = data.remaining_pool;
                drawCount++;

                if (data.amount >= maxPool * 0.25) {
                    SoundEffects.jackpot();
                    triggerConfetti(60);
                } else {
                    SoundEffects.ding();
                    triggerConfetti(25);
                }

                const callout = document.getElementById('latestDrawCallout');
                callout.classList.remove('hidden');
                document.getElementById('latestDrawerName').innerText = data.drawer_name;
                document.getElementById('latestDrawAmount').innerText = `+$${data.amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                document.getElementById('latestDrawMeta').innerText = `${data.percentage_of_pool}% of $${maxPool.toLocaleString()} pool`;

                ledger.unshift({
                    id: drawCount,
                    drawer: data.drawer_name,
                    amount: data.amount,
                    percentage: data.percentage_of_pool,
                    remaining: data.remaining_pool,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
                });

                nameInput.value = '';
                updateUI();
            } else {
                alert(data.message || "Draw error");
            }
        } catch (e) {
            console.error(e);
            alert("API connection error.");
        } finally {
            btn.disabled = false;
        }
    }

    function updateUI() {
        const claimed = maxPool - remainingPool;
        const claimedPct = ((claimed / maxPool) * 100).toFixed(1);

        document.getElementById('statMaxPool').innerText = `$${maxPool.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        document.getElementById('statClaimedPool').innerText = `$${claimed.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        document.getElementById('statClaimedPct').innerText = `${claimedPct}% distributed (${drawCount} draws)`;

        document.getElementById('statRemainingPool').innerText = `$${remainingPool.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        
        const drawsLeft = Math.max(0, expectedDrawers - drawCount);
        document.getElementById('statDrawsRemaining').innerText = remainingPool <= 0 ? "Pool fully distributed" : `${drawsLeft} expected draws left`;

        document.getElementById('poolProgressBar').style.width = `${claimedPct}%`;
        document.getElementById('progressBarLabel').innerText = `$${remainingPool.toLocaleString(undefined, { minimumFractionDigits: 2 })} Remaining`;

        const tbody = document.getElementById('balancerLedgerBody');
        document.getElementById('ledgerCount').innerText = `${ledger.length} Draws`;

        if (ledger.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyLedgerRow">
                    <td colspan="5" class="py-6 text-center text-zinc-600">
                        No draws yet. Configure pool and click "Draw Share".
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = '';
        ledger.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2 px-3 text-zinc-500">${item.id}</td>
                <td class="py-2 px-3 text-zinc-200 font-medium">${item.drawer}</td>
                <td class="py-2 px-3 text-right text-emerald-400 font-medium">+$${item.amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
                <td class="py-2 px-3 text-right text-zinc-400">${item.percentage}%</td>
                <td class="py-2 px-3 text-right text-zinc-400">$${item.remaining.toLocaleString(undefined, { minimumFractionDigits: 2 })}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function exportBalancerCSV() {
        if (ledger.length === 0) {
            alert("No entries to export.");
            return;
        }

        let csv = "Draw #,Drawer,Amount,Percentage,Remaining,Timestamp\n";
        ledger.forEach(item => {
            csv += `"${item.id}","${item.drawer}","${item.amount}","${item.percentage}%","${item.remaining}","${item.time}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `balancer_${Date.now()}.csv`;
        a.click();
    }

    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'SELECT') {
            e.preventDefault();
            drawBalancerShare();
        }
    });

    updateUI();
</script>
@endpush
