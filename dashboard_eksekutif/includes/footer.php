</main> <!-- Penutup <main> -->
  </div> <!-- Penutup <div class="row"> -->
</div> <!-- Penutup <div class="container-fluid"> -->

<!-- 
  Library JavaScript
-->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>

<!-- ===== GLOBAL AJAX LOADING HOOKS ===== -->
<script>
$(document).ready(function() {
    // Aktifkan overlay saat AJAX mulai
    $(document).ajaxStart(function() {
        $('#globalLoadingOverlay').css('display', 'flex');
    });
    // Sembunyikan overlay saat SEMUA AJAX selesai (sukses/error)
    $(document).ajaxStop(function() {
        $('#globalLoadingOverlay').fadeOut(200);
    });

    // DataTables: pesan empty-state untuk halaman tanpa auto-load
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            emptyTable: '<span class="text-muted"><i class="fas fa-info-circle me-1 text-info"></i>Data tidak dimuat otomatis untuk menjaga performa aplikasi. Atur filter lalu klik tombol untuk menampilkan data.</span>'
        }
    });
});

// Helper manual jika ada kasus di luar jQuery AJAX
window.showGlobalLoading = function() { $('#globalLoadingOverlay').css('display', 'flex'); };
window.hideGlobalLoading = function() { $('#globalLoadingOverlay').fadeOut(200); };
</script>
<!-- ===== END GLOBAL AJAX LOADING ===== -->
<?php
if (isset($page_js)) {
    echo $page_js;
}
?>

</body>
</html>