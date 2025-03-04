<?php
include 'sidebar.php';
use Models\Database;
$conn = Database::getConnection();

// Fetch only paid courses from the database
$sql = "SELECT id, name, description, price FROM courses WHERE is_paid = 1";
$stmt = $conn->query($sql);
$courses = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $courses[] = $row;
}

// No need to close the connection explicitly in PDO
?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <!-- <h3 class="font-weight-bold">Hello, <em><?php echo htmlspecialchars($userData['name']); ?></em></h3> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin">
                <p class="card-title mb-0" style="font-size:x-large">Paid Courses</p><br>
                <div class="row">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-4 d-flex align-items-stretch">
                            <div class="card mb-4" style="width: 100%;">
                                <!-- <img class="card-img-top" src="../views\public\assets\images\book.jpeg" alt="Card image cap" height="60%"> -->
                                <div class="card-body">
                                    <h5 class="card-title" style="font-family:cursive"><?php echo htmlspecialchars($course['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                                    <p class="card-text"><strong>Price:</strong> $<?php echo htmlspecialchars($course['price']); ?></p>
                                </div>
                                <div class="card-body">
                                    <a href="view_course.php?id=<?php echo $course['id']; ?>" class="card-link">View Course</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Bootstrap CSS -->