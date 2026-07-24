<?php
// Shared, scoped styles for the admin counselor-management feature. Mirrors
// the diary-table / article-form patterns used elsewhere so the look stays
// consistent app-wide.
return <<<'CSS'
.counselor-admin-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.counselor-admin-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.counselor-admin-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.counselor-admin-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.btn-counselor-admin {
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

.btn-counselor-admin-primary {
    background: var(--primary);
    color: #fff;
}

.btn-counselor-admin-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-counselor-admin-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-counselor-admin-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.btn-counselor-admin-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-counselor-admin-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.btn-counselor-admin-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.counselor-admin-table {
    width: 100%;
    border-collapse: collapse;
}

.counselor-admin-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.counselor-admin-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.counselor-admin-table thead th:first-child,
.counselor-admin-table tbody td:first-child {
    padding-left: 24px;
}

.counselor-admin-table thead th:last-child,
.counselor-admin-table tbody td:last-child {
    padding-right: 24px;
}

.counselor-admin-table tbody tr:last-child td {
    border-bottom: none;
}

.counselor-admin-table tbody tr:hover {
    background: #f9fafb;
}

.counselor-admin-name {
    display: flex;
    align-items: center;
    gap: 10px;
}

.counselor-admin-avatar {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
}

.counselor-admin-avatar img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
}

.status-pill-active { background: #ecfdf5; color: #047857; }
.status-pill-inactive { background: #f3f4f6; color: #6b7280; }
.status-pill-incomplete { background: #fffbeb; color: #b45309; }

.counselor-admin-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.counselor-admin-empty {
    text-align: center;
    padding: 56px 20px;
}

.counselor-admin-empty .counselor-admin-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.counselor-admin-empty p {
    margin: 4px 0 18px;
    color: var(--muted);
}

.counselor-admin-alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.85rem;
    margin-bottom: 18px;
}

.counselor-admin-alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.counselor-admin-detail {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 32px;
}

.counselor-admin-form .field {
    margin-bottom: 20px;
}

.counselor-admin-form .field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 640px) {
    .counselor-admin-form .field-row {
        grid-template-columns: 1fr;
    }
}

.counselor-admin-form label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

.counselor-admin-form input[type="text"],
.counselor-admin-form input[type="email"],
.counselor-admin-form input[type="password"],
.counselor-admin-form input[type="number"],
.counselor-admin-form select,
.counselor-admin-form textarea {
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

.counselor-admin-form input:focus,
.counselor-admin-form select:focus,
.counselor-admin-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.counselor-admin-form textarea {
    resize: vertical;
    min-height: 100px;
    line-height: 1.6;
}

.counselor-admin-form input[type="file"] {
    display: block;
    width: 100%;
    font-size: 0.85rem;
    color: var(--text);
}

.counselor-admin-form .field-hint {
    margin: 6px 0 0;
    font-size: 0.78rem;
    color: var(--muted);
}

.counselor-admin-check {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.counselor-admin-check input {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
}

.counselor-admin-check span {
    font-size: 0.85rem;
    color: var(--text);
}

.counselor-admin-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}
CSS;
