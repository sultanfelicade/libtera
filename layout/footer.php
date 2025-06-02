</main> <footer class="bg-light text-dark mt-auto shadow-sm">
  <div class="container-fluid px-4 py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
      
      <div class="mb-3 mb-md-0 d-flex align-items-center gap-2">
        <img src="/libtera/assets/logoFooter.png" alt="Logo" width="40">
        <span class="fw-bold">LibTera</span>
      </div>

      <div class="mb-3 mb-md-0">
        <ul class="nav justify-content-center">
          <li class="nav-item"><a class="nav-link px-2 text-dark" href="/libtera/index.php"><i class="fa-solid fa-circle-question"></i> FAQ</a></li>
        </ul>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- untuk html notif -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
  $(document).ready(function() {
    $('#templateEmailAdmin').summernote({
      height: 300, // Atur tinggi editor sesuai kebutuhan
      toolbar: [
        ['style', ['style']],
        ['font', ['bold', 'underline', 'clear']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['insert', ['link']],
        ['view', ['fullscreen', 'codeview', 'help']],
      ],
    });
  });
</script>
<script src="/libtera/layout/notifikasi.js"></script>

</body>
</html>