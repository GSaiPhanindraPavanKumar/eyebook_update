<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Generating Certificates</h3>
                
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"><?= htmlspecialchars($generation['subject']) ?></h4>
                        <?php if ($generation['status'] === 'completed'): ?>
                            <div class="alert alert-success">
                                <h5>Certificates have already been generated!</h5>
                                <p>Redirecting to home page where you can download them...</p>
                            </div>
                            <script>
                                setTimeout(() => {
                                    window.location.href = '/admin/certificate_generations';
                                }, 2000);
                            </script>
                        <?php else: ?>
                            <style>
                                .progress {
                                    height: 25px;
                                    border-radius: 12px;
                                    background-color: #f0f0f0;
                                    margin: 20px 0;
                                    box-shadow: inset 0 1px 3px rgba(0,0,0,.1);
                                }
                                
                                .progress-bar {
                                    transition: width 2s cubic-bezier(0.4, 0, 0.2, 1);
                                    font-size: 14px;
                                    font-weight: 600;
                                    line-height: 25px;
                                    border-radius: 12px;
                                    box-shadow: 0 2px 4px rgba(0,0,0,.1);
                                }

                                .progress-bar.bg-success {
                                    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
                                }

                                #statusText {
                                    font-size: 16px;
                                    margin-top: 15px;
                                    transition: opacity 0.5s ease-in-out;
                                }

                                .alert {
                                    transition: all 0.5s ease-in-out;
                                }
                            </style>

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
                            <div id="statusText" class="text-muted text-center">
                                Processing certificates... Please wait.
                            </div>
                            
                            <div id="error-message" class="alert alert-danger mt-3" style="display: none;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.html"); ?>
</div>

<script>
let lastProcessedCount = 0;
let isCompleted = false;

function checkProgress() {
    if (isCompleted) return;

    fetch('/admin/certificate_generations/progress/<?= $generationId ?>', {
        headers: {
            'Accept': 'application/json'
        },
        redirect: 'manual' // Prevent automatic redirects
    })
    .then(response => {
        // Check if generation is completed (redirect status)
        if (response.type === 'opaqueredirect' || response.status === 302) {
            isCompleted = true;
            handleCompletion();
            return;
        }
        
        return response.text().then(text => {
            try {
                // Try to find valid JSON in the response
                const jsonMatch = text.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    return JSON.parse(jsonMatch[0]);
                }
                // If we see certificate processing messages, consider it as in-progress
                if (text.includes('[Certificate Processing]')) {
                    const progressMatch = text.match(/Progress: (\d+)\/(\d+)/);
                    if (progressMatch) {
                        const [, current, total] = progressMatch;
                        return {
                            status: 'processing',
                            progress: (current / total) * 100,
                            generated_count: parseInt(current),
                            total_count: parseInt(total)
                        };
                    }
                }
                return null;
            } catch (e) {
                console.error('Parse error:', e, 'Response:', text);
                return null;
            }
        });
    })
    .then(data => {
        if (!data) return;
        
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const statusText = document.getElementById('statusText');
        const errorDiv = document.getElementById('error-message');
        
        errorDiv.style.display = 'none';
        
        if (data.progress !== undefined) {
            const progress = Math.round(data.progress);
            
            // Smooth transition for progress bar
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressText.textContent = progress + '%';
            
            if (data.generated_count !== undefined && data.total_count !== undefined) {
                if (data.generated_count > lastProcessedCount) {
                    statusText.textContent = `Processing: ${data.generated_count} of ${data.total_count} certificates`;
                    lastProcessedCount = data.generated_count;
                }
            }
        }
        
        if (data.status === 'completed' || (data.generated_count && data.generated_count >= data.total_count)) {
            handleCompletion();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Only show error if we're not completed
        if (!isCompleted) {
            handleError(error.message || 'An error occurred while checking progress');
        }
    });
}

function handleCompletion() {
    isCompleted = true;
    clearInterval(progressInterval);
    
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const statusText = document.getElementById('statusText');
    
    // Smoother final success animation
    progressBar.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
    progressBar.classList.remove('progress-bar-animated');
    progressBar.classList.add('bg-success');
    progressBar.style.width = '100%';
    progressText.textContent = '100%';
    
    // Fade out current status
    statusText.style.opacity = '0';
    
    // Smoother status text transition
    setTimeout(() => {
        statusText.textContent = 'Certificates successfully generated!';
        statusText.style.opacity = '1';
        
        // Show message about redirection with fade in
        const redirectMsg = document.createElement('div');
        redirectMsg.className = 'alert alert-success mt-3';
        redirectMsg.style.opacity = '0';
        redirectMsg.textContent = 'Redirecting to home page...';
        document.querySelector('.card-body').appendChild(redirectMsg);
        
        // Trigger fade in
        setTimeout(() => {
            redirectMsg.style.opacity = '1';
        }, 50);
        
        // Redirect after delay
        setTimeout(() => {
            window.location.href = '/admin/certificate_generations';
        }, 2500);
    }, 800);
}

function handleError(message) {
    isCompleted = true;
    clearInterval(progressInterval);
    
    const statusText = document.getElementById('statusText');
    const progressBar = document.getElementById('progressBar');
    const errorDiv = document.getElementById('error-message');
    
    statusText.textContent = 'Generation failed.';
    progressBar.classList.remove('bg-primary', 'progress-bar-animated');
    progressBar.classList.add('bg-danger');
    
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    
    // Add retry button
    if (!document.querySelector('.retry-btn')) {
        const retryBtn = document.createElement('button');
        retryBtn.className = 'btn btn-warning mt-3 retry-btn';
        retryBtn.innerHTML = '<i class="ti-reload mr-1"></i> Retry Generation';
        retryBtn.onclick = () => window.location.reload();
        document.querySelector('.card-body').appendChild(retryBtn);
    }
}

// Update check interval to be slightly longer for smoother transitions
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
    if (response.redirected) {
        window.location.href = response.url;
        return;
    }
    return response.text().then(text => {
        try {
            const jsonMatch = text.match(/\{.*\}/);
            return jsonMatch ? JSON.parse(jsonMatch[0]) : null;
        } catch (e) {
            return null;
        }
    });
})
.then(data => {
    if (!data) return;
    if (!data.success) {
        throw new Error(data.error || 'Failed to start generation');
    }
})
.catch(error => {
    handleError('Error starting generation: ' + error.message);
});
</script> 