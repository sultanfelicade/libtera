</main> 
<footer class="bg-light text-dark mt-auto shadow-sm">
  <div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
      
      <div class="mb-3 mb-md-0 d-flex align-items-center gap-2">
        <img src="/libtera/assets/logoFooter.png" alt="Logo" width="40">
        <span class="fw-bold">LibTera</span>
      </div>

      <div class="mb-3 mb-md-0">
        <ul class="nav justify-content-center">
          <li class="nav-item"><a class="nav-link px-2 text-dark" href="/libtera/index.php"><i class="fa-solid fa-circle-question"></i> FAQ</a></li> </ul>
      </div>

      <div class="text-end small">
        <div><i class="fas fa-envelope me-1"></i> libtera@perpus.ac.id</div>
        <div><i class="fas fa-phone me-1"></i> +62 812-3456-7890</div>
      </div>

    </div>

    <div class="text-center mt-3 small text-secondary border-top pt-2">
      &copy; <?= date('Y') ?> LibTera. All rights reserved.
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.custom-vertical-navbar');
    if (!navbar) {
        // console.warn('Custom vertical navbar not found.');
        return;
    }

    const glider = navbar.querySelector('.glider-indicator');
    const navItems = navbar.querySelectorAll('.nav-item'); // Semua item menu
    let activeItem = navbar.querySelector('.nav-item.active'); // Item yang aktif

    if (!glider) {
        // console.warn('Glider indicator not found.');
        return;
    }

    function positionGlider(targetItem) {
        if (!targetItem) {
            glider.style.opacity = '0'; // Sembunyikan glider jika tidak ada target
            return;
        }

        const itemHeight = targetItem.offsetHeight;
        const itemTopRelativeToNavbar = targetItem.offsetTop;

        glider.style.height = `${itemHeight}px`;
        glider.style.transform = `translateY(${itemTopRelativeToNavbar}px)`;
        glider.style.opacity = '1';
    }

    if (activeItem) {
        setTimeout(() => {
            positionGlider(activeItem);
        }, 50); // Sedikit delay untuk memastikan rendering selesai
    } else {
        glider.style.opacity = '0'; // Sembunyikan jika tidak ada yang aktif
    }

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Penting: Jika navigasi SPA, pastikan 'activeItem' adalah referensi terbaru
            // Untuk navigasi tradisional (full reload), PHP akan set kelas 'active'
            // jadi kita bisa query ulang di sini untuk memastikan.
            activeItem = navbar.querySelector('.nav-item.active');
            if (activeItem) {
                positionGlider(activeItem);
            }
        }, 100);
    });
});
</script>
</body>
</html>