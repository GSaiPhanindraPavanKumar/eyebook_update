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
                        <a href="addUniversity" class="btn btn-primary">
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
                                            <th data-sort="serialNumber">S.no <i class="fas fa-sort"></i></th>
                                            <th data-sort="longName">University Name <i class="fas fa-sort"></i></th>
                                            <th data-sort="shortName">Short Name <i class="fas fa-sort"></i></th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="universityTable">
                                        <?php
                                        $serialNumber = 1;
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $offset = ($page - 1) * $limit;
                                        $total_universities = count($universities);
                                        $total_pages = ceil($total_universities / $limit);
                                        $universities_paginated = array_slice($universities, $offset, $limit);

                                        foreach ($universities_paginated as $university): ?>
                                            <tr>
                                                <td><?= $serialNumber++ ?></td>
                                                <td><?= htmlspecialchars($university['long_name']) ?></td>
                                                <td><?= htmlspecialchars($university['short_name']) ?></td>
                                                <!-- <td>
                                                    <button type="submit" name="view" class="btn btn-outline-info btn-sm"><i class="fas fa-eye"></i>View</button>
                                                </td> -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="noRecords" style="display: none;" class="text-center">No records found</div>
                            </form>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
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

        $('th[data-sort]').on('click', function() {
            var table = $(this).parents('table').eq(0);
            var rows = table.find('tbody tr').toArray().sort(comparer($(this).index()));
            this.asc = !this.asc;
            if (!this.asc) { rows = rows.reverse(); }
            for (var i = 0; i < rows.length; i++) { table.append(rows[i]); }
        });

        function comparer(index) {
            return function(a, b) {
                var valA = getCellValue(a, index), valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
            };
        }

        function getCellValue(row, index) {
            return $(row).children('td').eq(index).text();
        }
    });
</script>