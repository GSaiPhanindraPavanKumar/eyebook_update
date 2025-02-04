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
                        <h4 class="card-title">Company Details</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <tbody>
                                    <?php if (isset($company['name']) && !empty($company['name'])): ?>
                                    <tr>
                                        <td><strong>Company Name:</strong></td>
                                        <td><?php echo htmlspecialchars($company['name'] ?? ''); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (isset($company['email']) && !empty($company['email'])): ?>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?php echo htmlspecialchars($company['email'] ?? ''); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (isset($company['phone']) && !empty($company['phone'])): ?>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo htmlspecialchars($company['phone'] ?? ''); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (isset($company['address']) && !empty($company['address'])): ?>
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td><?php echo htmlspecialchars($company['address'] ?? ''); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (isset($company['website']) && !empty($company['website'])): ?>
                                    <tr>
                                        <td><strong>Website:</strong></td>
                                        <td><?php echo htmlspecialchars($company['website'] ?? ''); ?></td>
                                    </tr>
                                    <?php endif; ?>

                                    <?php if (isset($company['created_at']) && !empty($company['created_at'])): ?>
                                    <tr>
                                        <td><strong>Created At:</strong></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($company['created_at']))); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> mt-3">
                            <?php 
                            echo $_SESSION['message'];
                            unset($_SESSION['message']);
                            unset($_SESSION['message_type']);
                            ?>
                        </div>
                        <?php endif; ?>

                        <div class="mt-3">
                            <a href="/admin/edit_company/<?php echo htmlspecialchars($company['id'] ?? ''); ?>" class="btn btn-primary">Edit Company</a>
                            <a href="/admin/companies" class="btn btn-secondary">Back to Companies</a>
                        </div>
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
                            <div id="noRecords" style="display: none;" class="text-center">No records found</div>
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
                            <button type="submit" class="btn btn-primary mb-2 ml-2" id="submitAddUniversity">Submit</button>
                        </div>
                        <div class="table-responsive">
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
            var selectedUniversities = [];
            $('input[name="select_university_add"]:checked').each(function() {
                selectedUniversities.push($(this).val());
            });

            if (selectedUniversities.length === 0) {
                alert('Please select at least one university to add.');
                return;
            }

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