<?php
$roleFooter = $_SESSION['role'] ?? '';
?>

<footer class="main-footer text-center py-3 <?= $roleFooter == 'mahasiswa' ? 'footer-mahasiswa' : '' ?>">
    <strong>&copy; <?= date('Y') ?> SIMKM</strong>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const toggleBtn = document.getElementById("toggleSidebar");
        const sidebar = document.getElementById("sidebar");
        const content = document.querySelector(".content-wrapper");

        toggleBtn.addEventListener("click", function() {

            sidebar.classList.toggle("collapsed");

            if (sidebar.classList.contains("collapsed")) {
                content.style.marginLeft = "70px";
            } else {
                content.style.marginLeft = "250px";
            }

        });

    });
</script>

</body>

</html>