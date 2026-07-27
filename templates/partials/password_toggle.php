<?php
/**
 * Show/hide toggle for every <input type="password"> on the page — drop in with a
 * single `require` (see auth/login.php, auth/register.php, auth/reset_password.php,
 * admin/counselors/create.php, admin/counselors/edit.php). No markup changes needed:
 * each password field is auto-wrapped and gets a toggle button on DOMContentLoaded.
 */
?>
<style>
    .pwd-toggle-wrap {
        position: relative;
        display: block;
    }

    .pwd-toggle-wrap input[type="password"],
    .pwd-toggle-wrap input[type="text"] {
        padding-right: 42px !important;
    }

    .pwd-toggle-btn {
        position: absolute;
        top: 50%;
        right: 6px;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        padding: 4px 8px;
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        opacity: 0.65;
    }

    .pwd-toggle-btn:hover {
        opacity: 1;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="password"]').forEach(function(input) {
            if (input.closest('.pwd-toggle-wrap')) {
                return;
            }

            var wrap = document.createElement('div');
            wrap.className = 'pwd-toggle-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pwd-toggle-btn';
            btn.setAttribute('aria-label', 'Tampilkan password');
            btn.textContent = '👁';
            wrap.appendChild(btn);

            btn.addEventListener('click', function() {
                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                btn.textContent = showing ? '👁' : '🙈';
                btn.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
            });
        });
    });
</script>
