<?php
// Shared, scoped styles for the full Notifikasi history page and the bell
// dropdown rendered in templates/layouts/index.php.
return <<<'CSS'
.notif-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 4px;
    color: var(--text);
}

.notif-page .page-head p {
    margin: 0 0 20px;
    color: var(--muted);
    font-size: 0.88rem;
}

.notif-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.notif-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border);
    color: inherit;
    text-decoration: none;
}

.notif-row:last-child {
    border-bottom: none;
}

.notif-row:hover {
    background: #f8fafc;
}

.notif-row.notif-unread {
    background: #eff6ff;
}

.notif-icon {
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.notif-body {
    flex: 1;
    min-width: 0;
}

.notif-title {
    font-weight: 600;
    font-size: 0.92rem;
    color: var(--text);
    margin-bottom: 2px;
}

.notif-row.notif-unread .notif-title::before {
    content: '';
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--primary);
    margin-right: 6px;
}

.notif-message {
    font-size: 0.85rem;
    color: var(--muted);
}

.notif-time {
    font-size: 0.75rem;
    color: var(--muted);
    white-space: nowrap;
}

.notif-empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--muted);
}

.notif-empty-icon {
    font-size: 2rem;
    margin-bottom: 8px;
}
CSS;
