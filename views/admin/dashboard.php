<?php
include("sidebar.php");
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($user['name']); ?></em></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 grid-margin stretch-card">
                <div class="card tale-bg">
                    <canvas id="myPieChart" width="200" height="150" style="width: 200px; height: 150px;"></canvas>
                </div>
            </div>
            <div class="col-md-6 grid-margin transparent">
                <div class="row">
                    <div class="col-md-6 mb-4 stretch-card transparent">
                        <div class="card card-tale">
                            <div class="card-body">
                                <p class="mb-4">Total Universities</p>
                                <p class="fs-30 mb-2"><?php echo $university_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4 stretch-card transparent">
                        <div class="card card-dark-blue">
                            <div class="card-body">
                                <p class="mb-4">Total Students</p>
                                <p class="fs-30 mb-2"><?php echo $student_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
                        <div class="card card-light-blue">
                            <div class="card-body">
                                <p class="mb-4">SPOCs</p>
                                <p class="fs-30 mb-2"><?php echo $spoc_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 stretch-card transparent">
                        <div class="card card-light-danger">
                            <div class="card-body">
                                <p class="mb-4">Courses</p>
                                <p class="fs-30 mb-2"><?php echo $course_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
                        <div class="card card-light-blue">
                            <div class="card-body">
                                <p class="mb-4">Transactions</p>
                                <p class="fs-30 mb-2">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 stretch-card transparent">
                        <div class="card card-light-danger">
                            <div class="card-body">
                                <p class="mb-4">Users</p>
                                <p class="fs-30 mb-2">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title mb-0">Universities</p>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th class="pl-0 pb-2 border-bottom">University Name</th>
                                        <th class="border-bottom pb-2">Short Name</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($universities)) {
                                        foreach ($universities as $row) {
                                            echo "<tr>";
                                            echo "<td class='pl-0'>" . htmlspecialchars($row['long_name']) . "</td>";
                                            echo "<td><p class='mb-0'><span class='font-weight-bold mr-2'>" . htmlspecialchars($row['short_name']) . "</span></p></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2'>No data available</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="row">
                    <div class="col-md-12 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <p class="card-title">User Usage Chart</p>
                                <select id="timeRange" class="form-control mb-3">
                                    <option value="today">Today</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                    <option value="year">This Year</option>
                                </select>
                                <canvas id="userUsageChart" width="350" height="200"></canvas>
                                <a href="/admin/download_usage_report" class="btn btn-secondary mt-3">Download Detailed Report</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 stretch-card grid-margin grid-margin-md-0">
                        <div class="card data-icon-card-primary">
                            <div class="card-body">
                                <p class="card-title text-white">Number of Meetings</p>
                                <div class="row">
                                    <div class="col-8 text-white">
                                        <h3>
                                            <?php echo $meeting_count; ?>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card">
                    <div class="card-body">
                        <p class="card-title">Courses</p>
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th class="pl-0 pb-2 border-bottom">Course Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($courses)) {
                                    foreach ($courses as $row) {
                                        echo "<tr>";
                                        echo "<td class='pl-0'>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='1'>No courses available</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('myPieChart').getContext('2d');
    var myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Universities', 'Students', 'SPOCs', 'Courses'],
            datasets: [{
                data: [<?php echo $university_count; ?>, <?php echo $student_count; ?>, <?php echo $spoc_count; ?>, <?php echo $course_count; ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Distribution of Entities'
                }
            }
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('userUsageChart').getContext('2d');
    var userUsageChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_keys($usageData)); ?>, // e.g., ['Admin', 'SPOC', 'Faculty', 'Student']
            datasets: [{
                label: 'User Usage',
                data: <?php echo json_encode(array_values($usageData)); ?>, // e.g., [10, 20, 30, 40]
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'User Usage'
                }
            }
        }
    });

    document.getElementById('timeRange').addEventListener('change', function() {
        var timeRange = this.value;
        fetch('/admin/getUsageData?timeRange=' + timeRange)
            .then(response => response.json())
            .then(data => {
                userUsageChart.data.labels = data.labels;
                userUsageChart.data.datasets[0].data = data.data;
                userUsageChart.update();
            });
    });
});
</script>