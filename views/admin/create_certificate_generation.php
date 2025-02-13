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
                        <div class="drop-zone">
                            <span class="drop-zone__prompt">Drop template file here or click to upload</span>
                            <input type="file" name="template" class="drop-zone__input" accept=".png,.jpg,.jpeg" required>
                        </div>
                        <small class="form-text text-muted">Supported formats: PNG, JPG</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Student Data (Excel)</label>
                        <div class="drop-zone">
                            <span class="drop-zone__prompt">Drop Excel file here or click to upload</span>
                            <input type="file" name="data_file" class="drop-zone__input" accept=".xlsx,.xls" required>
                        </div>
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

<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Error</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="errorModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
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

.draggable.unpositioned {
    border-color: #dc3545;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
    }
}

/* Add a helper text */
#template-designer::before {
    content: "Drag all text boxes onto the certificate template";
    display: block;
    margin-bottom: 10px;
    color: #666;
    font-style: italic;
}

.drop-zone {
    max-width: 100%;
    height: 100px;
    padding: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-size: 1.2rem;
    font-weight: 500;
    cursor: pointer;
    color: #666;
    border: 2px dashed #007bff;
    border-radius: 10px;
    margin-bottom: 10px;
    position: relative;
    transition: all 0.3s ease;
}

.drop-zone:hover {
    border-color: #0056b3;
    background-color: rgba(0,123,255,0.05);
}

.drop-zone.drop-zone--over {
    border-style: solid;
    background-color: rgba(0,123,255,0.1);
}

.drop-zone__input {
    display: none;
}

.drop-zone__thumb {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 10px;
    overflow: hidden;
    background-size: cover;
    background-position: center;
}

.drop-zone__thumb::after {
    content: attr(data-label);
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 5px 0;
    color: #ffffff;
    background: rgba(0, 0, 0, 0.75);
    font-size: 14px;
    text-align: center;
}

.file-display {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    background: rgba(255, 255, 255, 0.9);
    gap: 10px;
}

.file-display i {
    font-size: 1.5rem;
    color: #007bff;
}

