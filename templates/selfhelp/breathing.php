<?php ob_start(); ?>

<div class="selfhelp-page">
    <div class="page-head">
        <div>
            <h1>🫁 Latihan Pernapasan</h1>
            <p>Teknik box breathing (tarik napas &ndash; tahan &ndash; hembuskan &ndash; tahan, masing-masing 4 detik) untuk meredakan cemas dan tegang.</p>
        </div>
        <a href="/self-help" class="btn btn-outline-secondary btn-sm">&larr; Kembali</a>
    </div>

    <div class="assess-card assess-card-body">
        <div class="breathing-circle-wrap">
            <div class="breathing-circle" id="breathingCircle">Mulai</div>
        </div>

        <div class="text-center mb-3">
            <div class="text-muted small">Siklus selesai: <strong id="cycleCount">0</strong></div>
        </div>

        <div class="d-flex justify-content-center gap-2 mb-3">
            <button type="button" class="btn btn-primary" id="startBtn">▶️ Mulai</button>
            <button type="button" class="btn btn-outline-secondary" id="stopBtn" disabled>⏹️ Berhenti</button>
        </div>

        <div class="text-center text-muted small">
            Cari posisi duduk yang nyaman, lalu ikuti gerakan lingkaran di atas melalui hidung. Lakukan 5&ndash;10 siklus atau selama yang kamu butuhkan.
        </div>
    </div>
</div>

<script>
    (function() {
        var circle = document.getElementById('breathingCircle');
        var startBtn = document.getElementById('startBtn');
        var stopBtn = document.getElementById('stopBtn');
        var cycleCountEl = document.getElementById('cycleCount');

        var phases = [{
                label: 'Tarik Napas...',
                cls: 'is-inhale',
                duration: 4000
            },
            {
                label: 'Tahan...',
                cls: 'is-hold',
                duration: 4000
            },
            {
                label: 'Hembuskan...',
                cls: 'is-exhale',
                duration: 4000
            },
            {
                label: 'Tahan...',
                cls: 'is-hold',
                duration: 4000
            }
        ];

        var phaseIndex = 0;
        var cycles = 0;
        var timer = null;
        var running = false;

        function runPhase() {
            var phase = phases[phaseIndex];
            circle.className = 'breathing-circle ' + phase.cls;
            circle.textContent = phase.label;

            timer = setTimeout(function() {
                phaseIndex = (phaseIndex + 1) % phases.length;
                if (phaseIndex === 0) {
                    cycles += 1;
                    cycleCountEl.textContent = cycles;
                }
                if (running) {
                    runPhase();
                }
            }, phase.duration);
        }

        startBtn.addEventListener('click', function() {
            if (running) {
                return;
            }
            running = true;
            phaseIndex = 0;
            startBtn.disabled = true;
            stopBtn.disabled = false;
            runPhase();
        });

        stopBtn.addEventListener('click', function() {
            running = false;
            clearTimeout(timer);
            phaseIndex = 0;
            cycles = 0;
            cycleCountEl.textContent = cycles;
            circle.className = 'breathing-circle';
            circle.textContent = 'Selesai';
            startBtn.disabled = false;
            stopBtn.disabled = true;
        });
    })();
</script>

<?php
$content = ob_get_clean();
$pageTitle = $title ?? 'Latihan Pernapasan';
$extraStyles = require __DIR__ . '/_styles.php';
require __DIR__ . '/../layouts/index.php';
