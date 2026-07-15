// Toggle sidebar (opsional)
document.addEventListener("DOMContentLoaded", function() {
    console.log("Dashboard Bootstrap Loaded");

    const btn = document.getElementById("toggleSidebar");

    if (btn) {
        btn.addEventListener("click", function() {
            document.querySelector(".sidebar").classList.toggle("d-none");
        });
    }
});