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
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= $generation['progress'] ?>%" 
                                 aria-valuenow="<?= $generation['progress'] ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?= round($generation['progress']) ?>%
                            </div>
                        </div>
                        <p>Generated: <?= $generation['generated_count'] ?> / <?= $generation['total_count'] ?></p>
                        
                        <div id="status-message" class="alert alert-info">
                            <div id="status-text">Processing certificates... Please wait.</div>
                            <div id="error-details" class="mt-2 small" style="display: none;"></div>
                        </div>

                        <!-- Add debug information section -->
                        <?php if (getenv('APP_ENV') !== 'production'): ?>
                        <div id="debug-info" class="mt-4 p-3 bg-light">
                            <h5>Debug Information</h5>
                            <pre id="debug-log" style="max-height: 200px; overflow-y: auto;"></pre>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// Add logging function
function log(message) {
    console.log(`[Certificate Progress] ${message}`);
    <?php if (getenv('APP_ENV') !== 'production'): ?>
    const debugLog = document.getElementById('debug-log');
    const timestamp = new Date().toISOString();
    debugLog.innerHTML += `${timestamp} - ${message}\n`;
    debugLog.scrollTop = debugLog.scrollHeight;
    <?php endif; ?>
}

let failedAttempts = 0;
const MAX_RETRIES = 5;

function checkProgress() {
    log('Checking progress...');
    
    fetch('/admin/certificate_generations/check-progress/<?= $generationId ?>', {
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
            }
            return response.json();
        })
        .then(data => {
            log(`Status update: ${JSON.stringify(data)}`);
            
            // Update progress bar
            const progress = Math.round(data.progress || 0);
            $('.progress-bar')
                .css('width', progress + '%')
                .text(progress + '%')
                .attr('aria-valuenow', progress);
            
            // Update status message
            $('#status-text').text(
                `Processing: ${data.generated_count || 0} of ${data.total_count || 0} certificates (${progress}%)`
            );
            
            if (data.status === 'completed') {
                $('#status-message')
                    .removeClass('alert-info')
                    .addClass('alert-success');
                $('#status-text').text('Certificate generation completed!');
                setTimeout(() => {
                    window.location.href = '/admin/certificate_generations';
                }, 2000);
            } else if (data.status === 'failed') {
                $('#status-message')
                    .removeClass('alert-info')
                    .addClass('alert-danger');
                $('#status-text').text('Certificate generation failed');
            } else {
                setTimeout(checkProgress, 2000);
            }
        })
        .catch(error => {
            log('Error checking progress: ' + error.message);
            $('#error-details')
                .text(error.message)
                .show();
            setTimeout(checkProgress, 5000);
        });
}

// Add debug endpoint check in non-production
<?php if (getenv('APP_ENV') !== 'production'): ?>
async function checkDebugInfo() {
    try {
        log('Fetching debug information...');
        const response = await fetch('/admin/certificate_generations/debug/<?= $generationId ?>');
        const debugData = await response.json();
        log(`Debug data: ${JSON.stringify(debugData, null, 2)}`);
    } catch (error) {
        log(`Error fetching debug info: ${error.message}`);
    }
}
<?php endif; ?>

// Start the generation process immediately when the page loads
$(document).ready(function() {
    startGeneration();
    setTimeout(checkProgress, 2000);
});

async function startGeneration() {
    try {
        $('#status-text').text('Starting certificate generation...');
        
        const response = await fetch('/admin/certificate_generations/start/<?= $generationId ?>', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const text = await response.text();
        log('Raw response: ' + text);
        
        // Try to extract only the JSON part if there's additional output
        const jsonMatch = text.match(/\{.*\}/);
        if (!jsonMatch) {
            throw new Error('No JSON found in response');
        }
        
        const data = JSON.parse(jsonMatch[0]);
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to start generation');
        }
        
        $('#status-text').text('Generation process started successfully');
        log('Generation process started');
        
    } catch (error) {
        log('Error starting generation: ' + error.message);
        $('#status-message')
            .removeClass('alert-info')
            .addClass('alert-danger');
        $('#status-text').text('Error starting generation');
        $('#error-details')
            .text(error.message)
            .show();
    }
}
</script> 