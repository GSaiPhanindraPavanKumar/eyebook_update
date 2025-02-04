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
                        <div id="content-container">
                            <!-- Content will be loaded here -->
                            <div class="text-center" id="loading">
                                <div class="spinner-border" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Error Modal -->
                        <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="errorModalLabel">Content Not Available</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p id="error-message"></p>
                                        <hr>
                                        <p>Please raise a ticket to report this issue.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <a href="/spoc/tickets" class="btn btn-primary">Raise Ticket</a>
                                    </div>
                                </div>
                            </div>
                        </div>
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

<!-- Include Bootstrap CSS and JS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<!-- Custom CSS for landscape mode on mobile -->
<style>
    @media (max-width: 768px) {
        #book-iframe {
            width: 100vw;
            height: 100vh;
        }
    }
    #loading {
        padding: 20px;
    }
</style>

<script>
$(document).ready(function() {
    const indexPath = <?php echo json_encode($index_path); ?>;
    
    // Function to check content and load iframe
    function checkAndLoadContent() {
        $.ajax({
            url: indexPath,
            type: 'HEAD',
            success: function() {
                // Content exists, load the iframe
                $('#content-container').html(`
                    <iframe id="book-iframe" src="${indexPath}" style="width: 100%; height: 80vh;" frameborder="0"></iframe>
                `);
            },
            error: function(xhr) {
                // Check if response contains NoSuchKey
                if (xhr.responseText && xhr.responseText.includes('NoSuchKey')) {
                    $('#error-message').text('The course content appears to have been deleted from the server. Please raise a ticket to escalate this issue to higher officials.');
                } else {
                    $('#error-message').text('Unable to access the course content. Please raise a ticket to report this issue.');
                }
                $('#errorModal').modal('show');
                $('#content-container').html(`
                    <div class="text-center">
                        <h4>Content Not Available</h4>
                        <p>Please click the button below to report this issue.</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#errorModal">
                            Report Issue
                        </button>
                    </div>
                `);
            }
        });
    }

    // Start the content check
    checkAndLoadContent();
});
</script>