<?php include("sidebar.php"); ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Edit Cohort</h3>
                <form method="POST" action="/admin/edit_cohort/<?php echo $cohort['id']; ?>">
                    <div class="form-group">
                        <label for="name">Cohort Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($cohort['name']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Cohort</button>
                </form>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>