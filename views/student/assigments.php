<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Assignments</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <style>
    /* Base styles */
    body {
      font-family: 'Helvetica Neue', Arial, sans-serif;
      margin: 20px;
      background-color: #e9ecef; /* Soft gray background */
    }

    h2 {
      text-align: center;
      color: #343a40; /* Darker gray for header */
      margin-bottom: 20px;
      font-size: 2rem;
    }

    /* Card styles */
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Soft shadow */
      margin-bottom: 20px;
      background-color: #ffffff; /* White background for cards */
    }

    .card-header {
      background-color: #007bff; /* Bootstrap primary color */
      color: #ffffff;
      font-weight: bold;
      border-top-left-radius: 10px;
      border-top-right-radius: 10px;
      padding: 15px; /* Added padding */
    }

    /* Table styles */
    table {
      width: 100%;
      font-size: 14px;
      margin: 0;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border: none; /* No borders for a cleaner look */
    }

    th {
      background-color: #f8f9fa; /* Light gray for header row */
      color: #495057; /* Darker text for contrast */
    }

    /* Buttons */
    .submit-btn {
      background-color: #28a745; /* Green button */
      color: #fff;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
    }

    .submit-btn:hover {
      background-color: #218838; /* Darker green on hover */
    }

    .view-button {
      background-color: #007bff;
      color: #fff;
      border: none;
      padding: 8px 12px;
      cursor: pointer;
      border-radius: 5px;
      transition: background-color 0.3s;
      margin-bottom: 10px; /* Spacing for the button */
    }

    .view-button:hover {
      background-color: #0056b3; /* Darker blue on hover */
    }

    /* Iframe styles */
    iframe {
      display: none; /* Hide by default */
      width: 100%;
      height: 300px; /* Fixed height for iframes */
      border: none;
      margin-top: 10px;
      border-radius: 5px; /* Rounded corners */
    }

    /* Responsive styles */
    @media (max-width: 768px) {
      .card {
        margin-bottom: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Your Assignments</h2>
    <div class="row">
      <?php
      include('sidebar.php');

      use Models\Database;
      $conn = Database::getConnection();

      // Check connection
      if (!$conn) {
        die("Connection failed: " . $conn->errorInfo());
      }

      // SQL query to fetch all assignments
      $sql = "SELECT * FROM assignments";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (count($result) > 0) {
        foreach ($result as $row) {
          echo '<div class="col-md-6 col-lg-4">'; // Responsive column sizes
          echo '<div class="card">';
          echo '<div class="card-header">' . htmlspecialchars($row['title']) . '</div>';
          echo '<div class="card-body">';
          echo '<p>' . htmlspecialchars($row['instructions']) . '</p>';
          echo '<p><strong>Due Date:</strong> ' . htmlspecialchars($row['deadline']) . '</p>';
          
          $basePath = 'http://localhost/new/eyebook_update/views/faculty/';
          $filePath = htmlspecialchars($row['file_path']);
          $fullPath = $basePath . $filePath;
          
          echo '<button class="view-button">View</button>';
          echo '<iframe src="' . $fullPath . '"></iframe>';
          echo '<form action="submit_assignment.php" method="post" enctype="multipart/form-data" style="margin-top: 10px;">';
          echo '<input type="hidden" name="assignment_id" value="' . $row['id'] . '">';
          echo '<input type="file" name="submission_file" required class="form-control">'; // Added Bootstrap styling
          echo '<button type="submit" class="submit-btn">Submit</button>';
          echo '</form>';
          echo '</div>';
          echo '</div>';
          echo '</div>'; // Closing column div
        }
      } else {
        echo '<p class="text-center">No assignments found.</p>';
      }

      $conn = null;
      ?>
    </div> <!-- Closing row div -->
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function openFullscreen(iframe) {
      if (iframe.requestFullscreen) {
        iframe.requestFullscreen();
      } else if (iframe.webkitRequestFullscreen) { /* Chrome, Safari */
        iframe.webkitRequestFullscreen();
      } else if (iframe.mozRequestFullScreen) { /* Firefox */
        iframe.mozRequestFullScreen();
      }
    }

    // Add event listener to the "View" button
    document.querySelectorAll(".view-button").forEach(button => {
      button.addEventListener("click", () => {
        const iframe = button.nextElementSibling; // Get the next iframe
        iframe.style.display = iframe.style.display === 'block' ? 'none' : 'block'; // Toggle iframe visibility
        openFullscreen(iframe);
      });
    });
  </script>
</body>
</html>