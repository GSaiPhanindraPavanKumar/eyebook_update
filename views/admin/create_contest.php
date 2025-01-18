<?php include 'sidebar.php'; ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Create Contest</h3>
                <form action="/admin/save_contest" method="POST">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="datetime-local" class="form-control" id="end_date" name="end_date" required>
                    </div>
                    <div class="form-group">
                        <label for="universities">Select Universities</label>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>University Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($universities as $university): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="university_id[]" value="<?php echo $university['id']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($university['long_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Contest</button>
                </form>
            </div>
        </div>
    </div>
</div>