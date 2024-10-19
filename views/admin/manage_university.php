<?php
include 'sidebar.php';// Include your database connection

// Fetch universities from the database
function fetchUniversities($conn) {
    $sql = "SELECT * FROM universities"; // Adjust the table name as needed
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$universities = fetchUniversities($conn);
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:x-large">Dashboard</p><br>
                        <div class="table-responsive">
                            <form id="universityForm" method="post" action="">
                                <table class="table table-striped table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Serial Number</th>
                                            <th>Long Name</th>
                                            <th>Short Name</th>
                                            <th>Location</th>
                                            <th>Country</th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $serialNumber = 1;
                                        foreach ($universities as $university): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected[]" value="<?= $university['id'] ?>"></td>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= $university['long_name'] ?></td>
                                                <td><?= $university['short_name'] ?></td>
                                                <td><?= $university['location'] ?></td>
                                                <td><?= $university['country'] ?></td>
                                                <td>
                                                    <!-- <button type="button" class="btn btn-primary edit-btn" data-id="<?= $university['id'] ?>" data-long_name="<?= $university['long_name'] ?>" data-short_name="<?= $university['short_name'] ?>" data-location="<?= $university['location'] ?>" data-country="<?= $university['country'] ?>">Edit</button> -->
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <!-- <button type="submit" name="delete" class="btn btn-danger">Delete Selected</button> -->
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editForm" method="post" action="controllers/AdminController.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit University</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label for="edit-long_name">Long Name</label>
                        <input type="text" class="form-control" id="edit-long_name" name="long_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-short_name">Short Name</label>
                        <input type="text" class="form-control" id="edit-short_name" name="short_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-location">Location</label>
                        <input type="text" class="form-control" id="edit-location" name="location" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-country">Country</label>
                        <input type="text" class="form-control" id="edit-country" name="country" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function() {
        $('.edit-btn').on('click', function() {
            var id = $(this).data('id');
            var long_name = $(this).data('long_name');
            var short_name = $(this).data('short_name');
            var location = $(this).data('location');
            var country = $(this).data('country');
            $('#edit-id').val(id);
            $('#edit-long_name').val(long_name);
            $('#edit-short_name').val(short_name);
            $('#edit-location').val(location);
            $('#edit-country').val(country);
            $('#editModal').modal('show');
        });
    });
</script>