<?php
// Shared, scoped styles for the student roster page. Mirrors the admin
// counselor-management patterns (templates/admin/counselors/_styles.php) so
// the look stays consistent app-wide.
return <<<'CSS'
.student-admin-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.student-admin-page .page-head h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.student-admin-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.88rem;
}

.student-admin-count {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 999px;
    background: #eff6ff;
    color: var(--primary-dark);
    font-size: 0.85rem;
    font-weight: 600;
}

.student-admin-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}

.student-admin-table-scroll {
    overflow-x: auto;
}

.student-admin-table {
    width: 100%;
    border-collapse: collapse;
    white-space: nowrap;
}

.student-admin-table thead th {
    text-align: left;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted);
    font-weight: 600;
    padding: 16px 12px 10px;
    border-bottom: 1px solid var(--border);
}

.student-admin-table tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    font-size: 0.88rem;
    color: var(--text);
}

.student-admin-table thead th:first-child,
.student-admin-table tbody td:first-child {
    padding-left: 24px;
}

.student-admin-table thead th:last-child,
.student-admin-table tbody td:last-child {
    padding-right: 24px;
}

.student-admin-table tbody tr:last-child td {
    border-bottom: none;
}

.student-admin-table tbody tr:hover {
    background: #f9fafb;
}

.student-admin-name {
    display: flex;
    align-items: center;
    gap: 10px;
}

.student-admin-avatar {
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

.student-admin-avatar img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-admin-sub {
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

.status-pill-active { background: #ecfdf5; color: #047857; }
.status-pill-inactive { background: #f3f4f6; color: #6b7280; }
.status-pill-incomplete { background: #fffbeb; color: #b45309; }
.status-pill-rejected { background: #fef2f2; color: #b91c1c; }

.student-admin-empty {
    text-align: center;
    padding: 56px 20px;
}

.student-admin-empty .student-admin-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.student-admin-empty p {
    margin: 4px 0 0;
    color: var(--muted);
}
CSS;
