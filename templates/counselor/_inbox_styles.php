<?php
// Extra styles for the counselor's incoming-consultation inbox. Layered on
// top of templates/counselor/_styles.php for the shared avatar/empty-state
// classes.
return <<<'CSS'
.thread-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.thread-row {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 18px;
    text-decoration: none;
    color: inherit;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.thread-row:hover {
    border-color: var(--primary);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}

.thread-row-body {
    flex: 1;
    min-width: 0;
}

.thread-row-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
}

.thread-row-head strong {
    color: var(--text);
    font-size: 0.95rem;
}

.thread-row-time {
    font-size: 0.75rem;
    color: var(--muted);
    white-space: nowrap;
}

.thread-row-snippet {
    margin: 2px 0 0;
    color: var(--muted);
    font-size: 0.85rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.thread-unread-badge {
    flex-shrink: 0;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 999px;
    background: var(--primary);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
CSS;
