<?php
// Shared, scoped styles for the pending-account approval queue. Mirrors the
// admin counselor-management / student-roster patterns so the look stays
// consistent app-wide.
return <<<'CSS'
.approval-admin-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.approval-admin-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.approval-admin-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.approval-admin-count {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 999px;
    background: #fffbeb;
    color: #b45309;
    font-size: 0.85rem;
    font-weight: 600;
}

.approval-admin-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.approval-admin-table-scroll {
    overflow-x: auto;
}

.approval-admin-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.approval-admin-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.approval-admin-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.approval-admin-table thead th:first-child,
.approval-admin-table tbody td:first-child {
    padding-left: 24px;
}

.approval-admin-table thead th:last-child,
.approval-admin-table tbody td:last-child {
    padding-right: 24px;
}

.approval-admin-table tbody tr:last-child td {
    border-bottom: none;
}

.approval-admin-table tbody tr:hover {
    background: #f9fafb;
}

.approval-admin-name {
    display: flex;
    align-items: center;
    gap: 10px;
}

.approval-admin-avatar {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    background: #d97706;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
}

.approval-admin-avatar img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.approval-admin-sub {
    color: var(--muted);
    font-size: 0.8rem;
}

.approval-admin-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-approval-admin {
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

.btn-approval-admin-approve {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}

.btn-approval-admin-approve:hover {
    background: #d1fae5;
}

.btn-approval-admin-reject {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-approval-admin-reject:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.approval-admin-empty {
    text-align: center;
    padding: 56px 20px;
}

.approval-admin-empty .approval-admin-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.approval-admin-empty p {
    margin: 4px 0 0;
    color: var(--muted);
}
CSS;
