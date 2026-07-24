<?php
// Shared, scoped styles for the student "Booking Saya" list. Mirrors the
// diary/tips/self-help-activities patterns so this page looks consistent
// with the rest of the app instead of relying on bare Bootstrap defaults.
return <<<'CSS'
.booking-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.booking-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.booking-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.booking-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.booking-table-scroll {
    overflow-x: auto;
}

.booking-table {
    width: 100%;
    border-collapse: collapse;
}

.booking-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.booking-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.booking-table thead th:first-child,
.booking-table tbody td:first-child {
    padding-left: 24px;
}

.booking-table thead th:last-child,
.booking-table tbody td:last-child {
    padding-right: 24px;
}

.booking-table tbody tr:last-child td {
    border-bottom: none;
}

.booking-table tbody tr:hover {
    background: #f9fafb;
}

.booking-counselor {
    font-weight: 600;
}

.booking-date {
    white-space: nowrap;
    font-weight: 500;
}

.booking-time {
    white-space: nowrap;
    color: var(--muted);
}

.booking-monitoring-note {
    margin-top: 4px;
    font-size: 0.78rem;
    color: var(--muted);
}

.booking-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.8rem;
    white-space: nowrap;
}

.booking-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.booking-badge-yellow { background: #fef9c3; color: #a16207; }
.booking-badge-green  { background: #dcfce7; color: #15803d; }
.booking-badge-blue   { background: #dbeafe; color: #1d4ed8; }
.booking-badge-gray   { background: #f1f5f9; color: #475569; }
.booking-badge-red    { background: #fee2e2; color: #b91c1c; }
.booking-badge-orange { background: #ffedd5; color: #c2410c; }

.booking-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-booking {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    line-height: 1.2;
}

.btn-booking-primary {
    background: var(--primary);
    color: #fff;
}

.btn-booking-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-booking-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-booking-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.btn-booking-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-booking-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.btn-booking-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.booking-empty {
    text-align: center;
    padding: 56px 20px;
}

.booking-empty .booking-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.booking-empty p {
    margin: 4px 0 18px;
    color: var(--muted);
}
CSS;
