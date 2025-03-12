<?php 
include 'sidebar.php'; 
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Manage Transactions</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card shadow">
                    <div class="card-body">
                        <p class="card-title mb-0" style="font-size:larger">Transactions</p><br>

                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Transaction ID</th>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
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