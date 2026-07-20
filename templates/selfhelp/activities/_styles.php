<?php
// Shared, scoped styles for the Activity Planner sub-feature. Mirrors the
// diary/tips patterns (templates/diary/_styles.php, templates/tips/_styles.php)
// so this list+create flow looks consistent with the rest of the app instead
// of the Bootstrap-card look used by the other Self Help pages.
return <<<'CSS'
.activity-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.activity-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.activity-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.activity-page .page-head-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.activity-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.activity-table-scroll {
    overflow-x: auto;
}

.activity-table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.activity-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.activity-table thead th:first-child,
.activity-table tbody td:first-child {
    padding-left: 24px;
}

.activity-table thead th:last-child,
.activity-table tbody td:last-child {
    padding-right: 24px;
}

.activity-table tbody tr:last-child td {
    border-bottom: none;
}

.activity-table tbody tr:hover {
    background: #f9fafb;
}

.activity-title {
    font-weight: 600;
}

.activity-desc {
    color: var(--muted);
    font-size: 0.82rem;
    margin-top: 2px;
}

.activity-date {
    white-space: nowrap;
    font-weight: 500;
}

.activity-mood {
    white-space: nowrap;
    color: var(--muted);
}

.activity-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.8rem;
    white-space: nowrap;
}

.activity-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.activity-badge-yellow { background: #fef9c3; color: #a16207; }
.activity-badge-green  { background: #dcfce7; color: #15803d; }
.activity-badge-gray   { background: #f1f5f9; color: #475569; }

.activity-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
}

.activity-actions-row {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}

.activity-complete-form {
    display: flex;
    gap: 6px;
    align-items: center;
}

.activity-complete-form select {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 0.8rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
}

.btn-activity {
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

.btn-activity-primary {
    background: var(--primary);
    color: #fff;
}

.btn-activity-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-activity-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-activity-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.btn-activity-success {
    background: transparent;
    border-color: #bbf7d0;
    color: #15803d;
}

.btn-activity-success:hover {
    background: #ecfdf5;
}

.btn-activity-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-activity-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.btn-activity-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.activity-empty {
    text-align: center;
    padding: 56px 20px;
}

.activity-empty .activity-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.activity-empty p {
    margin: 4px 0 18px;
    color: var(--muted);
}

.activity-detail {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 32px;
    max-width: 640px;
}

.activity-alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.85rem;
    margin-bottom: 18px;
}

.activity-alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.activity-form .field {
    margin-bottom: 20px;
}

.activity-form label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

.activity-form .field-hint {
    margin: 0 0 8px;
    font-size: 0.8rem;
    color: var(--muted);
}

.activity-form input[type="text"],
.activity-form input[type="date"],
.activity-form select,
.activity-form textarea {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
    transition: border-color .15s ease, box-shadow .15s ease;
}

.activity-form input[type="text"]:focus,
.activity-form input[type="date"]:focus,
.activity-form select:focus,
.activity-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.activity-form textarea {
    resize: vertical;
    min-height: 100px;
    line-height: 1.6;
}

.activity-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}
CSS;
