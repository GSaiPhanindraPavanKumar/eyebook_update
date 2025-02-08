<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Generate Certificates</h3>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <div id="preview-section" class="my-4" style="display: none;">
                    <h4>Certificate Preview</h4>
                    <div class="card">
                        <div class="card-body">
                            <img id="preview-image" class="img-fluid" alt="Certificate Preview">
                            <div class="mt-3">
                                <p><strong>SME:</strong> <span id="preview-reg"></span></p>
                                <p><strong>Name:</strong> <span id="preview-name"></span></p>
                                <p><strong>Project Title:</strong> <span id="preview-grade"></span></p>
                                <p><strong>Date Range:</strong> <span id="preview-date"></span></p>
                            </div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-secondary" onclick="cancelGeneration()">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="proceedGeneration()">Proceed with Generation</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form id="certificate-form" action="/admin/certificate_generations/store" method="POST" enctype="multipart/form-data" onsubmit="return showPreview(event)">
                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Date Range</label>
                        <input type="text" name="date_range" class="form-control" required 
                               placeholder="e.g., 28th May 2024 - 3rd June 2024">
                        <small class="form-text text-muted">Format: 28th May 2024 - 3rd June 2024</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Certificate Template (PNG/JPG)</label>
                        <input type="file" name="template" class="form-control" accept=".png,.jpg,.jpeg" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Student Data (Excel)</label>
                        <input type="file" name="data_file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="form-text text-muted">
                            Download template <a href="/templates/certificate_template.xlsx">here</a>
                        </small>
                    </div>
                    
                    <div id="template-designer" class="my-4 border p-3">
                        <h5>Position Text Elements</h5>
                        <div class="position-relative" style="width: 100%; max-width: 1000px; margin: 0 auto;">
                            <div id="template-container" style="position: relative; min-height: 300px;">
                                <img id="template-preview" class="w-100" style="display: none; position: absolute; top: 0; left: 0;">
                                <div class="draggables-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                    <div class="draggable" data-field="registration_number">SME</div>
                                    <div class="draggable" data-field="name">Name</div>
                                    <div class="draggable" data-field="grade">Project Title</div>
                                    <div class="draggable" data-field="date">Date Range</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="positions" id="positions">
                    
                    <button type="submit" class="btn btn-primary">Generate Certificates</button>
                </form>
            </div>
        </div>
    </div>
    <?php include("footer.html"); ?>
</div>

<style>
.draggable {
    position: absolute;
    cursor: move;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.9);
    border: 2px solid #007bff;
    z-index: 1000;
    user-select: none;
    border-radius: 4px;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s;
    left: 0;
    top: 0;
}

.draggable:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

#template-container {
    position: relative;
    background: #f8f9fa;
    min-height: 300px;
    border: 1px solid #ddd;
    overflow: hidden;
}

#template-preview {
    max-width: 100%;
    height: auto;
    display: block;
}

.draggables-container {
    pointer-events: none;
}

.draggables-container .draggable {
    pointer-events: all;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
<script>
// Update the template preview and container dimensions
document.querySelector('input[name="template"]').addEventListener('change', function(e) {
    var file = e.target.files[0];
    var reader = new FileReader();
    
    reader.onload = function(e) {
        var img = document.getElementById('template-preview');
        img.onload = function() {
            // Show the image
            img.style.display = 'block';
            
            // Set container dimensions maintaining aspect ratio
            var container = document.getElementById('template-container');
            const aspectRatio = img.naturalHeight / img.naturalWidth;
            const containerWidth = container.offsetWidth;
            container.style.height = (containerWidth * aspectRatio) + 'px';
            
            // Reset draggable positions
            document.querySelectorAll('.draggable').forEach(el => {
                el.style.transform = 'translate(0px, 0px)';
                el.setAttribute('data-x', 0);
                el.setAttribute('data-y', 0);
            });
            
            // Update positions
            updatePositions();
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
});

// Initialize interact.js
interact('.draggable').draggable({
    inertia: true,
    modifiers: [
        interact.modifiers.restrictRect({
            restriction: '#template-container',
            endOnly: true
        })
    ],
    listeners: {
        move: dragMoveListener,
        end: function (event) {
            updatePositions();
        }
    }
}).on('dragstart', function (event) {
    event.target.style.zIndex = 1000; // Ensure dragged element is on top
});

function dragMoveListener(event) {
    var target = event.target;
    var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
    var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

    // Keep the element within the container bounds
    var container = document.getElementById('template-container');
    x = Math.min(Math.max(0, x), container.offsetWidth - target.offsetWidth);
    y = Math.min(Math.max(0, y), container.offsetHeight - target.offsetHeight);

    target.style.transform = `translate(${x}px, ${y}px)`;
    target.setAttribute('data-x', x);
    target.setAttribute('data-y', y);
}

// Store the last successful preview positions
let lastPreviewPositions = null;

async function showPreview(event) {
    event.preventDefault();
    
    const form = document.getElementById('certificate-form');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/admin/certificate_generations/preview', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Store the successful preview positions
            lastPreviewPositions = data.positions;
            console.log('Stored preview positions:', lastPreviewPositions);
            
            // Show preview
            document.getElementById('preview-image').src = data.previewUrl;
            document.getElementById('preview-reg').textContent = data.data.registration_number;
            document.getElementById('preview-name').textContent = data.data.name;
            document.getElementById('preview-grade').textContent = data.data.grade;
            
            // Show preview section, hide form
            document.getElementById('preview-section').style.display = 'block';
            form.style.display = 'none';
        } else {
            alert('Failed to generate preview: ' + data.error);
        }
    } catch (error) {
        console.error('Preview error:', error);
        alert('Error generating preview: ' + error.message);
    }
}

function cancelGeneration() {
    document.getElementById('preview-section').style.display = 'none';
    document.getElementById('certificate-form').style.display = 'block';
}

function proceedGeneration() {
    // Use the stored preview positions
    if (!lastPreviewPositions) {
        alert('Error: Preview positions not found. Please try again.');
        return;
    }
    
    // Set the positions from the preview
    document.getElementById('positions').value = JSON.stringify(lastPreviewPositions);
    console.log('Submitting with positions:', lastPreviewPositions);
    
    // Submit the form
    document.getElementById('certificate-form').submit();
}

function updatePositions() {
    var container = document.getElementById('template-container');
    var img = document.getElementById('template-preview');
    var positions = {};
    
    // Use actual image dimensions for the grid
    const imageWidth = img.naturalWidth;
    const imageHeight = img.naturalHeight;
    
    document.querySelectorAll('.draggable').forEach(el => {
        var x = parseFloat(el.getAttribute('data-x')) || 0;
        var y = parseFloat(el.getAttribute('data-y')) || 0;
        
        // Get the element's dimensions
        const boxWidth = el.offsetWidth;
        const boxHeight = el.offsetHeight;
        
        // Add half the box dimensions to get the center point
        const centerX = x + (boxWidth / 2);
        const centerY = y + (boxHeight / 2);
        
        // Calculate positions based on actual image dimensions, using the center point
        positions[el.dataset.field] = {
            x: Math.round((centerX / container.offsetWidth) * imageWidth),
            y: Math.round((centerY / container.offsetHeight) * imageHeight)
        };
    });
    
    document.getElementById('positions').value = JSON.stringify(positions);
    console.log('Updated positions:', positions);
}

// Add resize observer to handle window resizing
const resizeObserver = new ResizeObserver(entries => {
    for (let entry of entries) {
        if (entry.target.id === 'template-container') {
            updatePositions();
        }
    }
});

resizeObserver.observe(document.getElementById('template-container'));
</script> 