.file-name {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.remove-file {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    transition: background-color 0.2s;
}

.remove-file:hover {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
<script>
// Update the template preview and container dimensions
document.querySelector('input[name="template"]').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/png'];
    if (!validateFileType(file, allowedTypes)) {
        this.value = ''; // Clear the input
        return;
    }

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
            document.getElementById('preview-date').textContent = data.data.date;
            
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

document.addEventListener('DOMContentLoaded', function() {
    // Add validation function
    function validatePositions() {
        const draggables = document.querySelectorAll('.draggable');
        const unpositioned = [];
        
        draggables.forEach(el => {
            const x = parseFloat(el.getAttribute('data-x')) || 0;
            const y = parseFloat(el.getAttribute('data-y')) || 0;
            
            // If element hasn't been moved (both x and y are 0), add to unpositioned array
            if (x === 0 && y === 0) {
                unpositioned.push(el.getAttribute('data-field'));
            }
        });
        
        return unpositioned;
    }

    // Update the showPreview function
    window.showPreview = function(event) {
        event.preventDefault();
        
        // Check if template is uploaded
        const templateInput = document.querySelector('input[name="template"]');
        if (!templateInput.files.length) {
            showErrorModal('Please upload a certificate template first');
            return false;
        }
        
        // Validate positions
        const unpositioned = validatePositions();
        if (unpositioned.length > 0) {
            const fieldNames = unpositioned.map(field => {
                switch(field) {
                    case 'registration_number': return 'SME';
                    case 'name': return 'Name';
                    case 'grade': return 'Project Title';
                    case 'date': return 'Date Range';
                    default: return field;
                }
            });
            
            showErrorModal(`
                <p>Please position all text boxes on the template.</p>
                <p>Unpositioned elements:</p>
                <ul>
                    ${fieldNames.map(name => `<li>${name}</li>`).join('')}
                </ul>
            `);
            return false;
        }
        
        // Update positions before preview
        updatePositions();
        
        // Continue with existing preview logic
        const form = document.getElementById('certificate-form');
        const formData = new FormData(form);
        
        fetch('/admin/certificate_generations/preview', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                lastPreviewPositions = data.positions;
                
                document.getElementById('preview-image').src = data.previewUrl;
                document.getElementById('preview-reg').textContent = data.data.registration_number;
                document.getElementById('preview-name').textContent = data.data.name;
                document.getElementById('preview-grade').textContent = data.data.grade;
                document.getElementById('preview-date').textContent = data.data.date;
                
                document.getElementById('preview-section').style.display = 'block';
                form.style.display = 'none';
            } else {
                alert('Failed to generate preview: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
            alert('Error generating preview: ' + error.message);
        });
        
        return false;
    };

    // Add visual indicator for unpositioned elements
    function updateDraggableStyles() {
        const draggables = document.querySelectorAll('.draggable');
        
        draggables.forEach(el => {
            const x = parseFloat(el.getAttribute('data-x')) || 0;
            const y = parseFloat(el.getAttribute('data-y')) || 0;
            
            if (x === 0 && y === 0) {
                el.classList.add('unpositioned');
            } else {
                el.classList.remove('unpositioned');
            }
        });
    }

    // Update styles initially and after drag
    updateDraggableStyles();
    interact('.draggable').on('dragend', updateDraggableStyles);
});

// Function to show error modal instead of alert
function showErrorModal(message) {
    document.getElementById('errorModalMessage').innerHTML = message;
    $('#errorModal').modal('show');
}

// Function to validate file type
function validateFileType(file, allowedTypes) {
    const fileType = file.type;
    if (!allowedTypes.includes(fileType)) {
        const allowedExtensions = allowedTypes.map(type => type.split('/')[1].toUpperCase()).join(', ');
        showErrorModal(`Invalid file type. Please upload a ${allowedExtensions} file.`);
        return false;
    }
    return true;
}

// Drag and drop functionality
document.querySelectorAll('.drop-zone__input').forEach(inputElement => {
    const dropZoneElement = inputElement.closest('.drop-zone');

    dropZoneElement.addEventListener('click', e => {
        inputElement.click();
    });

    inputElement.addEventListener('change', e => {
        if (inputElement.files.length) {
            const file = inputElement.files[0];
            const allowedTypes = inputElement.name === 'template' 
                ? ['image/jpeg', 'image/png']
                : ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            if (!validateFileType(file, allowedTypes)) {
                inputElement.value = '';
                return;
            }

            updateDropZoneFile(dropZoneElement, file);
        }
    });

    dropZoneElement.addEventListener('dragover', e => {
        e.preventDefault();
        dropZoneElement.classList.add('drop-zone--over');
    });

    ['dragleave', 'dragend'].forEach(type => {
        dropZoneElement.addEventListener(type, e => {
            dropZoneElement.classList.remove('drop-zone--over');
        });
    });

    dropZoneElement.addEventListener('drop', e => {
        e.preventDefault();

        if (e.dataTransfer.files.length) {
            const file = e.dataTransfer.files[0];
            const allowedTypes = inputElement.name === 'template' 
                ? ['image/jpeg', 'image/png']
                : ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            if (!validateFileType(file, allowedTypes)) {
                return;
            }

            inputElement.files = e.dataTransfer.files;
            updateDropZoneFile(dropZoneElement, file);
            
            // Trigger change event for template preview
            if (inputElement.name === 'template') {
                inputElement.dispatchEvent(new Event('change'));
            }
        }

        dropZoneElement.classList.remove('drop-zone--over');
    });
});

function updateDropZoneFile(dropZoneElement, file) {
    let thumbnailElement = dropZoneElement.querySelector('.drop-zone__thumb');
    const dropZonePrompt = dropZoneElement.querySelector('.drop-zone__prompt');
    const input = dropZoneElement.querySelector('.drop-zone__input');

    // Format file name - replace spaces with underscores for image files
    let fileName = file.name;
    if (file.type.startsWith('image/')) {
        fileName = fileName.replace(/\s+/g, '_');
        
        // Create a new file with the updated name
        const newFile = new File([file], fileName, { type: file.type });
        
        // Update the input's files
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(newFile);
        input.files = dataTransfer.files;
    }

    // Hide the prompt text
    if (dropZonePrompt) {
        dropZonePrompt.style.display = 'none';
    }

    // Remove existing thumbnail
    if (thumbnailElement) {
        thumbnailElement.remove();
    }

    // Add thumbnail container
    thumbnailElement = document.createElement('div');
    thumbnailElement.classList.add('drop-zone__thumb');
    thumbnailElement.dataset.label = fileName;
    dropZoneElement.appendChild(thumbnailElement);

    // Add file icon and name display
    const fileDisplay = document.createElement('div');
    fileDisplay.classList.add('file-display');
    
    // Choose icon based on file type
    const iconClass = file.type.startsWith('image/') ? 'fa-image' : 'fa-file-excel';
    fileDisplay.innerHTML = `
        <i class="fas ${iconClass}"></i>
        <span class="file-name">${fileName}</span>
        <button type="button" class="remove-file" aria-label="Remove file">
            <i class="fas fa-times"></i>
        </button>
    `;
    thumbnailElement.appendChild(fileDisplay);

    // Add remove button functionality
    const removeButton = fileDisplay.querySelector('.remove-file');
    removeButton.addEventListener('click', (e) => {
        e.stopPropagation();
        input.value = '';
        dropZonePrompt.style.display = 'block';
        thumbnailElement.remove();
        
        // If this was a template, hide the preview
        if (input.name === 'template') {
            const templatePreview = document.getElementById('template-preview');
            if (templatePreview) {
                templatePreview.style.display = 'none';
            }
            // Reset container height
            const container = document.getElementById('template-container');
            if (container) {
                container.style.height = '300px';
            }
        }
    });

    // Show thumbnail for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => {
            thumbnailElement.style.backgroundImage = `url('${reader.result}')`;
        };
    }
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 