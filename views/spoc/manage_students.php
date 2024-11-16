<?php include 'sidebar.php'; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Students</h3>
                    </div>
                    <!-- <div class="col-12 col-xl-4 text-right">
                        <a href="addStudent.php" class="btn btn-primary">Add Student</a>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Students</p><br>
                        <div class="table-responsive">
                            <input class="form-control mb-3" id="searchInput" type="text" placeholder="Search...">
                            <form id="studentForm" method="post" action="deleteStudent.php">
                                <table class="table table-striped table-borderless table-lg">
                                    <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Serial Number</th>
                                            <th>Registration Number</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Section</th>
                                            <th>Stream</th>
                                            <th>Year</th>
                                            <th>Department</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentTable">
                                        <?php
                                        $serialNumber = 1;
                                        foreach ($students as $student): ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected[]" value="<?= $student['id'] ?>"></td>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= htmlspecialchars($student['regd_no']) ?></td>
                                                <td><?= htmlspecialchars($student['name']) ?></td>
                                                <td><?= htmlspecialchars($student['email']) ?></td>
                                                <td><?= htmlspecialchars($student['section']) ?></td>
                                                <td><?= htmlspecialchars($student['stream']) ?></td>
                                                <td><?= htmlspecialchars($student['year']) ?></td>
                                                <td><?= htmlspecialchars($student['dept']) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm edit-btn" data-id="<?= $student['id'] ?>" data-regd_no="<?= $student['regd_no'] ?>" data-name="<?= $student['name'] ?>" data-email="<?= $student['email'] ?>" data-section="<?= $student['section'] ?>" data-stream="<?= $student['stream'] ?>" data-year="<?= $student['year'] ?>" data-dept="<?= $student['dept'] ?>">Edit</button>
                                                    <button type="submit" name="delete" value="<?= $student['id'] ?>" class="btn btn-danger btn-sm">Delete</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;">No records found</div>
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
            <form id="editForm" method="post" action="updateStudent.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="form-group">
                        <label for="edit-regd_no">Registration Number</label>
                        <input type="text" class="form-control" id="edit-regd_no" name="regd_no" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-section">Section</label>
                        <input type="text" class="form-control" id="edit-section" name="section" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-stream">Stream</label>
                        <input type="text" class="form-control" id="edit-stream" name="stream" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-year">Year</label>
                        <input type="text" class="form-control" id="edit-year" name="year" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-dept">Department</label>
                        <input type="text" class="form-control" id="edit-dept" name="dept" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const regd_no = this.getAttribute('data-regd_no');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');
                const section = this.getAttribute('data-section');
                const stream = this.getAttribute('data-stream');
                const year = this.getAttribute('data-year');
                const dept = this.getAttribute('data-dept');

                document.getElementById('edit-id').value = id;
                document.getElementById('edit-regd_no').value = regd_no;
                document.getElementById('edit-name').value = name;
                document.getElementById('edit-email').value = email;
                document.getElementById('edit-section').value = section;
                document.getElementById('edit-stream').value = stream;
                document.getElementById('edit-year').value = year;
                document.getElementById('edit-dept').value = dept;

                $('#editModal').modal('show');
            });
        });

        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('#studentTable tr');
            let noRecords = true;

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        match = true;
                    }
                });
                if (match) {
                    row.style.display = '';
                    noRecords = false;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('noRecords').style.display = noRecords ? '' : 'none';
        });
    });
</script>