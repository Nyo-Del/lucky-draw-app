@extends('layouts.app')

@section('title', 'Dice - Lucky Draw')

@push('styles')
<style>
    .dice-scene {
        perspective: 900px;
        width: 130px;
        height: 130px;
    }

    .cube {
        width: 100%;
        height: 100%;
        position: relative;
        transform-style: preserve-3d;
        transition: transform 1.2s cubic-bezier(0.2, 0.8, 0.3, 1);
    }

    .cube-face {
        position: absolute;
        width: 130px;
        height: 130px;
        background: #18181f;
        border: 1px solid #32323e;
        border-radius: 20px;
        display: grid;
        padding: 20px;
    }

    .pip {
        display: block;
        align-self: center;
        justify-self: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #f4f4f5;
    }

    .face-1 { transform: rotateY(0deg) translateZ(65px); grid-template: 1fr / 1fr; }
    .face-6 { transform: rotateY(180deg) translateZ(65px); grid-template: repeat(3, 1fr) / repeat(2, 1fr); }
    .face-2 { transform: rotateY(-90deg) translateZ(65px); grid-template: repeat(2, 1fr) / repeat(2, 1fr); }
    .face-5 { transform: rotateY(90deg) translateZ(65px); grid-template: repeat(3, 1fr) / repeat(3, 1fr); }
    .face-3 { transform: rotateX(-90deg) translateZ(65px); grid-template: repeat(3, 1fr) / repeat(3, 1fr); }
    .face-4 { transform: rotateX(90deg) translateZ(65px); grid-template: repeat(2, 1fr) / repeat(2, 1fr); }

    .face-2 .pip:nth-child(2) { grid-column: 2; grid-row: 2; }
    .face-3 .pip:nth-child(1) { grid-column: 1; grid-row: 1; }
    .face-3 .pip:nth-child(2) { grid-column: 2; grid-row: 2; }
    .face-3 .pip:nth-child(3) { grid-column: 3; grid-row: 3; }
    .face-5 .pip:nth-child(1) { grid-column: 1; grid-row: 1; }
    .face-5 .pip:nth-child(2) { grid-column: 3; grid-row: 1; }
    .face-5 .pip:nth-child(3) { grid-column: 2; grid-row: 2; }
    .face-5 .pip:nth-child(4) { grid-column: 1; grid-row: 3; }
    .face-5 .pip:nth-child(5) { grid-column: 3; grid-row: 3; }

    @keyframes tumbleClean {
        0% { transform: rotateX(0deg) rotateY(0deg) rotateZ(0deg) translateY(0); }
        25% { transform: rotateX(360deg) rotateY(180deg) rotateZ(90deg) translateY(-40px); }
        50% { transform: rotateX(720deg) rotateY(540deg) rotateZ(270deg) translateY(-60px); }
        75% { transform: rotateX(1080deg) rotateY(900deg) rotateZ(450deg) translateY(-20px); }
        100% { transform: rotateX(1440deg) rotateY(1260deg) rotateZ(720deg) translateY(0); }
    }

    .tumbling {
        animation: tumbleClean 1.2s cubic-bezier(0.25, 1, 0.5, 1) infinite;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1f1f26] pb-4">
        <div>
            <div class="font-mono text-xs text-zinc-500 uppercase tracking-wider">Mode 02</div>
            <h1 class="text-xl font-semibold text-white tracking-tight">Dice (1 Item Chooser)</h1>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="loadDicePreset('team')" class="px-2.5 py-1.5 rounded-md text-xs ui-card-subtle text-zinc-300 hover:text-white border border-[#23232b] transition-colors cursor-pointer">
                Team Members
            </button>
            <button onclick="loadDicePreset('food')" class="px-2.5 py-1.5 rounded-md text-xs ui-card-subtle text-zinc-300 hover:text-white border border-[#23232b] transition-colors cursor-pointer">
                Dinner Choices
            </button>
            <button onclick="clearDiceHistory()" class="px-2.5 py-1.5 rounded-md text-xs text-zinc-400 hover:text-zinc-200 border border-[#23232b] transition-colors cursor-pointer">
                Clear Log
            </button>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- 3D Dice Stage (7 Cols) -->
        <div class="lg:col-span-7 flex flex-col items-center ui-card rounded-xl p-8">
            <!-- 3D Cube -->
            <div class="py-10 flex justify-center items-center">
                <div class="dice-scene">
                    <div id="diceCube" class="cube">
                        <div class="cube-face face-1"><span class="pip"></span></div>
                        <div class="cube-face face-2"><span class="pip"></span><span class="pip"></span></div>
                        <div class="cube-face face-3"><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>
                        <div class="cube-face face-4"><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>
                        <div class="cube-face face-5"><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>
                        <div class="cube-face face-6"><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span><span class="pip"></span></div>
                    </div>
                </div>
            </div>

            <!-- Result Box -->
            <div id="winnerSpotlight" class="w-full max-w-sm p-4 rounded-xl ui-card-subtle border border-[#23232b] text-center my-2">
                <span id="chosenDiceFaceBadge" class="text-[11px] font-mono text-zinc-500 uppercase tracking-widest block mb-1">
                    Ready to Roll
                </span>
                <div id="chosenItemDisplay" class="text-xl font-medium text-white break-words py-0.5">
                    Click "Roll Dice"
                </div>
            </div>

            <!-- Controls -->
            <div class="w-full max-w-sm mt-4 flex flex-col items-center gap-2">
                <button id="rollDiceBtn" onclick="rollDice()" class="w-full py-2.5 px-4 rounded-lg bg-zinc-100 hover:bg-white text-zinc-900 font-medium text-sm transition-colors cursor-pointer">
                    Roll Dice
                </button>
                <span class="text-[11px] font-mono text-zinc-500">Or press <kbd class="px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-400">Space</kbd></span>
            </div>
        </div>

        <!-- Sidebar (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            <!-- Items Pool -->
            <div class="ui-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <span class="font-medium text-white">Item Candidates (<span id="itemsCount">0</span>)</span>
                    <button onclick="shuffleItems()" class="text-zinc-400 hover:text-white font-mono text-[11px] cursor-pointer">
                        Shuffle
                    </button>
                </div>

                <div class="flex gap-2 mb-3">
                    <input type="text" id="newDiceItemInput" placeholder="Add candidate name..." class="flex-1 px-3 py-1.5 rounded-lg bg-[#0e0e12] border border-[#23232b] text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-500">
                    <button onclick="addDiceItem()" class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-white text-xs font-medium transition cursor-pointer">
                        Add
                    </button>
                </div>

                <div id="diceItemsList" class="space-y-1.5 max-h-56 overflow-y-auto pr-1 text-xs"></div>
            </div>

            <!-- History -->
            <div class="ui-card rounded-xl p-5">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <span class="font-medium text-white">History</span>
                    <button onclick="exportDiceHistoryCSV()" class="text-zinc-500 hover:text-zinc-300 font-mono text-[11px] cursor-pointer">
                        Export CSV
                    </button>
                </div>
                <div id="diceHistoryList" class="space-y-1.5 max-h-44 overflow-y-auto pr-1 text-xs">
                    <div class="text-[11px] text-zinc-600 font-mono py-3 text-center">No rolls logged yet.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let diceItems = [
        "Alex Chen",
        "Sarah Jenkins",
        "David Miller",
        "Elena Rostova",
        "Michael Chang",
        "Jessica Taylor"
    ];

    const dicePresets = {
        team: [
            "Alex Chen",
            "Sarah Jenkins",
            "David Miller",
            "Elena Rostova",
            "Michael Chang",
            "Jessica Taylor"
        ],
        food: [
            "Ramen & Noodles",
            "Artisan Pizza",
            "Burgers",
            "Mexican Tacos",
            "Thai Bistro",
            "Fresh Salad Bar"
        ]
    };

    let diceHistory = [];
    let isRolling = false;

    const faceRotations = {
        1: { x: 0, y: 0 },
        2: { x: 0, y: 90 },
        3: { x: 90, y: 0 },
        4: { x: -90, y: 0 },
        5: { x: 0, y: -90 },
        6: { x: 180, y: 0 }
    };

    function renderDiceItems() {
        const list = document.getElementById('diceItemsList');
        document.getElementById('itemsCount').innerText = diceItems.length;
        list.innerHTML = '';

        diceItems.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between p-2 rounded-lg bg-[#0e0e12] border border-[#202027] text-xs';
            row.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[11px] text-zinc-500 w-4">${index + 1}.</span>
                    <span class="text-zinc-200">${item}</span>
                </div>
                <button onclick="removeDiceItem(${index})" class="text-zinc-500 hover:text-zinc-300 transition cursor-pointer" title="Delete">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;
            list.appendChild(row);
        });
    }

    function addDiceItem() {
        const input = document.getElementById('newDiceItemInput');
        const val = input.value.trim();
        if (!val) return;
        diceItems.push(val);
        input.value = '';
        renderDiceItems();
    }

    function removeDiceItem(idx) {
        if (diceItems.length <= 1) {
            alert("At least 1 item is required.");
            return;
        }
        diceItems.splice(idx, 1);
        renderDiceItems();
    }

    function shuffleItems() {
        for (let i = diceItems.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [diceItems[i], diceItems[j]] = [diceItems[j], diceItems[i]];
        }
        renderDiceItems();
    }

    function loadDicePreset(key) {
        if (dicePresets[key]) {
            diceItems = [...dicePresets[key]];
            renderDiceItems();
        }
    }

    function rollDice() {
        if (isRolling || diceItems.length === 0) return;

        isRolling = true;
        const btn = document.getElementById('rollDiceBtn');
        const cube = document.getElementById('diceCube');
        const badge = document.getElementById('chosenDiceFaceBadge');
        const display = document.getElementById('chosenItemDisplay');

        btn.disabled = true;
        btn.classList.add('opacity-50');

        SoundEffects.diceRoll();
        cube.classList.add('tumbling');
        badge.innerText = "Rolling...";
        display.innerText = "Selecting...";

        const winningItemIndex = Math.floor(Math.random() * diceItems.length);
        const chosenItem = diceItems[winningItemIndex];

        const diceFace = Math.floor(Math.random() * 6) + 1;
        const rotation = faceRotations[diceFace];

        const extraRotations = 4;
        const finalX = rotation.x + (extraRotations * 360);
        const finalY = rotation.y + (extraRotations * 360);

        setTimeout(() => {
            cube.classList.remove('tumbling');
            cube.style.transform = `rotateX(${finalX}deg) rotateY(${finalY}deg)`;

            SoundEffects.ding();
            triggerConfetti(35);

            badge.innerText = `Rolled a ${diceFace}`;
            display.innerText = chosenItem;

            diceHistory.unshift({
                item: chosenItem,
                face: diceFace,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })
            });
            updateDiceHistoryUI();

            isRolling = false;
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        }, 1100);
    }

    function updateDiceHistoryUI() {
        const list = document.getElementById('diceHistoryList');
        if (diceHistory.length === 0) {
            list.innerHTML = `<div class="text-[11px] text-zinc-600 font-mono py-3 text-center">No rolls logged yet.</div>`;
            return;
        }

        list.innerHTML = '';
        diceHistory.forEach((h, idx) => {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-2 rounded-lg bg-[#0e0e12] border border-[#1f1f26] text-xs';
            div.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="font-mono text-[11px] text-zinc-400 bg-zinc-800 px-1.5 py-0.5 rounded">d${h.face}</span>
                    <span class="font-medium text-zinc-200">${h.item}</span>
                </div>
                <span class="font-mono text-[11px] text-zinc-500">${h.time}</span>
            `;
            list.appendChild(div);
        });
    }

    function clearDiceHistory() {
        diceHistory = [];
        updateDiceHistoryUI();
    }

    function exportDiceHistoryCSV() {
        if (diceHistory.length === 0) {
            alert("No history to export.");
            return;
        }
        let csv = "Index,Face,Chosen Item,Timestamp\n";
        diceHistory.forEach((h, i) => {
            csv += `"${diceHistory.length - i}","${h.face}","${h.item}","${h.time}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `dice_${Date.now()}.csv`;
        a.click();
    }

    window.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && e.target.tagName !== 'INPUT') {
            e.preventDefault();
            rollDice();
        }
    });

    renderDiceItems();
</script>
@endpush
