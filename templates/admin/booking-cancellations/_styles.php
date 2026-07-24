<?php
// Shared, scoped styles for the booking-cancellation approval queue. Mirrors the
// admin approvals / counselor-management patterns so the look stays consistent app-wide.
return <<<'CSS'
.bcancel-admin-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.bcancel-admin-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.bcancel-admin-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.bcancel-admin-count {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 999px;
    background: #fffbeb;
    color: #b45309;
    font-size: 0.85rem;
    font-weight: 600;
}

.bcancel-admin-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.bcancel-admin-table-scroll {
    overflow-x: auto;
}

.bcancel-admin-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.bcancel-admin-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.bcancel-admin-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
    white-space: normal;
}

.bcancel-admin-table thead th:first-child,
.bcancel-admin-table tbody td:first-child {
    padding-left: 24px;
}

.bcancel-admin-table thead th:last-child,
.bcancel-admin-table tbody td:last-child {
    padding-right: 24px;
}

.bcancel-admin-table tbody tr:last-child td {
    border-bottom: none;
}

.bcancel-admin-table tbody tr:hover {
    background: #f9fafb;
}

.bcancel-admin-sub {
    color: var(--muted);
    font-size: 0.8rem;
}

.bcancel-admin-reason {
    max-width: 260px;
    white-space: normal;
}

.bcancel-admin-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    white-space: nowrap;
}

.btn-bcancel-admin {
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

.btn-bcancel-admin-approve {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}

.btn-bcancel-admin-approve:hover {
    background: #d1fae5;
}

.btn-bcancel-admin-reject {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-bcancel-admin-reject:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.bcancel-admin-empty {
    text-align: center;
    padding: 56px 20px;
}

.bcancel-admin-empty .bcancel-admin-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.bcancel-admin-empty p {
    margin: 4px 0 0;
    color: var(--muted);
}
CSS;
