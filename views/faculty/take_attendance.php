<?php
require_once __DIR__ . '/../../models/database.php';
use Models\Database;
use Models\Student;
use Models\VirtualClassroom;

$conn = Database::getConnection();

$classroomId = $_GET['classroom_id'] ?? null;

if ($classroomId) {
    // Fetch classroom details
    $stmt = $conn->prepare("SELECT * FROM virtual_classrooms WHERE classroom_id = ?");
    $stmt->execute([$classroomId]);
    $classroom = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch all students
    $students = Student::getAll($conn);
} else {
    echo "Error: Classroom ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Take Attendance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .toggle-button {
            display: inline-block;
            width: 60px;
            height: 30px;
            background-color: red;
            border-radius: 15px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .toggle-button.present {
            background-color: green;
        }
        .toggle-button::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 5px;
            width: 20px;
            height: 20px;
            background-color: white;
            border-radius: 50%;
            transition: left 0.3s;
        }
        .toggle-button.present::before {
            left: 35px;
        }
        .toggle-button::after {
            content: 'A';
            position: absolute;
            left: 9px;
            top: 2px;
            color: black;
            font-weight: bold;
            transition: left 0.3s;
        }
        .toggle-button.present::after {
            content: 'P';
            left: 40px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Classroom Details</h2>
                <p><strong>Topic:</strong> <?php echo htmlspecialchars($classroom['topic']); ?></p>
                <p><strong>Start Time:</strong> <?php echo htmlspecialchars($classroom['start_time']); ?></p>
                <p><strong>Duration:</strong> <?php echo htmlspecialchars($classroom['duration']); ?> minutes</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Take Attendance</h2>
                <form method="POST" action="/faculty/save_attendance">
                    <input type="hidden" name="classroom_id" value="<?php echo htmlspecialchars($classroomId); ?>">
                    <table class="table table-hover mt-4">
                        <thead class="thead-dark">
                            <tr>
                                <th>Student Name</th>
                                <th>Registration Number</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['regd_no']); ?></td>
                                    <td>
                                        <div class="toggle-button" onclick="toggleAttendance(this)">
                                            <input type="hidden" name="attendance[<?php echo $student['id']; ?>]" value="absent">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success mt-4">Save Attendance</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleAttendance(element) {
            element.classList.toggle('present');
            var input = element.querySelector('input');
            if (element.classList.contains('present')) {
                input.value = 'present';
            } else {
                input.value = 'absent';
            }
        }
    </script>
</body>
</html>