<?php include 'sidebar.php'; ?>
<?php use Models\University; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">View University</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">University Details</h5>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($company['name']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($company['description']); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Universities</h5>
                            <div>
                                <button class="btn btn-danger btn-sm" id="removeUniversityBtn"><i class="fas fa-trash"></i> Remove University</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <?php if (empty($universities)): ?>
                                <div class="text-center py-5">
                                    <img src="/views/landing/assets/img/empty-box.png" alt="No Universities" style="width: 150px; opacity: 0.5;">
                                    <p class="text-muted mt-3">No universities found</p>
                                </div>
                            <?php else: ?>
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Select</th>
                                            <th>Name</th>
                                            <th>Short Name</th>
                                            <th>No of Faculties</th>
                                            <th>No of Students</th>
                                            <th>View</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($universities as $university): ?>
                                            <tr>
                                                <td><input type="checkbox" name="select_university" value="<?php echo $university['id']; ?>"></td>
                                                <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                                <td><?php echo htmlspecialchars($university['short_name']); ?></td>
                                                <td><?php echo University::getCountfacultyByUniversityId($conn, $university['id']); ?></td>
                                                <td><?php echo University::getCountByUniversityId($conn, $university['id']); ?></td>
                                                <td><a href="/admin/view_university/<?php echo $university['id']; ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i> View</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add University Form -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Add University</h5>
                            <button type="submit" class="btn btn-primary mb-2 ml-2" id="submitAddUniversity">
                                <span class="button-text">Submit</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="table-responsive">
                            <?php 
                            $availableUniversities = array_filter($allUniversities, function($university) {
                                return empty($university['company_id']);
                            });
                            ?>
                            
                            <?php if (empty($availableUniversities)): ?>
                                <div class="text-center py-5">
                                    <img src="/views/landing/assets/img/empty-box.png" alt="No Universities Available" style="width: 150px; opacity: 0.5;">
                                    <p class="text-muted mt-3">No universities available to add</p>
                                </div>
                            <?php else: ?>
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Select</th>
                                            <th>Name</th>
                                            <th>Short Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allUniversities as $university): ?>
                                            <?php if (empty($university['company_id'])): ?>
                                                <tr>
                                                    <td><input type="checkbox" name="select_university_add" value="<?php echo htmlspecialchars($university['id']); ?>"></td>
                                                    <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($university['short_name']); ?></td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->


<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script>
    $(document).ready(function() {
        $('#submitAddUniversity').on('click', function(event) {
            event.preventDefault();
            const $button = $(this);
            const $buttonText = $button.find('.button-text');
            const $spinner = $button.find('.spinner-border');
            
            var selectedUniversities = [];
            $('input[name="select_university_add"]:checked').each(function() {
                selectedUniversities.push($(this).val());
            });

            if (selectedUniversities.length === 0) {
                alert('Please select at least one university to add.');
                return;
            }

            // Disable button and show loading state
            $button.prop('disabled', true);
            $buttonText.text('Submitting...');
            $spinner.removeClass('d-none');

            var companyId = <?php echo $company['id']; ?>;

            $.ajax({
                url: '/admin/add_university_to_company',
                type: 'POST',
                data: { university_ids: selectedUniversities, company_id: companyId },
                success: function(response) {
                    var res = JSON.parse(response);
                    var msg = res.message;
                    msg = msg.replace('Universities', 'Sub-Universities');
                    msg = msg.replace('company', 'university');
                    alert(msg);
                    location.reload();
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                    // Reset button state on error
                    $button.prop('disabled', false);
                    $buttonText.text('Submit');
                    $spinner.addClass('d-none');
                }
            });
        });

        $('#removeUniversityBtn').on('click', function() {
            var selectedUniversities = [];
            $('input[name="select_university"]:checked').each(function() {
                selectedUniversities.push($(this).val());
            });

            if (selectedUniversities.length === 0) {
                alert('Please select at least one university to remove.');
                return;
            }

            if (confirm('Are you sure you want to remove the selected universities?')) {
                $.ajax({
                    url: '/admin/remove_universities',
                    type: 'POST',
                    data: { university_ids: selectedUniversities },
                    success: function(response) {
                        var res = JSON.parse(response);
                        alert(res.message);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + error);
                    }
                });
            }
        });
    });
</script>

<style>
/* Add some spacing between spinner and text */
.spinner-border {
    margin-left: 8px;
}

/* Ensure button maintains its width during state changes */
#submitAddUniversity {
    min-width: 100px;
}

/* Optional: Add transition for smooth state changes */
.btn {
    transition: all 0.3s ease;
}

/* Add styles for empty state */
.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.text-muted {
    color: #6c757d !important;
}

.mt-3 {
    margin-top: 1rem !important;
}
</style>