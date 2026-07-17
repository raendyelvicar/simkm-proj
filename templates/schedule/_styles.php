<?php
// Shared, scoped styles for the konselor-facing schedule view. Mirrors
// templates/students/_styles.php's list-table pattern so the look stays
// consistent app-wide.
return <<<'CSS'
.schedule-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.schedule-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.schedule-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.schedule-count {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 999px;
    background: #eff6ff;
    color: var(--primary-dark);
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
}

.schedule-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.schedule-table-scroll {
    overflow-x: auto;
}

.schedule-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.schedule-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.schedule-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.schedule-table thead th:first-child,
.schedule-table tbody td:first-child {
    padding-left: 24px;
}

.schedule-table thead th:last-child,
.schedule-table tbody td:last-child {
    padding-right: 24px;
}

.schedule-table tbody tr:last-child td {
    border-bottom: none;
}

.schedule-table tbody tr:hover {
    background: #f9fafb;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-pill-active { background: #ecfdf5; color: #047857; }
.status-pill-inactive { background: #f3f4f6; color: #6b7280; }

.btn-schedule {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--text);
    cursor: pointer;
    text-decoration: none;
    transition: background-color .15s ease, border-color .15s ease;
}

.btn-schedule:hover {
    background: #f3f4f6;
}

.schedule-empty {
    text-align: center;
    padding: 56px 20px;
}

.schedule-empty .schedule-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.schedule-empty p {
    margin: 4px 0 0;
    color: var(--muted);
}
CSS;
