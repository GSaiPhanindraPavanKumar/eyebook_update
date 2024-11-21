<?php include('sidebar.php'); ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Assignments</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <!-- <p class="card-title mb-0" style="font-size:larger">Universities</p><br> -->
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b">Student</th>
                                        <th class="py-2 px-4 border-b">File</th>
                                        <th class="py-2 px-4 border-b">Grade</th>
                                        <th class="py-2 px-4 border-b">Feedback</th>
                                        <th class="py-2 px-4 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="submissionTable">
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($submission['student_id']) ?></td>
                                            <td class="py-2 px-4 border-b"><a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="text-blue-500 hover:underline">Download</a></td>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($submission['grade']) ?></td>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($submission['feedback']) ?></td>
                                            <td class="py-2 px-4 border-b">
                                                <form action="/faculty/mark_assignment" method="post">
                                                    <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                                                    <div class="form-group">
                                                        <label for="grade">Grade:</label>
                                                        <input type="text" class="form-control" id="grade" name="grade" value="<?= htmlspecialchars($submission['grade']) ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="feedback">Feedback:</label>
                                                        <textarea class="form-control" id="feedback" name="feedback"><?= htmlspecialchars($submission['feedback']) ?></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </form>
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
    </div>
    <?php include 'footer.html'; ?>
</div>