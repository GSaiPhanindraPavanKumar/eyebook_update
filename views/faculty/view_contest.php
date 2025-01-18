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
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h3 class="font-weight-bold">Questions</h3>
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
                                                <a href="/faculty/view_question/<?php echo $question['id']; ?>" class="btn btn-info">View</a>
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