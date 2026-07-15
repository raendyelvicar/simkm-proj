<?php
// Shared, scoped styles for the article feature. Reuses the color variables
// (--primary, --border, --text, --muted, ...) already defined in the
// enclosing layout so the accent stays consistent instead of introducing a
// second color system just for articles.
return <<<'CSS'
.article-page .page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.article-page .page-head h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    color: var(--text);
}

.article-page .page-head p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 0.9rem;
}

.btn-article {
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

.btn-article-primary {
    background: var(--primary);
    color: #fff;
}

.btn-article-primary:hover {
    background: var(--primary-dark);
    color: #fff;
}

.btn-article-ghost {
    background: transparent;
    border-color: var(--border);
    color: var(--text);
}

.btn-article-ghost:hover {
    background: #f3f4f6;
    color: var(--text);
}

.btn-article-danger {
    background: transparent;
    border-color: var(--border);
    color: #b91c1c;
}

.btn-article-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.btn-article-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.article-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}

.article-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.article-card-thumb {
    display: block;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background: #f3f4f6;
}

.article-card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tag-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.tag-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 999px;
    background: #f3f4f6;
    color: var(--muted);
    font-size: 0.72rem;
    font-weight: 600;
}

.article-card-body {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}

.article-card h2 {
    font-size: 1.05rem;
    margin: 0;
}

.article-card h2 a {
    color: var(--text);
    text-decoration: none;
}

.article-card h2 a:hover {
    color: var(--primary);
}

.article-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 0.78rem;
    color: var(--muted);
}

.category-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(37, 99, 235, 0.08);
    color: var(--primary-dark);
    font-weight: 600;
    font-size: 0.72rem;
}

.article-snippet {
    color: var(--muted);
    font-size: 0.88rem;
    line-height: 1.6;
    flex: 1;
}

.article-actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-top: auto;
}

.article-empty {
    text-align: center;
    padding: 56px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
}

.article-empty .article-empty-icon {
    font-size: 2.2rem;
    margin-bottom: 10px;
}

.article-empty p {
    margin: 4px 0 18px;
    color: var(--muted);
}

.article-detail {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: 32px;
}

.article-detail h1 {
    font-size: 1.6rem;
    margin: 10px 0 8px;
    color: var(--text);
}

.article-detail-body {
    margin-top: 20px;
    line-height: 1.8;
    color: var(--text);
    font-size: 0.96rem;
    white-space: pre-line;
}

.article-detail .tag-list {
    margin-top: 12px;
}

.article-detail-image {
    margin-top: 20px;
    border-radius: 12px;
    overflow: hidden;
}

.article-detail-image img {
    width: 100%;
    max-height: 420px;
    object-fit: cover;
    display: block;
}

.article-alert {
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.85rem;
    margin-bottom: 18px;
}

.article-alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.article-form .field {
    margin-bottom: 20px;
}

.article-form label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 6px;
}

.article-form input[type="text"],
.article-form textarea {
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

.article-form input[type="text"]:focus,
.article-form textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.article-form textarea {
    resize: vertical;
    min-height: 220px;
    line-height: 1.6;
}

.article-form input[type="file"] {
    display: block;
    width: 100%;
    font-size: 0.85rem;
    color: var(--text);
}

.article-form .field-hint {
    margin: 6px 0 0;
    font-size: 0.78rem;
    color: var(--muted);
}

.article-form .current-image-preview {
    margin-bottom: 10px;
    border-radius: 10px;
    overflow: hidden;
    max-width: 240px;
}

.article-form .current-image-preview img {
    width: 100%;
    display: block;
}

.article-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
}
CSS;
