<?php include("sidebar.php"); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="font-weight-bold">Certificate Generations</h3>
                    <a href="/admin/certificate_generations/create" class="btn btn-primary">
                        Generate New Certificates
                    </a>
                </div>

                <!-- Search Box -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span style="border-radius: 8px; font-size: 10px;" class="input-group-text bg-primary text-white">
                                            <i class="ti-search"></i>
                                        </span>
                                    </div>
                                    <input type="text" 
                                           id="searchInput" 
                                           class="form-control" 
                                           placeholder="Search by subject..."
                                           style="height: 38px; border-radius: 5px;">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select id="statusFilter" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="completed">Completed</option>
                                    <option value="processing">Processing</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <style>
                    .status-badge {
                        padding: 8px 16px;
                        border-radius: 50px;
                        font-weight: 500;
                        font-size: 0.875rem;
                    }
                    .status-completed {
                        color: #2d8a3e;
                        background-color: #ebfbee;
                        border: 1px solid #2d8a3e;
                    }
                    .status-failed {
                        color: #e03131;
                        background-color: #fff5f5;
                        border: 1px solid #e03131;
                    }
                    .status-processing {
                        color: #1971c2;
                        background-color: #e7f5ff;
                        border: 1px solid #1971c2;
                    }
                    .no-results {
                        text-align: center;
                        padding: 2rem;
                        color: #666;
                        font-style: italic;
                    }
                    
                    /* Pagination styles */
                    .pagination-container {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-top: 1.5rem;
                        padding: 1rem;
                        background-color: #f8f9fa;
                        border-radius: 8px;
                    }
                    
                    .pagination {
                        margin: 0;
                    }
                    
                    .page-link {
                        color: #1971c2;
                        border: 1px solid #1971c2;
                        margin: 0 2px;
                        border-radius: 4px;
                    }
                    
                    .page-item.active .page-link {
                        background-color: #1971c2;
                        border-color: #1971c2;
                    }
                    
                    .page-info {
                        color: #666;
                        font-size: 0.9rem;
                    }
                    
                    .records-per-page {
                        width: auto;
                        display: inline-block;
                        margin-left: 0.5rem;
                    }

                    .download-btn {
                        cursor: pointer;
                    }

                    .download-btn.disabled {
                        pointer-events: none;
                        opacity: 0.65;
                    }

                    .download-btn .spinner-border {
                        width: 1rem;
                        height: 1rem;
                        margin-right: 0.5rem;
                    }

                    .dropdown-item {
                        display: flex;
                        align-items: center;
                        padding: 0.5rem 1rem;
                    }

                    .dropdown-item i {
                        width: 20px;
                    }

                    .btn-group {
                        position: relative;
                    }

                    .dropdown-menu {
                        position: absolute;
                        min-width: 200px;
                        padding: 0.5rem 0;
                        margin: 0.125rem 0 0;
                        background-color: #fff;
                        border: 1px solid rgba(0,0,0,.15);
                        border-radius: 0.25rem;
                        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
                        z-index: 1000;
                    }

                    .dropdown-item {
                        display: flex;
                        align-items: center;
                        padding: 0.75rem 1rem;
                        color: #212529;
                        text-decoration: none;
                        background-color: transparent;
                        border: 0;
                        white-space: nowrap;
                    }

                    .dropdown-item.disabled {
                        pointer-events: none;
                        opacity: 0.65;
                        background-color: #f8f9fa;
                    }

                    .dropdown-item i {
                        width: 20px;
                        margin-right: 0.75rem;
                    }

                    .spinner-container {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .spinner-border-sm {
                        width: 1rem;
                        height: 1rem;
                        border-width: 0.2em;
                        margin-right: 0.5rem;
                    }

                    /* Add animation for spinner */
                    @keyframes spinner-border {
                        to { transform: rotate(360deg); }
                    }

                    .spinner-border {
                        display: inline-block;
                        width: 1rem;
                        height: 1rem;
                        vertical-align: text-bottom;
                        border: 0.2em solid currentColor;
                        border-right-color: transparent;
                        border-radius: 50%;
                        animation: spinner-border .75s linear infinite;
                    }

                    .download-option {
                        min-width: 180px;
                        padding: 10px 20px;
                    }

                    .download-btn.loading {
                        pointer-events: none;
                        opacity: 0.75;
                    }

                    .spinner-border {
                        display: inline-block;
                        width: 1rem;
                        height: 1rem;
                        vertical-align: text-bottom;
                        border: 0.2em solid currentColor;
                        border-right-color: transparent;
                        border-radius: 50%;
                        animation: spinner-border .75s linear infinite;
                    }

                    @keyframes spinner-border {
                        to { transform: rotate(360deg); }
                    }
                </style>

                <div class="table-responsive mt-3">
                    <table class="table table-hover" id="certificateTable">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Generated Count</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php foreach ($generations as $gen): ?>
                            <tr>
                                <td><?= htmlspecialchars($gen['subject']) ?></td>
                                <td><?= $gen['generated_count'] ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch($gen['status']) {
                                        case 'completed':
                                            $statusClass = 'status-completed';
                                            break;
                                        case 'failed':
                                            $statusClass = 'status-failed';
                                            break;
                                        case 'processing':
                                            $statusClass = 'status-processing';
                                            break;
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= ucfirst($gen['status']) ?>
                                    </span>
                                </td>
                                <td><?= $gen['created_at'] ?></td>
                                <td>
                                    <?php if ($gen['status'] === 'completed'): ?>
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-sm download-btn" 
                                                data-generation-id="<?= $gen['id'] ?>">
                                            <span class="btn-content">
                                                <i class="ti-download mr-1"></i> Download
                                            </span>
                                        </button>
                                    <?php elseif ($gen['status'] === 'processing'): ?>
                                        <a href="/admin/certificate_generations/progress/<?= $gen['id'] ?>" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="ti-reload"></i> View Progress
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="ti-close"></i> Failed
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div id="noResults" class="no-results" style="display: none;">
                        No matching records found
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="d-flex align-items-center">
                            <span class="page-info">Show</span>
                            <select class="form-control records-per-page ml-2" id="recordsPerPage">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="page-info ml-2">entries</span>
                        </div>
                        
                        <ul class="pagination" id="pagination">
                            <!-- Pagination will be generated by JavaScript -->
                        </ul>
                        
                        <div class="page-info" id="pageInfo">
                            Showing <span id="startRecord">1</span> to <span id="endRecord">10</span> of <span id="totalRecords">0</span> entries
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("footer.html"); ?>
</div>

<div class="modal fade" id="downloadModal" tabindex="-1" role="dialog" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="downloadModalLabel">Download Certificates</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-center mb-4">Choose download format:</p>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-primary mr-3 download-option" data-format="images">
                        <i class="fas fa-images mr-2"></i>
                        Download as Images
                    </button>
                    <button class="btn btn-outline-primary download-option" data-format="pdf">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Download as PDF
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Add this hidden iframe at the bottom of the page -->
<iframe id="downloadFrame" style="display:none;"></iframe>

<script>
// Store all records for client-side pagination and search
const allRecords = <?= json_encode($allGenerations) ?>;
let currentPage = 1;
let recordsPerPage = 10;
let filteredRecords = [...allRecords];

function generateTableRow(gen) {
    let statusClass = '';
    switch(gen.status) {
        case 'completed':
            statusClass = 'status-completed';
            break;
        case 'failed':
            statusClass = 'status-failed';
            break;
        case 'processing':
            statusClass = 'status-processing';
            break;
    }
    
    let actionButton = '';
    if (gen.status === 'completed') {
        actionButton = `
            <button type="button" 
                    class="btn btn-outline-primary btn-sm download-btn" 
                    data-generation-id="${gen.id}">
                <span class="btn-content">
                    <i class="ti-download mr-1"></i> Download
                </span>
            </button>`;
    } else if (gen.status === 'processing') {
        actionButton = `<a href="/admin/certificate_generations/progress/${gen.id}" 
                          class="btn btn-outline-info btn-sm">
                          <i class="ti-reload mr-1"></i> View Progress
                       </a>`;
    } else {
        actionButton = `<button class="btn btn-outline-secondary btn-sm" disabled>
                          <i class="ti-close mr-1"></i> Failed
                       </button>`;
    }
    
    return `
        <tr>
            <td>${gen.subject}</td>
            <td>${gen.generated_count}</td>
            <td>
                <span class="status-badge ${statusClass}">
                    ${gen.status.charAt(0).toUpperCase() + gen.status.slice(1)}
                </span>
            </td>
            <td>${gen.created_at}</td>
            <td>${actionButton}</td>
        </tr>
    `;
}

function filterAndDisplayRecords() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusTerm = document.getElementById('statusFilter').value.toLowerCase();
    
    // Filter records
    filteredRecords = allRecords.filter(gen => {
        const matchesSearch = gen.subject.toLowerCase().includes(searchTerm);
        const matchesStatus = !statusTerm || gen.status.toLowerCase() === statusTerm;
        return matchesSearch && matchesStatus;
    });
    
    // Update pagination
    updatePagination();
    // Display current page
    displayCurrentPage();
}

function displayCurrentPage() {
    const startIndex = (currentPage - 1) * recordsPerPage;
    const endIndex = Math.min(startIndex + recordsPerPage, filteredRecords.length);
    const recordsToShow = filteredRecords.slice(startIndex, endIndex);
    
    // Generate table rows
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = recordsToShow.map(generateTableRow).join('');
    
    // Reinitialize download buttons
    initializeDownloadButtons();
    
    // Update page info
    document.getElementById('startRecord').textContent = filteredRecords.length ? startIndex + 1 : 0;
    document.getElementById('endRecord').textContent = endIndex;
    document.getElementById('totalRecords').textContent = filteredRecords.length;
    
    // Show/hide no results message
    const table = document.getElementById('certificateTable');
    const noResults = document.getElementById('noResults');
    table.style.display = filteredRecords.length ? '' : 'none';
    noResults.style.display = filteredRecords.length ? 'none' : 'block';
}

function updatePagination() {
    const totalPages = Math.ceil(filteredRecords.length / recordsPerPage);
    const pagination = document.getElementById('pagination');
    
    let paginationHtml = '';
    
    // Previous button
    paginationHtml += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 || 
            i === totalPages || 
            (i >= currentPage - 2 && i <= currentPage + 2)
        ) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        } else if (
            i === currentPage - 3 || 
            i === currentPage + 3
        ) {
            paginationHtml += `
                <li class="page-item disabled">
                    <a class="page-link" href="#">...</a>
                </li>
            `;
        }
    }
    
    // Next button
    paginationHtml += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>
    `;
    
    pagination.innerHTML = paginationHtml;
    
    // Add click handlers
    pagination.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const newPage = parseInt(e.target.dataset.page);
            if (!isNaN(newPage) && newPage > 0 && newPage <= totalPages) {
                currentPage = newPage;
                displayCurrentPage();
                updatePagination();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initial display
    filterAndDisplayRecords();
    
    // Search input handler
    document.getElementById('searchInput').addEventListener('input', () => {
        currentPage = 1;
        filterAndDisplayRecords();
    });
    
    // Status filter handler
    document.getElementById('statusFilter').addEventListener('change', () => {
        currentPage = 1;
        filterAndDisplayRecords();
    });
    
    // Records per page handler
    document.getElementById('recordsPerPage').addEventListener('change', (e) => {
        recordsPerPage = parseInt(e.target.value);
        currentPage = 1;
        filterAndDisplayRecords();
    });

    let currentDownloadBtn = null;
    
    // Handle main download button click
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentDownloadBtn = this;
            $('#downloadModal').modal('show'); // Use jQuery modal
        });
    });

    // Add download helper function
    function startDownload(url, button) {
        // Create a download timeout
        const downloadTimeout = setTimeout(() => {
            resetButton(button);
        }, 8000); // 15 second fallback

        // Setup download frame
        const downloadFrame = document.getElementById('downloadFrame');
        
        // Listen for the load event
        downloadFrame.onload = function() {
            // Clear the timeout since download has started
            clearTimeout(downloadTimeout);
            // Reset the button
            resetButton(button);
        };

        // Start download
        downloadFrame.src = url;
    }

    // Add reset button function
    function resetButton(button) {
        if (!button) return;
        
        button.classList.remove('loading');
        button.querySelector('.btn-content').innerHTML = `
            <i class="ti-download mr-1"></i> Download
        `;
    }

    // Update the format selection handler
    document.querySelectorAll('.download-option').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (!currentDownloadBtn) return;
            
            const format = this.dataset.format;
            const generationId = currentDownloadBtn.dataset.generationId;
            
            // Close modal
            $('#downloadModal').modal('hide');
            
            // Show spinner in button
            currentDownloadBtn.classList.add('loading');
            currentDownloadBtn.querySelector('.btn-content').innerHTML = `
                <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                Downloading...
            `;
            
            // Start download with the current button reference
            const downloadUrl = `/admin/certificate_generations/download/${generationId}${format === 'pdf' ? '?format=pdf' : ''}`;
            startDownload(downloadUrl, currentDownloadBtn);
            
            // Clear current button reference
            currentDownloadBtn = null;
        });
    });

    // Update the modal close handler to also reset any loading button
    $('#downloadModal').on('hidden.bs.modal', function () {
        if (currentDownloadBtn) {
            resetButton(currentDownloadBtn);
            currentDownloadBtn = null;
        }
    });
});

// Add this function to reinitialize download buttons after table updates
function initializeDownloadButtons() {
    document.querySelectorAll('.download-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            currentDownloadBtn = this;
            $('#downloadModal').modal('show');
        });
    });
}
</script> 