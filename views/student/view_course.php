<?php include "sidebar.php"; ?>
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="font-weight-bold mb-4">Course Management: <?php echo htmlspecialchars($course['name'] ?? ''); ?></h2>
                    <span class="badge bg-primary text-white">Course ID: <?php echo htmlspecialchars($course['id'] ?? ''); ?></span>
                </div>
            </div>
        </div>
        <!-- Overview and Course Control Panel -->
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Course Overview</h4>
                        <!-- Course Plan Section -->
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Course Plan</h5>
                            <?php if (!empty($course['course_plan'])) : ?>
                                <button class="btn btn-primary" onclick="redirectToCoursePlan()">View</button>
                            <?php endif; ?>
                        </div>
                        <!-- Course Book Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Book</h5>
                            <?php if (!empty($course['course_book'])) : ?>
                                <button class="btn btn-primary" onclick="redirectToCourseBook()">View</button>
                            <?php endif; ?>
                        </div>
                        <!-- Course Materials Section -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Course Materials</h5>
                        </div>
                        <table class="table table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Unit Number</th>
                                    <th scope="col">Topic</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($course['course_materials'])) {
                                    foreach ($course['course_materials'] as $unit) {
                                        if (isset($unit['materials'])) {
                                            foreach ($unit['materials'] as $material) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($unit['unitNumber']) . "</td>";
                                                echo "<td>" . htmlspecialchars($unit['topic']) . "</td>";
                                                $full_url = $material['indexPath'];
                                                echo "<td><a href='#' class='btn btn-primary' onclick='redirectToCourseMaterial(\"" . htmlspecialchars($full_url) . "\")'>View</a></td>";
                                                echo "</tr>";
                                            }
                                        }
                                    }
                                } else {
                                    echo "<tr><td colspan='3'>No course materials available.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <!-- Payment Form -->
                        <form id="paymentForm">
                            <input type="hidden" id="course_id" name="course_id" value="<?php echo $course['id']; ?>">
                            <button type="button" id="payButton" class="btn btn-primary">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.html'; ?>
</div>

<!-- Include Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.getElementById('payButton').onclick = function(e) {
    var courseId = document.getElementById('course_id').value;
    var options = {
        "key": "", // Enter the Key ID generated from the Dashboard
        "amount": "<?php echo $course['price'] * 100; ?>", // Amount is in currency subunits. Default currency is INR.
        "currency": "INR",
        "name": "Course Subscription",
        "description": "Subscription for " + "<?php echo $course['name']; ?>",
        "handler": function (response){
            // Handle payment success
            fetch('payment_success.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    payment_id: response.razorpay_payment_id,
                    course_id: courseId
                })
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      alert('Payment successful! You are now subscribed to the course.');
                      window.location.reload();
                  } else {
                      alert('Payment failed. Please try again.');
                  }
              });
        },
        "prefill": {
            "name": "<?php echo $student['name']; ?>",
            "email": "<?php echo $student['email']; ?>"
        },
        "theme": {
            "color": "#3399cc"
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
    e.preventDefault();
}

function redirectToCoursePlan() {
    var coursePlan = <?php echo json_encode($course['course_plan'] ?? []); ?>;
    var baseUrl = "http://localhost/eyebook_update/"; // Replace with your actual base URL
    var coursePlanUrl = "";
    if (coursePlan && coursePlan.url) {
        coursePlanUrl = baseUrl + coursePlan.url;
    }
    if (coursePlanUrl) {
        window.open(coursePlanUrl, '_blank');
    } else {
        alert('Course Plan URL not available.');
    }
}
function redirectToCourseBook() {
    var courseMaterials = <?php echo json_encode($course['course_book'] ?? []); ?>;
    var baseUrl = "http://localhost/eyebook_update/"; // Replace with your actual base URL
    var courseBookUrl = "";
    if (courseMaterials.length > 0 && courseMaterials[0].materials.length > 0) {
        courseBookUrl = baseUrl + courseMaterials[0].materials[0].indexPath;
    }
    if (courseBookUrl) {
        window.open(courseBookUrl, '_blank');
    } else {
        alert('Course Book URL not available.');
    }
}
function redirectToCourseMaterial(url) {
    var baseUrl = "http://localhost/eyebook_update/"; // Replace with your actual base URL
    var courseMaterialUrl = baseUrl + url;
    if (url) {
        window.open(courseMaterialUrl, '_blank');
    } else {
        alert('Course Material URL not available.');
    }
}
</script>