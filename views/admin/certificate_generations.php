<?php include("sidebar.php"); ?>
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
                
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="font-weight-bold">Certificate Generations</h3>
                    <a href="/admin/certificate_generations/create" class="btn btn-primary">
                        Generate New Certificates
                    </a>
                </div>
                
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Generated Count</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($generations as $gen): ?>
                            <tr>
                                <td><?= htmlspecialchars($gen['subject']) ?></td>
                                <td><?= $gen['generated_count'] ?></td>
                                <td><?= $gen['status'] ?></td>
                                <td><?= $gen['created_at'] ?></td>
                                <td>
                                    <a href="/admin/certificate_generations/download/<?= $gen['id'] ?>" 
                                       class="btn btn-primary btn-sm">Download All</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 