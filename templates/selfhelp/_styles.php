<?php
// Shared, scoped styles for the Self Help feature. Reuses the same --primary /
// --border / --muted variables from the app shell (templates/layouts/index.php)
// and the .assess-* class names from the assessment feature for a consistent look.
return <<<'CSS'
.selfhelp-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.selfhelp-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.selfhelp-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.selfhelp-feature-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px;
    height: 100%;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.15s ease, transform 0.15s ease;
}

.selfhelp-feature-card:hover {
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    transform: translateY(-2px);
    color: inherit;
}

.selfhelp-feature-icon {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.selfhelp-feature-card h5 {
    font-weight: 700;
    margin-bottom: 6px;
}

.selfhelp-feature-card p {
    color: var(--muted);
    font-size: 0.88rem;
    margin-bottom: 0;
}

.selfhelp-feature-card.is-recommended {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px #bfdbfe;
}

.selfhelp-feature-card.is-urgent {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px #fecaca;
}

.selfhelp-badge-recommended {
    display: inline-block;
    align-self: flex-start;
    background: #eff6ff;
    color: var(--primary);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    margin-bottom: 10px;
}

.selfhelp-badge-urgent {
    display: inline-block;
    align-self: flex-start;
    background: #fee2e2;
    color: #b91c1c;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    margin-bottom: 10px;
}

.breathing-circle-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 16px;
}

.breathing-circle {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle at 30% 30%, #93c5fd, var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 1.05rem;
    text-align: center;
    transition: transform 4s ease-in-out;
    transform: scale(0.7);
    box-shadow: 0 8px 30px rgba(37, 99, 235, 0.35);
}

.breathing-circle.is-inhale {
    transform: scale(1);
    transition-duration: 4s;
}

.breathing-circle.is-hold {
    transform: scale(1);
    transition-duration: 0.3s;
}

.breathing-circle.is-exhale {
    transform: scale(0.7);
    transition-duration: 4s;
}

.pfa-emergency-card {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 16px;
    padding: 22px;
}

.pfa-step {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 10px;
}

.mood-scale {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.mood-scale label {
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 8px 14px;
    cursor: pointer;
    text-align: center;
}

.mood-scale input {
    display: none;
}

.mood-scale input:checked + span {
    color: var(--primary);
    font-weight: 700;
}

.mood-scale input:checked ~ label,
.mood-scale label:has(input:checked) {
    border-color: var(--primary);
    background: #eff6ff;
}
CSS;
