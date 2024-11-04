<?php
include "sidebar.php";
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

require 'zoom_integration.php';

// Load configuration
$config = require '../../src/config.php';

require_once '../../config/connection.php';

// Establish database connection
$zoom = new ZoomAPI($config['zoom']['client_id'], $config['zoom']['client_secret'], $config['zoom']['account_id'], $conn);
$allClassrooms = $zoom->getAllClassrooms();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="font-weight-bold mb-4">Student Dashboard</h2>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title">Virtual Classes</h4>
                                    <table class="table table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Topic</th>
                                                <th>Date</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Join URL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($allClassrooms as $classroom):
                                                $start_time = new DateTime($classroom['start_time'], new DateTimeZone('UTC'));
                                                $end_time = clone $start_time;
                                                $end_time->modify('+' . $classroom['duration'] . ' minutes');
                                            ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($classroom['topic']); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('Y-m-d')); ?></td>
                                                    <td><?php echo htmlspecialchars($start_time->format('H:i:s')); ?></td>
                                                    <td><?php echo htmlspecialchars($end_time->format('H:i:s')); ?></td>
                                                    <td><a href="<?php echo htmlspecialchars($classroom['join_url']); ?>" target="_blank" class="btn btn-primary">Join</a></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include('footer.html'); ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>