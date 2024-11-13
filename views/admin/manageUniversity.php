<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Universities</h3>
                    </div>
                    <div class="col-12 col-xl-4 text-right">
                        <a href="addUniversity.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create University
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Universities</p><br>
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="🔍 Search Universities...">
                            <form id="universityForm" method="post" action="deleteUniversity.php">
                                <table class="table table-hover table-borderless table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Select</th>
                                            <th>Serial Number <i class="fas fa-sort"></i></th>
                                            <th>Long Name <i class="fas fa-sort"></i></th>
                                            <th>Short Name <i class="fas fa-sort"></i></th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="universityTable">
                                        <?php $serialNumber = 1; foreach ($universities as $university): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected[]" value="<?= $university['id'] ?>"></td>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= $university['long_name'] ?></td>
                                                <td><?= $university['short_name'] ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-primary btn-sm edit-btn" 
                                                            data-id="<?= $university['id'] ?>" 
                                                            data-long_name="<?= $university['long_name'] ?>" 
                                                            data-short_name="<?= $university['short_name'] ?>" 
                                                            data-location="<?= $university['location'] ?>" 
                                                            data-country="<?= $university['country'] ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="submit" name="delete" value="<?= $university['id'] ?>" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editForm" method="post" action="updateUniversity.php">
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
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

        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var visibleRows = 0;
            $('#universityTable tr').filter(function() {
                var isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(isVisible);
                if (isVisible) visibleRows++;
            });
            $('#noRecords').toggle(visibleRows === 0);
        });
    });
</script>
