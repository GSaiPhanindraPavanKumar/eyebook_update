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
                                        <th class="py-2 px-4 border-b">Title</th>
                                        <th class="py-2 px-4 border-b">Course</th>
                                        <th class="py-2 px-4 border-b">Deadline</th>
                                        <!-- <th class="py-2 px-4 border-b">Status</th> -->
                                        <th class="py-2 px-4 border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="assignmentTable">
                                    <?php foreach ($assignments as $assignment): ?>
                                        <tr>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['title']) ?></td>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['course_name']) ?></td>
                                            <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['deadline']) ?></td>
                                            <!-- <td class="py-2 px-4 border-b"><?= htmlspecialchars($assignment['status']) ?></td> -->
                                            <td class="py-2 px-4 border-b">
                                                <a href="/faculty/view_assignment/<?= $assignment['id'] ?>" class="text-blue-500 hover:underline">View Submissions</a>
                                                <!-- <a href="/faculty/edit_assignment/<?= $assignment['id'] ?>" class="text-blue-500 hover:underline ml-2">Edit</a> -->
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