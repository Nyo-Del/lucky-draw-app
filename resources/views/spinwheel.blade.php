@extends('layouts.app')

@section('title', 'Spin Wheel - Lucky Draw')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f1f26] pb-4">
        <div>
            <div class="font-mono text-xs text-zinc-500 uppercase tracking-wider">Mode 01</div>
            <h1 class="text-xl font-semibold text-white tracking-tight">Spin Wheel</h1>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="loadPreset('prizes')" class="px-2.5 py-1.5 rounded-md text-xs ui-card-subtle text-zinc-300 hover:text-white border border-[#23232b] transition-colors cursor-pointer">
                Tech & Cash
            </button>
            <button onclick="loadPreset('fun')" class="px-2.5 py-1.5 rounded-md text-xs ui-card-subtle text-zinc-300 hover:text-white border border-[#23232b] transition-colors cursor-pointer">
                Team Lunch
            </button>
            <button onclick="clearHistory()" class="px-2.5 py-1.5 rounded-md text-xs text-zinc-400 hover:text-zinc-200 border border-[#23232b] transition-colors cursor-pointer">
                Clear Log
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Wheel Area (7 Cols) -->
        <div class="lg:col-span-7 flex flex-col items-center ui-card rounded-xl p-6 sm:p-8">
            <!-- Sleek Minimalist Top Needle -->
            <div class="relative z-20 -mb-3 flex flex-col items-center">
                <div class="w-0 h-0 border-l-[10px] border-l-transparent border-r-[10px] border-r-transparent border-t-[18px] border-t-white drop-shadow-sm"></div>
            </div>

            <!-- Canvas Container -->
            <div class="relative w-full max-w-[420px] aspect-square flex items-center justify-center">
                <canvas id="wheelCanvas" width="840" height="840" class="w-full h-full"></canvas>

                <!-- Clean Center Hub -->
                <button id="spinCenterBtn" onclick="spinWheel()" class="absolute z-10 w-20 h-20 rounded-full bg-[#16161c] border-2 border-[#2b2b36] hover:border-zinc-400 text-white font-medium text-xs tracking-wider flex flex-col items-center justify-center transition-all cursor-pointer">
                    <span>SPIN</span>
                </button>
            </div>

            <!-- Spin Trigger Control -->
            <div class="mt-6 w-full max-w-sm flex flex-col items-center gap-2">
                <button id="mainSpinBtn" onclick="spinWheel()" class="w-full py-2.5 px-4 rounded-lg bg-zinc-100 hover:bg-white text-zinc-900 font-medium text-sm transition-colors cursor-pointer">
                    Spin the Wheel
                </button>
                <span class="text-[11px] font-mono text-zinc-500">Or press <kbd class="px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-400">Space</kbd></span>
            </div>
        </div>

        <!-- Sidebar: Items Manager & History (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Prizes Manager -->
            <div class="ui-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <span class="font-medium text-white">Wheel Slices (<span id="prizesCount">0</span>)</span>
                    <span class="font-mono text-zinc-500">Customizable</span>
                </div>

                <!-- Add Input -->
                <div class="flex gap-2 mb-3">
                    <input type="text" id="newPrizeInput" placeholder="Add prize item..." class="flex-1 px-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-500">
                    <input type="color" id="newPrizeColor" value="#3b82f6" title="Pick Color" class="w-8 h-8 rounded-lg bg-[#0e0e12] border border-[#23232b] cursor-pointer p-0.5">
                    <button onclick="addCustomPrize()" class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-medium transition cursor-pointer">
                        Add
                    </button>
                </div>

                <!-- Items List -->
                <div id="prizesList" class="space-y-1.5 max-h-56 overflow-y-auto pr-1 text-xs"></div>
            </div>

            <!-- Spin History -->
            <div class="ui-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <span class="font-medium text-white">History</span>
                    <button onclick="exportHistoryCSV()" class="text-zinc-500 hover:text-zinc-300 font-mono text-[11px] flex items-center gap-1 cursor-pointer">
                        Export CSV
                    </button>
                </div>
                <div id="historyList" class="space-y-1.5 max-h-44 overflow-y-auto pr-1 text-xs">
                    <div class="text-[11px] text-zinc-600 font-mono py-3 text-center">No spins logged yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clean Winner Modal -->
<div id="winnerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200">
    <div class="ui-card border border-zinc-700 rounded-xl p-6 max-w-sm w-full text-center relative" id="winnerModalBox">
        <div class="w-10 h-10 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center text-zinc-200 mx-auto mb-3">
            <i class="fa-solid fa-trophy text-sm"></i>
        </div>
        <span class="text-[11px] font-mono uppercase tracking-widest text-zinc-400">Selected Prize</span>
        <h2 id="winnerName" class="text-xl font-semibold text-white mt-1 break-words">Prize Name</h2>
        
        <div class="mt-6 flex gap-2">
            <button onclick="closeWinnerModal()" class="flex-1 py-2 px-3 rounded-lg bg-zinc-100 hover:bg-white text-zinc-900 font-medium text-xs transition cursor-pointer">
                Confirm
            </button>
            <button onclick="closeWinnerModal(); spinWheel();" class="py-2 px-3 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-medium text-xs transition cursor-pointer">
                Spin Again
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Sophisticated, curated color palette (no clownish rainbow)
    let prizes = [
        { name: "iPhone 16 Pro", color: "#1e293b" },
        { name: "$1,000 Cash", color: "#065f46" },
        { name: "PlayStation 5", color: "#312e81" },
        { name: "Better Luck Next Time", color: "#27272a" },
        { name: "$500 Gift Card", color: "#854d0e" },
        { name: "AirPods Max", color: "#4c1d95" },
        { name: "Gold Bar 1oz", color: "#713f12" },
        { name: "Mystery Box", color: "#1e1b4b" }
    ];

    const presets = {
        prizes: [
            { name: "iPhone 16 Pro", color: "#1e293b" },
            { name: "$1,000 Cash", color: "#065f46" },
            { name: "PlayStation 5", color: "#312e81" },
            { name: "Better Luck Next Time", color: "#27272a" },
            { name: "$500 Gift Card", color: "#854d0e" },
            { name: "AirPods Max", color: "#4c1d95" },
            { name: "Gold Bar 1oz", color: "#713f12" },
            { name: "Mystery Box", color: "#1e1b4b" }
        ],
        fun: [
            { name: "Free Pizza", color: "#831843" },
            { name: "Starbucks Coffee", color: "#065f46" },
            { name: "Team Lunch", color: "#854d0e" },
            { name: "Skip 1 Chore", color: "#312e81" },
            { name: "Dessert Treat", color: "#1e293b" },
            { name: "Movie Night", color: "#4c1d95" }
        ]
    };

    let historyLog = [];
    let isSpinning = false;
    let currentAngle = 0;
    let lastTickIndex = -1;

    const canvas = document.getElementById('wheelCanvas');
    const ctx = canvas.getContext('2d');

    function drawWheel() {
        const numPrizes = prizes.length;
        if (numPrizes === 0) return;

        const sliceAngle = (2 * Math.PI) / numPrizes;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = centerX - 16;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Clean outer ring border
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius + 2, 0, 2 * Math.PI);
        ctx.fillStyle = '#1c1c24';
        ctx.fill();

        // Draw Slices
        for (let i = 0; i < numPrizes; i++) {
            const angle = currentAngle + i * sliceAngle;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, angle, angle + sliceAngle);
            ctx.closePath();
            ctx.fillStyle = prizes[i].color;
            ctx.fill();
            ctx.strokeStyle = '#0e0e12';
            ctx.lineWidth = 2;
            ctx.stroke();

            // Text
            ctx.save();
            ctx.translate(centerX, centerY);
            ctx.rotate(angle + sliceAngle / 2);
            ctx.textAlign = "right";
            ctx.fillStyle = "#f4f4f5";
            ctx.font = "500 24px Inter, sans-serif";
            
            let text = prizes[i].name;
            if (text.length > 18) text = text.substring(0, 16) + '...';
            ctx.fillText(text, radius - 35, 8);
            ctx.restore();

            // Subtle perimeter tick pin
            const pegX = centerX + (radius - 8) * Math.cos(angle);
            const pegY = centerY + (radius - 8) * Math.sin(angle);
            ctx.beginPath();
            ctx.arc(pegX, pegY, 3, 0, 2 * Math.PI);
            ctx.fillStyle = '#71717a';
            ctx.fill();
        }

        // Inner circle
        ctx.beginPath();
        ctx.arc(centerX, centerY, 65, 0, 2 * Math.PI);
        ctx.fillStyle = '#0e0e12';
        ctx.fill();
        ctx.strokeStyle = '#272730';
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    function renderPrizesList() {
        const listEl = document.getElementById('prizesList');
        document.getElementById('prizesCount').innerText = prizes.length;
        listEl.innerHTML = '';

        prizes.forEach((prize, index) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-xs';
            row.innerHTML = `
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: ${prize.color}"></span>
                    <span class="text-zinc-200">${prize.name}</span>
                </div>
                <button onclick="removePrize(${index})" class="text-zinc-500 hover:text-zinc-300 transition cursor-pointer" title="Delete">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;
            listEl.appendChild(row);
        });

        drawWheel();
    }

    function addCustomPrize() {
        const input = document.getElementById('newPrizeInput');
        const colorInput = document.getElementById('newPrizeColor');
        const name = input.value.trim();
        if (!name) return;

        prizes.push({
            name: name,
            color: colorInput.value
        });
        input.value = '';
        renderPrizesList();
    }

    function removePrize(index) {
        if (prizes.length <= 2) {
            alert("A lucky wheel must have at least 2 prizes.");
            return;
        }
        prizes.splice(index, 1);
        renderPrizesList();
    }

    function loadPreset(key) {
        if (presets[key]) {
            prizes = JSON.parse(JSON.stringify(presets[key]));
            renderPrizesList();
        }
    }

    function spinWheel() {
        if (isSpinning || prizes.length === 0) return;

        isSpinning = true;
        document.getElementById('mainSpinBtn').disabled = true;
        document.getElementById('mainSpinBtn').classList.add('opacity-50');

        const numPrizes = prizes.length;
        const sliceAngle = (2 * Math.PI) / numPrizes;
        const winningIndex = Math.floor(Math.random() * numPrizes);
        
        const targetSliceCenter = (3 * Math.PI / 2) - (winningIndex * sliceAngle + sliceAngle / 2);
        const totalRotations = (5 + Math.floor(Math.random() * 2)) * 2 * Math.PI;
        
        const startAngle = currentAngle % (2 * Math.PI);
        const targetAngle = startAngle + totalRotations + (targetSliceCenter - (startAngle % (2 * Math.PI)));
        const deltaAngle = targetAngle - startAngle;

        const duration = 4500;
        const startTime = performance.now();

        function animate(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Clean ease-out cubic
            const easeOut = 1 - Math.pow(1 - progress, 3);
            currentAngle = startAngle + deltaAngle * easeOut;

            const currentSlice = Math.floor(((3 * Math.PI / 2 - currentAngle) % (2 * Math.PI) + 2 * Math.PI) / sliceAngle) % numPrizes;
            if (currentSlice !== lastTickIndex) {
                lastTickIndex = currentSlice;
                SoundEffects.tick();
            }

            drawWheel();

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                isSpinning = false;
                document.getElementById('mainSpinBtn').disabled = false;
                document.getElementById('mainSpinBtn').classList.remove('opacity-50');
                showWinner(prizes[winningIndex].name);
            }
        }

        requestAnimationFrame(animate);
    }

    function showWinner(prizeName) {
        SoundEffects.winFanfare();
        triggerConfetti(45);

        document.getElementById('winnerName').innerText = prizeName;
        const modal = document.getElementById('winnerModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);

        historyLog.unshift({
            prize: prizeName,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
        });
        updateHistoryUI();
    }

    function closeWinnerModal() {
        const modal = document.getElementById('winnerModal');
        modal.classList.add('opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    function updateHistoryUI() {
        const list = document.getElementById('historyList');
        if (historyLog.length === 0) {
            list.innerHTML = `<div class="text-[11px] text-zinc-600 font-mono py-3 text-center">No spins logged yet.</div>`;
            return;
        }

        list.innerHTML = '';
        historyLog.forEach((item, idx) => {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-2 rounded-lg bg-[#0e0e12] border border-[#1f1f26] text-xs';
            div.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[11px] text-zinc-500">${historyLog.length - idx}.</span>
                    <span class="font-medium text-zinc-200">${item.prize}</span>
                </div>
                <span class="font-mono text-[11px] text-zinc-500">${item.time}</span>
            `;
            list.appendChild(div);
        });
    }

    function clearHistory() {
        historyLog = [];
        updateHistoryUI();
    }

    function exportHistoryCSV() {
        if (historyLog.length === 0) {
            alert("No history to export.");
            return;
        }
        let csv = "Index,Prize,Timestamp\n";
        historyLog.forEach((item, i) => {
            csv += `"${historyLog.length - i}","${item.prize}","${item.time}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `spinwheel_${Date.now()}.csv`;
        a.click();
    }

    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            spinWheel();
        }
    });

    renderPrizesList();
</script>
@endpush
