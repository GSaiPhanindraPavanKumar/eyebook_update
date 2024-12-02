<?php
include 'sidebar.php';
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <button id="fullscreen-btn" class="btn btn-primary mb-3">Toggle Full Screen</button>
                        <iframe id="pdf-iframe" src="<?php echo htmlspecialchars($index_path, ENT_QUOTES, 'UTF-8'); ?>#toolbar=0" style="width: 100%; height: 80vh;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Include Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

<!-- Custom CSS for landscape mode on mobile -->
<style>
    @media (max-width: 768px) {
        #pdf-iframe {
            width: 100vw;
            height: 100vh;
        }
    }
</style>

<!-- JavaScript for Full Screen -->
<script>
document.getElementById('fullscreen-btn').addEventListener('click', function() {
    var iframe = document.getElementById('pdf-iframe');
    if (iframe.requestFullscreen) {
        iframe.requestFullscreen();
    } else if (iframe.mozRequestFullScreen) { // Firefox
        iframe.mozRequestFullScreen();
    } else if (iframe.webkitRequestFullscreen) { // Chrome, Safari and Opera
        iframe.webkitRequestFullscreen();
    } else if (iframe.msRequestFullscreen) { // IE/Edge
        iframe.msRequestFullscreen();
    }
});
</script>