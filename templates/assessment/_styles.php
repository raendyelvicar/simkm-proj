<?php
// Shared, scoped styles for the self-assessment feature (BDI-II + PWB).
// Reuses the color variables already defined in the app shell
// (templates/layouts/index.php) so the accent stays consistent app-wide.
return <<<'CSS'
.assess-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.assess-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.assess-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.assess-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.assess-card-body {
    padding: 24px;
}

.assess-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.82rem;
}

.assess-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.assess-badge-green   { background: #dcfce7; color: #15803d; }
.assess-badge-yellow  { background: #fef9c3; color: #a16207; }
.assess-badge-orange  { background: #ffedd5; color: #c2410c; }
.assess-badge-red     { background: #fee2e2; color: #b91c1c; }
.assess-badge-gray    { background: #f1f5f9; color: #475569; }

.assess-question {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 14px;
}

.assess-question-title {
    font-weight: 600;
    margin-bottom: 10px;
}

.assess-option {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
}

.assess-option:hover {
    background: #f8fafc;
}

.assess-option input {
    margin-top: 4px;
}

.assess-dimension-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 14px;
    margin-top: 16px;
}

.assess-dimension-card {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
}

.assess-dimension-card h6 {
    font-weight: 700;
    margin-bottom: 6px;
}

.assess-score-hero {
    text-align: center;
    padding: 28px 16px;
}

.assess-score-hero .score {
    font-size: 2.4rem;
    font-weight: 800;
    color: var(--primary);
}

.assess-sticky-submit {
    position: sticky;
    bottom: 0;
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 14px 0;
    margin-top: 8px;
}

.assess-table th, .assess-table td {
    vertical-align: middle;
}

.assess-timer {
    font-size: 1.3rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    padding: 6px 16px;
    border-radius: 999px;
    background: #eff6ff;
    color: var(--primary);
    border: 1px solid #bfdbfe;
}

.assess-timer-warning {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #fecaca;
}

.assess-rail {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}

.assess-rail-label {
    width: 100%;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin: 10px 0 2px;
}

.assess-rail-label:first-child {
    margin-top: 0;
}

.assess-dot {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text);
    font-size: 0.78rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 0;
}

.assess-dot:hover {
    border-color: var(--primary);
}

.assess-dot-answered {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

.assess-dot-current {
    box-shadow: 0 0 0 3px #bfdbfe;
}

.assess-question-nav-label {
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 700;
    font-size: 0.75rem;
}

.assess-question-text {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 16px;
}

.assess-stat-tile {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}

.assess-stat-tile .value {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--primary);
}

.assess-stat-tile .label {
    color: var(--muted);
    font-size: 0.8rem;
}
CSS;
