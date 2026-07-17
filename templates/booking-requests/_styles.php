<?php
// Shared, scoped styles for the konselor's booking queue. Mirrors
// templates/admin/approvals/_styles.php's list-table-with-row-actions
// pattern so the look stays consistent app-wide.
return <<<'CSS'
.booking-queue-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.booking-queue-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.booking-queue-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.booking-queue-count {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 999px;
    background: #f3f4f6;
    color: var(--text);
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
}

.booking-queue-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.booking-queue-table-scroll {
    overflow-x: auto;
}

.booking-queue-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.booking-queue-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.booking-queue-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
    white-space: normal;
}

.booking-queue-table thead th:first-child,
.booking-queue-table tbody td:first-child {
    padding-left: 24px;
}

.booking-queue-table thead th:last-child,
.booking-queue-table tbody td:last-child {
    padding-right: 24px;
}

.booking-queue-table tbody tr:last-child td {
    border-bottom: none;
}

.booking-queue-table tbody tr:hover {
    background: #f9fafb;
}

.booking-queue-sub {
    color: var(--muted);
    font-size: 0.8rem;
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

.status-pill-pending { background: #fffbeb; color: #b45309; }
.status-pill-progress { background: #eff6ff; color: #1d4ed8; }
.status-pill-completed { background: #ecfdf5; color: #047857; }

.booking-queue-actions {
    display: flex;
    gap: 6px;
    align-items: center;
    flex-wrap: wrap;
}

.btn-booking-queue {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    text-decoration: none;
    transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    line-height: 1.2;
}

.btn-booking-queue-success {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}

.btn-booking-queue-success:hover {
    background: #d1fae5;
}

.btn-booking-queue-primary {
    background: var(--primary);
    color: #fff;
}

.btn-booking-queue-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-booking-queue-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-booking-queue-ghost:hover {
    background: #f3f4f6;
}

.btn-booking-queue-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-booking-queue-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.booking-queue-input-sm {
    width: 62px;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 0.8rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
}

.booking-queue-input-sm:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.booking-queue-inline-form {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.booking-queue-empty {
    text-align: center;
    padding: 56px 20px;
}

.booking-queue-empty .booking-queue-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.booking-queue-empty p {
    margin: 4px 0 0;
    color: var(--muted);
}
CSS;
