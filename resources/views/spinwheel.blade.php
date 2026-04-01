<!DOCTYPE html>
<html lang="my">

<head>
    <meta charset="utf-8">
    <title>Spin & Win</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        .dot-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            pointer-events: none;
        }

        .dot-ring div {
            position: absolute;
            width: 12px;
            height: 12px;
            background: #ffffff;
            border-radius: 50%;
            top: 48%;
            left: 48%;
            transform: translate(-50%, -50%);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(120deg,
                    #52B848 0%,
                    #55B9E6 18%,
                    #F7B7C4 55%,
                    #F8D7C0 100%);
            margin: 0;
            padding: 16px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #0f172a;
        }

        .container {
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.96);
            border-radius: 22px;
            padding: 20px 16px 24px;
            box-shadow:
                0 18px 40px rgba(0, 0, 0, 0.65),
                0 0 0 1px rgba(148, 163, 184, 0.18);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            position: relative;
            overflow: hidden;
        }

        /* Glow accents */
        .container::before,
        .container::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            filter: blur(35px);
            opacity: 0.3;
            pointer-events: none;
        }

        .container::before {
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, #22c55e, transparent 70%);
            top: -80px;
            right: -80px;
        }

        .container::after {
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, #f97316, transparent 70%);
            bottom: -110px;
            left: -90px;
        }

        .header {
            text-align: center;
            color: #e5e7eb;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            background: rgba(248, 250, 252, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.4);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #52B848;
            margin-bottom: 6px;
        }

        .tag-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #22c55e;
        }

        h2 {
            font-size: 1.35rem;
            margin: 0;
        }

        .subtitle {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 4px;
        }

        .wheel-wrapper {
            position: relative;
            width: 100%;
            max-width: 310px;
            aspect-ratio: 1 / 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wheel-shadow {
            position: absolute;
            inset: 10%;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 10%, rgba(248, 250, 252, 0.12), transparent 55%),
                radial-gradient(circle at 80% 90%, rgba(59, 130, 246, 0.24), transparent 65%);
            filter: blur(12px);
            opacity: 0.8;
            pointer-events: none;
        }

        .wheel-frame {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background:
                radial-gradient(circle at 30% 0%, rgba(248, 250, 252, 0.2), transparent 60%),
                radial-gradient(circle at 70% 100%, rgba(251, 146, 60, 0.3), transparent 70%);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .wheel-frame-inner {
            width: 86%;
            height: 86%;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 0%, #020617, #020617 50%, #111827 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow:
                0 0 0 1px rgba(148, 163, 184, 0.3),
                inset 0 0 0 1px rgba(15, 23, 42, 0.9);
        }

        .wheel {
            width: 90%;
            height: 90%;
            border-radius: 50%;
            border: 8px solid rgba(15, 23, 42, 0.95);
            background: #55B9E6;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 4s cubic-bezier(.17, .67, .29, 1.27);
            position: relative;
            box-shadow:
                0 16px 40px rgba(15, 23, 42, 0.85),
                inset 0 0 18px rgba(15, 23, 42, 0.8);
        }

        .wheel::after {
            content: "";
            position: absolute;
            inset: 10%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(248, 250, 252, 0.32), transparent 70%);
            opacity: 0.65;
            pointer-events: none;
        }

        /* .pointer {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%) rotate(180deg);
            width: 0;
            height: 0;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
            border-bottom: 22px solid #f97316;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.7));
        }

        .pointer-base {
            position: absolute;
            top: 6px;
            left: 50%;
            transform: translateX(-50%);
            width: 28px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(to right, #fbbf24, #f97316);
            box-shadow:
                0 3px 8px rgba(0, 0, 0, 0.8),
                0 0 0 1px rgba(15, 23, 42, 0.9);
        } */
        .pointer-bottle {
            position: absolute;
            top: 7%;
            /* adjust up/down */
            left: 50%;
            transform: translateX(-50%) rotate(180deg);
            width: 5%;
            height: auto;
            z-index: 20;
            pointer-events: none;
        }

        .center-circle {
            position: relative;
            width: 32%;
            height: 32%;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 0%, #fefce8, #facc15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.85rem;
            text-align: center;
            padding: 4px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #78350f;
            box-shadow:
                0 0 0 2px rgba(250, 204, 21, 0.65),
                0 10px 18px rgba(0, 0, 0, 0.65);
            z-index: 2;
        }

        .center-circle::after {
            content: "";
            position: absolute;
            inset: 18%;
            border-radius: inherit;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.9), transparent 65%);
            opacity: 0.7;
        }

        .center-circle span {
            position: relative;
            z-index: 1;
        }

        .center-circle.spinning {
            animation: pulse 0.9s ease-in-out infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow:
                    0 0 0 2px rgba(250, 204, 21, 0.7),
                    0 10px 18px rgba(0, 0, 0, 0.65);
            }

            50% {
                transform: scale(1.06);
                box-shadow:
                    0 0 0 4px rgba(250, 204, 21, 0.3),
                    0 16px 26px rgba(0, 0, 0, 0.85);
            }

            100% {
                transform: scale(1);
                box-shadow:
                    0 0 0 2px rgba(250, 204, 21, 0.7),
                    0 10px 18px rgba(0, 0, 0, 0.65);
            }
        }

        .wheel-labels {
            position: absolute;
            inset: 18%;
            border-radius: 50%;
            pointer-events: none;
            font-size: 0.62rem;
            font-weight: 700;
            color: #020617;
            text-transform: uppercase;
        }

        .wheel-label {
            position: absolute;
            text-align: center;
            width: 44%;
            transform: translate(-50%, -50%);
            filter: drop-shadow(0 1px 4px rgba(255, 255, 255, 0.7));
        }

        .label-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 7px;
            border-radius: 999px;
            background: rgba(248, 250, 252, 0.78);
        }

        .label-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
        }

        button {
            width: 100%;
            padding: 13px;
            border-radius: 999px;
            border: none;
            background: radial-gradient(circle at 30% 0%, #fefce8, #f97316);
            color: #1f2937;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            box-shadow:
                0 10px 22px rgba(0, 0, 0, 0.8),
                0 0 0 1px rgba(248, 250, 252, 0.12);
            position: relative;
            overflow: hidden;
            margin-top: 2px;
        }

        button::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg,
                    rgba(255, 255, 255, 0.18),
                    transparent 55%,
                    rgba(255, 255, 255, 0.18));
            transform: translateX(-120%);
            transition: transform 0.6s ease;
        }

        button:hover:not(:disabled)::after {
            transform: translateX(120%);
        }

        button:active:not(:disabled) {
            transform: translateY(1px);
            box-shadow:
                0 6px 14px rgba(0, 0, 0, 0.9),
                0 0 0 1px rgba(248, 250, 252, 0.12);
        }

        button:disabled {
            opacity: 0.55;
            cursor: default;
        }

        .result {
            min-height: 44px;
            font-size: 0.92rem;
            text-align: center;
            color: #e5e7eb;
        }

        .result strong {
            color: #52B848;
        }

        .note {
            font-size: 0.78rem;
            color: #9ca3af;
            text-align: center;
            margin: 0;
        }

        .legend {
            width: 100%;
            display: flex;
            justify-content: space-between;
            gap: 6px;
            font-size: 0.75rem;
            color: #cbd5f5;
            margin-top: -4px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 5px 7px;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.35);
            flex: 1;
            justify-content: center;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
        }

        .legend-item span {
            white-space: nowrap;
        }

        @media (max-width: 360px) {
            h2 {
                font-size: 1.2rem;
            }

            .subtitle {
                font-size: 0.75rem;
            }

            .wheel-wrapper {
                max-width: 260px;
            }

            .legend-item {
                padding-inline: 4px;
                font-size: 0.7rem;
            }
        }

        /* .pointer-inside {
            position: absolute;
            bottom: -10px;
            /* moves pointer downward inside the rim */
        left: 50%;
        transform: translateX(-50%) rotate(180deg);
        /* rotate so it points up */
        width: 0;
        height: 0;
        border-left: 15px solid transparent;
        border-right: 15px solid transparent;
        border-top: 22px solid #f97316;
        /* pointer color */
        z-index: 10;
        }

        */
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="tag">

                <h2>Chingu Spin &amp; Win</h2>
            </div>

            <p class="subtitle">

                One Phone Number, One Spin

            </p>
        </div>

        <div class="wheel-wrapper">
            <img src="/images/Peach.png" class="pointer-bottle" alt="pointer">
            <div class="wheel-shadow"></div>




            <!-- NEW DOT RING -->
            <div class="dot-ring"></div>



            <div class="wheel-frame">
                <div class="wheel-frame-inner">
                    <div id="wheel" class="wheel">

                        <div id="centerCircle" class="center-circle">
                            <span>Spin</span>
                        </div>

                        <div id="wheelLabels" class="wheel-labels"></div>
                    </div>
                </div>
            </div>
        </div>

        <button id="spinBtn">SPIN NOW</button>



        <div id="result" class="result"></div>


    </div>

    <script>
        const dotRing = document.querySelector('.dot-ring');
        const wheelWrapper = document.querySelector('.wheel-wrapper');

        // generate 12 dots
        function createDots() {
            dotRing.innerHTML = ""; // clear existing dots (needed on resize)

            const wheelSize = wheelWrapper.offsetWidth; // responsive size
            const radius = wheelSize * 0.47; // 47% looks perfect inside rim

            for (let i = 0; i < 12; i++) {
                const dot = document.createElement('div');
                const angle = (i / 12) * 360;

                const x = radius * Math.cos(angle * Math.PI / 180);
                const y = radius * Math.sin(angle * Math.PI / 180);

                dot.style.transform = `translate(${x}px, ${y}px)`;
                dotRing.appendChild(dot);
            }
        }

        createDots(); // initial load
        window.addEventListener("resize", createDots); // recalc on screen resize


        const wheel = document.getElementById('wheel');
        const wheelLabels = document.getElementById('wheelLabels');
        const spinBtn = document.getElementById('spinBtn');
        const resultDiv = document.getElementById('result');
        const centerCircle = document.getElementById('centerCircle');
        const wheelPrizes = @json($wheelPrizes ?? []);

        let spinning = false;
        let currentRotation = 0;
        const fallbackPrizes = [{
                name: 'Sticker Pack',
                color: '#55B9E6'
            },
            {
                name: 'Fans',
                color: '#F7B7C4'
            },
            {
                name: 'Charm',
                color: '#52B848'
            },
        ];
        const activePrizes = Array.isArray(wheelPrizes) && wheelPrizes.length > 0 ? wheelPrizes : fallbackPrizes;

        function buildWheelVisual() {
            const segmentSize = 360 / activePrizes.length;
            const stops = [];

            activePrizes.forEach((prize, index) => {
                const start = index * segmentSize;
                const end = (index + 1) * segmentSize;
                const color = typeof prize.color === 'string' ? prize.color : fallbackPrizes[index % fallbackPrizes
                    .length].color;
                stops.push(`${color} ${start}deg ${end}deg`);
            });

            wheel.style.background = `conic-gradient(${stops.join(', ')})`;
            wheelLabels.innerHTML = '';

            activePrizes.forEach((prize, index) => {
                const centerDeg = (index * segmentSize) + (segmentSize / 2);
                const rad = (centerDeg - 90) * (Math.PI / 180);
                const radiusPercent = 39;
                const x = 50 + (Math.cos(rad) * radiusPercent);
                const y = 50 + (Math.sin(rad) * radiusPercent);

                const label = document.createElement('span');
                label.className = 'wheel-label';
                label.style.left = `${x}%`;
                label.style.top = `${y}%`;
                label.textContent = prize.name;
                wheelLabels.appendChild(label);
            });
        }

        function computeRotationForSegment(segmentIndex, segmentCount) {
            const normalizedIndex = Number.isInteger(segmentIndex) && segmentIndex >= 0 ? segmentIndex : 0;
            const totalSegments = Number.isInteger(segmentCount) && segmentCount > 0 ? segmentCount : activePrizes
                .length;
            const segmentSize = 360 / totalSegments;
            const centerAngle = (normalizedIndex * segmentSize) + (segmentSize / 2);

            const pointerAngle = 270;
            const baseRotation = pointerAngle - centerAngle;
            const extraSpins = 5 * 360;

            const maxOffset = Math.min(30, Math.max(8, segmentSize / 3));
            const randomOffset = Math.random() * (2 * maxOffset) - maxOffset;

            return currentRotation + extraSpins + baseRotation + randomOffset;
        }

        function startSpinVisual() {
            centerCircle.classList.add('spinning');
        }

        function stopSpinVisual() {
            centerCircle.classList.remove('spinning');
        }

        buildWheelVisual();

        spinBtn.addEventListener('click', () => {
            if (spinning) return;

            spinning = true;
            spinBtn.disabled = true;
            resultDiv.textContent = '';
            resultDiv.classList.remove('result-win');
            startSpinVisual();

            fetch("{{ route('survey.spin.process') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({})
                })
                .then(async (response) => {
                    const status = response.status;
                    const data = await response.json();
                    return {
                        status,
                        data
                    };
                })
                .then(({
                    status,
                    data
                }) => {
                    if (!data.success) {
                        stopSpinVisual();
                        resultDiv.textContent = data.message || 'Something went wrong.';
                        spinning = false;

                        if (status !== 403) {
                            spinBtn.disabled = false;
                        }
                        return;
                    }

                    const prize = data.prize;
                    const segment = Number.isInteger(data.segment) ? data.segment : 0;
                    const segmentCount = Number.isInteger(data.segmentCount) ? data.segmentCount : activePrizes
                        .length;
                    const targetRotation = computeRotationForSegment(segment, segmentCount);
                    currentRotation = targetRotation;

                    wheel.style.transform = `rotate(${targetRotation}deg)`;

                    setTimeout(() => {
                        stopSpinVisual();
                        resultDiv.innerHTML =
                            `🎉 <strong>သင်ရရှိထားသော ဆုမှာ – ${prize}</strong> ဖြစ်ပါတယ်။`;
                        spinning = false;

                        // user used their spin → keep disabled
                        spinBtn.disabled = true;
                    }, 4000);
                })
                .catch(() => {
                    stopSpinVisual();
                    resultDiv.textContent = 'Network error. Please try again.';
                    spinning = false;
                    spinBtn.disabled = false;
                });
        });
    </script>

</body>

</html>
