<?php
// Shared, scoped styles for the chat/consultation feature. Reuses the color
// variables (--primary, --border, --text, --muted, ...) already defined in
// the enclosing layout, plus the .counselor-avatar-* classes from the
// counselor feature for the header avatar.
return <<<'CSS'
.chat-page {
    max-width: 760px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.chat-back {
    color: var(--muted);
    text-decoration: none;
    font-size: 0.88rem;
}

.chat-back:hover {
    color: var(--text);
}

.chat-head {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat-head-info {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 16px 20px;
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

.counselor-avatar-sm {
    width: 44px;
    height: 44px;
}

.counselor-avatar-sm .counselor-avatar-initial {
    font-size: 1rem;
}

.chat-head-info h1 {
    font-size: 1.1rem;
    margin: 0;
    color: var(--text);
}

.chat-subtitle {
    font-size: 0.82rem;
    color: var(--muted);
}

.chat-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    height: 480px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat-empty {
    margin: auto;
    color: var(--muted);
    font-size: 0.9rem;
    text-align: center;
}

.chat-bubble {
    max-width: 72%;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 0.9rem;
    line-height: 1.5;
}

.chat-bubble-theirs {
    align-self: flex-start;
    background: #f1f5f9;
    color: var(--text);
    border-bottom-left-radius: 4px;
}

.chat-bubble-mine {
    align-self: flex-end;
    background: var(--primary);
    color: #fff;
    border-bottom-right-radius: 4px;
}

.chat-bubble-text {
    white-space: pre-line;
    word-break: break-word;
}

.chat-bubble-time {
    margin-top: 4px;
    font-size: 0.7rem;
    opacity: 0.7;
}

.chat-form {
    display: flex;
    gap: 10px;
}

.chat-input {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 11px 14px;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text);
    background: #fff;
}

.chat-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
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
CSS;
