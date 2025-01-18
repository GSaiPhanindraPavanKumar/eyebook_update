<?php 
include 'sidebar.php'; 
use Models\University;
use Models\Contest;
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <h3 class="font-weight-bold">Contest Details</h3>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($contest['title']); ?></h5>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($contest['description']); ?></p>
                        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($contest['start_date']); ?></p>
                        <p><strong>End Date:</strong> <?php echo htmlspecialchars($contest['end_date']); ?></p>
                        <p><strong>Universities:</strong> 
                            <?php 
                            $university_ids = json_decode($contest['university_id'], true);
                            foreach ($university_ids as $university_id) {
                                $university = University::getById($conn, $university_id);
                                echo htmlspecialchars($university['long_name']) . '<br>';
                            }
                            ?>
                        </p>
                        <a href="/admin/edit_contest/<?php echo $contest['id']; ?>" class="btn btn-warning">Edit Contest</a>
                        <a href="/admin/delete_contest/<?php echo $contest['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contest?');">Delete Contest</a>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h3 class="font-weight-bold">Questions</h3>
                    <a href="/admin/add_questions/<?php echo $contest['id']; ?>" class="btn btn-primary">Add Question</a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Grade</th>
                                    <th>No of Submissions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($questions)): 
                                    foreach ($questions as $question): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($question['question']); ?></td>
                                            <td><?php echo htmlspecialchars($question['grade']); ?></td>
                                            <td><?php echo htmlspecialchars($question['submission_count']); ?></td>
                                            <td>
                                                <a href="/admin/view_question/<?php echo $question['id']; ?>" class="btn btn-info">View</a>
                                                <a href="/admin/edit_question/<?php echo $question['id']; ?>" class="btn btn-warning">Edit</a>
                                                <a href="/admin/delete_question/<?php echo $question['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; 
                                else: ?>
                                    <tr>
                                        <td colspan="4">No questions found for this contest.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h3 class="font-weight-bold">Leaderboard</h3>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Total Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($leaderboard)): 
                                    $rank = 1;
                                    foreach ($leaderboard as $entry): ?>
                                        <tr>
                                            <td><?php echo $rank++; ?></td>
                                            <td><?php echo htmlspecialchars($entry['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($entry['total_grade']); ?></td>
                                        </tr>
                                    <?php endforeach; 
                                else: ?>
                                    <tr>
                                        <td colspan="3">No submissions found for this contest.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>