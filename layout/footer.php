<!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const burgerBtn = document.getElementById('burgerBtn');

  function toggleSidebar() {
    if (sidebar.style.left === '0px') {
      sidebar.style.left = '-250px';
    } else {
      sidebar.style.left = '0px';
    }
  }

  function handleResize() {
    if (window.innerWidth >= 768) {
      sidebar.style.left = '0px';
    } else {
      sidebar.style.left = '-250px';
    }
  }

  if (burgerBtn && sidebar) {
    burgerBtn.addEventListener('click', toggleSidebar);
    window.addEventListener('resize', handleResize);
    window.addEventListener('DOMContentLoaded', handleResize);
  }
</script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");

    function toggleSidebar() {
      if (!sidebar) return;
      if (sidebar.style.left === "0px") {
        sidebar.style.left = "-250px";
      } else {
        sidebar.style.left = "0px";
      }
    }

    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener("click", function (e) {
        e.preventDefault();
        toggleSidebar();
      });
    }

    // Handle responsive sidebar state on resize
    window.addEventListener("resize", function () {
      if (window.innerWidth >= 768) {
        sidebar.style.left = "0px";
      } else {
        sidebar.style.left = "-250px";
      }
    });

    // Initial state
    if (window.innerWidth < 768) {
      sidebar.style.left = "-250px";
    }
  });
</script>

</body>
</html>
