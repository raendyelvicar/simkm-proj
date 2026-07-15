<?php
// Shared, scoped styles for the counselor feature. Reuses the color variables
// (--primary, --border, --text, --muted, ...) already defined in the
// enclosing layout so the accent stays consistent instead of introducing a
// second color system just for counselors.
return <<<'CSS'
.counselor-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.counselor-page .page-head h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.counselor-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.9rem;
}

.btn-counselor {
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

.btn-counselor-primary {
    background: var(--primary);
    color: #fff;
}

.btn-counselor-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-counselor-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-counselor-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.counselor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 20px;
}

.counselor-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.counselor-avatar {
    position: relative;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.counselor-avatar img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.counselor-avatar-initial {
    color: #fff;
    font-weight: 700;
    font-size: 1.3rem;
}

.counselor-avatar-lg {
    width: 88px;
    height: 88px;
}

.counselor-avatar-lg .counselor-avatar-initial {
    font-size: 1.8rem;
}

.counselor-avatar-sm {
    width: 44px;
    height: 44px;
}

.counselor-avatar-sm .counselor-avatar-initial {
    font-size: 1rem;
}

.counselor-card-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
}

.counselor-card-body h2 {
    font-size: 1.05rem;
    margin: 0;
    color: var(--text);
}

.category-pill {
    display: inline-flex;
    align-items: center;
    align-self: flex-start;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary-dark);
    font-weight: 600;
    font-size: 0.72rem;
}

.counselor-bio {
    color: var(--muted);
    font-size: 0.88rem;
    line-height: 1.6;
    flex: 1;
    margin: 0;
}

.counselor-meta {
    color: var(--muted);
    font-size: 0.82rem;
}

.counselor-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: auto;
}

.counselor-empty {
    text-align: center;
    padding: 56px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
}

.counselor-empty .counselor-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.counselor-empty p {
    margin: 4px 0 0;
    color: var(--muted);
}

.counselor-back {
    display: inline-block;
    margin-bottom: 16px;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.88rem;
}

.counselor-back:hover {
    color: var(--text);
}

.counselor-detail {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 32px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.counselor-detail-head {
    display: flex;
    align-items: center;
    gap: 18px;
}

.counselor-detail-head h1 {
    font-size: 1.4rem;
    margin: 0 0 8px;
    color: var(--text);
}

.counselor-detail-bio {
    color: var(--text);
    line-height: 1.8;
    font-size: 0.96rem;
    margin: 0;
}
CSS;
