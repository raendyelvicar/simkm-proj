(function () {
    'use strict';

    var init = window.ASSESS_INIT || { questions: [], answers: {}, remainingSeconds: 0, sessionId: 0 };
    var questions = init.questions || [];
    var answers = {};
    Object.keys(init.answers || {}).forEach(function (qid) {
        answers[qid] = init.answers[qid];
    });

    var currentIndex = 0;
    var firstUnanswered = questions.findIndex(function (q) { return !answers[q.id]; });
    if (firstUnanswered !== -1) {
        currentIndex = firstUnanswered;
    }

    var remainingSeconds = init.remainingSeconds;
    var finished = false;
    var finishing = false;

    var timerEl = document.getElementById('assess-timer');
    var railEl = document.getElementById('assess-rail');
    var labelEl = document.getElementById('assess-question-label');
    var textEl = document.getElementById('assess-question-text');
    var choicesEl = document.getElementById('assess-choices');
    var progressEl = document.getElementById('assess-progress');
    var prevBtn = document.getElementById('assess-prev');
    var nextBtn = document.getElementById('assess-next');
    var finishBtn = document.getElementById('assess-finish');

    function typeLabel(type) {
        return type === 'bdi2' ? 'BDI-II' : 'PWB';
    }

    function answeredCount() {
        return Object.keys(answers).length;
    }

    function formatTime(seconds) {
        seconds = Math.max(0, seconds);
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    }

    function renderTimer() {
        timerEl.textContent = formatTime(remainingSeconds);
        timerEl.classList.toggle('assess-timer-warning', remainingSeconds <= 300);
    }

    function renderRail() {
        railEl.innerHTML = '';
        var lastType = null;

        questions.forEach(function (q, idx) {
            if (q.type !== lastType) {
                var group = document.createElement('div');
                group.className = 'assess-rail-label';
                group.textContent = typeLabel(q.type);
                railEl.appendChild(group);
                lastType = q.type;
            }

            var dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'assess-dot';
            if (answers[q.id]) {
                dot.classList.add('assess-dot-answered');
            }
            if (idx === currentIndex) {
                dot.classList.add('assess-dot-current');
            }
            dot.textContent = idx + 1;
            dot.setAttribute('aria-label', typeLabel(q.type) + ' nomor ' + (q.order_no));
            dot.addEventListener('click', function () {
                currentIndex = idx;
                renderAll();
            });
            railEl.appendChild(dot);
        });
    }

    function renderQuestion() {
        var q = questions[currentIndex];
        if (!q) {
            return;
        }

        labelEl.textContent = typeLabel(q.type) + ' — Pertanyaan ' + q.order_no;
        textEl.textContent = q.question_text;

        choicesEl.innerHTML = '';
        q.choices.forEach(function (choice) {
            var label = document.createElement('label');
            label.className = 'assess-option';

            var input = document.createElement('input');
            input.type = 'radio';
            input.name = 'q' + q.id;
            input.value = choice.id;
            if (answers[q.id] === choice.id) {
                input.checked = true;
            }
            input.addEventListener('change', function () {
                submitAnswer(q.id, choice.id);
            });

            var span = document.createElement('span');
            span.textContent = choice.label;

            label.appendChild(input);
            label.appendChild(span);
            choicesEl.appendChild(label);
        });

        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex === questions.length - 1;
        progressEl.textContent = answeredCount() + ' / ' + questions.length + ' terjawab';
    }

    function renderAll() {
        renderRail();
        renderQuestion();
        renderTimer();
    }

    function redirectTo(url) {
        finished = true;
        window.location = url;
    }

    function submitAnswer(questionId, choiceId) {
        fetch('/assessment/session/answer', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question_id: questionId, choice_id: choiceId })
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.expired) {
                    redirectTo(data.redirect);
                    return;
                }
                if (!data.ok) {
                    return;
                }
                answers[questionId] = choiceId;
                remainingSeconds = data.remaining_seconds;
                renderAll();
            })
            .catch(function () { /* transient network hiccup — user's selection stays visible, next click retries */ });
    }

    function finishSession() {
        if (finishing) {
            return;
        }
        finishing = true;

        fetch('/assessment/session/finish', { method: 'POST' })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                redirectTo(data.redirect);
            })
            .catch(function () { finishing = false; });
    }

    function pollState() {
        if (finished) {
            return;
        }
        fetch('/assessment/session/state')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                remainingSeconds = data.remaining_seconds;
                if (data.expired) {
                    redirectTo(data.redirect);
                    return;
                }
                renderTimer();
            })
            .catch(function () { /* next poll retries */ });
    }

    prevBtn.addEventListener('click', function () {
        if (currentIndex > 0) {
            currentIndex--;
            renderAll();
        }
    });

    nextBtn.addEventListener('click', function () {
        if (currentIndex < questions.length - 1) {
            currentIndex++;
            renderAll();
        }
    });

    finishBtn.addEventListener('click', function () {
        var unanswered = questions.length - answeredCount();
        if (unanswered > 0 && !window.confirm(unanswered + ' pertanyaan belum dijawab, tetap kirim?')) {
            return;
        }
        finishSession();
    });

    setInterval(function () {
        if (finished) {
            return;
        }
        remainingSeconds--;
        renderTimer();
        if (remainingSeconds <= 0) {
            finishSession();
        }
    }, 1000);

    setInterval(pollState, 30000);

    renderAll();
})();
