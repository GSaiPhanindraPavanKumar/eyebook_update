<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Generating Certificates</h3>
                
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?= htmlspecialchars($generation['subject']) ?></h4>
                        <div class="progress mb-3">
                            <div id="progressBar" 
                                 class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span id="progressText">0%</span>
                            </div>
                        </div>
                        <div id="statusText" class="text-muted">
                            Processing certificates... Please wait.
                        </div>
                        
                        <div id="error-message" class="alert alert-danger mt-3" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.html"); ?>
</div>

<script>
function checkProgress() {
    fetch('/admin/certificate_generations/progress/<?= $generationId ?>', {
        headers: {
            'Accept': 'application/json'
        },
        // Add redirect handling
        redirect: 'follow'
    })
    .then(response => {
        // Check if response is a redirect
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Parse JSON response
        return response.json().catch(e => {
            throw new Error('Invalid JSON response from server');
        });
    })
    .then(data => {
        if (!data) return; // Skip if redirected or no data
        
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const statusText = document.getElementById('statusText');
        
        if (data.progress !== undefined) {
            const progress = Math.round(data.progress);
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressText.textContent = progress + '%';
            
            if (data.generated_count && data.total_count) {
                statusText.textContent = `Processing: ${data.generated_count} of ${data.total_count} certificates`;
            }
        }
        
        if (data.status === 'completed') {
            clearInterval(progressInterval);
            statusText.textContent = 'Generation completed!';
            
            // Add download button
            const downloadBtn = document.createElement('a');
            downloadBtn.href = `/admin/certificate_generations/download/${<?= $generationId ?>}`;
            downloadBtn.className = 'btn btn-primary mt-3';
            downloadBtn.innerHTML = '<i class="ti-download mr-1"></i> Download Certificates';
            document.querySelector('.card-body').appendChild(downloadBtn);
            
            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = '/admin/certificate_generations';
            }, 3000);
            
        } else if (data.status === 'failed') {
            clearInterval(progressInterval);
            statusText.textContent = 'Generation failed.';
            progressBar.classList.remove('bg-primary');
            progressBar.classList.add('bg-danger');
            
            if (data.error) {
                const errorDiv = document.getElementById('error-message');
                errorDiv.textContent = data.error;
                errorDiv.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Error checking progress:', error);
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Error checking progress: ' + error.message;
        errorDiv.style.display = 'block';
        
        // Stop checking if we get too many errors
        if (error.message.includes('Invalid JSON')) {
            clearInterval(progressInterval);
        }
    });
}

// Check progress every 2 seconds
const progressInterval = setInterval(checkProgress, 2000);

// Initial check
checkProgress();

// Start the generation process
fetch('/admin/certificate_generations/start/<?= $generationId ?>', {
    method: 'POST',
    headers: {
        'Accept': 'application/json'
    },
    redirect: 'follow'
})
.then(response => {
    // Handle redirect
    if (response.redirected) {
        window.location.href = response.url;
        return;
    }
    return response.json();
})
.then(data => {
    if (!data) return; // Skip if redirected
    if (!data.success) {
        throw new Error(data.error || 'Failed to start generation');
    }
})
.catch(error => {
    console.error('Error starting generation:', error);
    const errorDiv = document.getElementById('error-message');
    errorDiv.textContent = 'Error starting generation: ' + error.message;
    errorDiv.style.display = 'block';
});
</script> 