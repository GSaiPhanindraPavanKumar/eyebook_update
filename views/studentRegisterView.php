<!-- filepath: /c:/xampp/htdocs/eyebook_update/views/studentRegisterView.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Student Registration</h2>
        <form action="/register_student" method="POST">
            <div class="form-group">
                <label for="regd_no">User Name/Registration Number:</label>
                <input type="text" class="form-control" id="regd_no" name="regd_no" required>
            </div>
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="section">Section:</label>
                <input type="text" class="form-control" id="section" name="section" required>
            </div>
            <div class="form-group">
                <label for="stream">Stream:</label>
                <input type="text" class="form-control" id="stream" name="stream" required>
            </div>
            <div class="form-group">
                <label for="year">Year:</label>
                <input type="text" class="form-control" id="year" name="year" required>
            </div>
            <div class="form-group">
                <label for="dept">Department:</label>
                <input type="text" class="form-control" id="dept" name="dept" required>
            </div>
            <div class="form-group">
                <label for="university_id">University:</label>
                <select class="form-control" id="university_id" name="university_id" required>
                    <option value="">Select University</option>
                    <?php foreach ($universities as $university): ?>
                        <option value="<?php echo $university['id']; ?>"><?php echo htmlspecialchars($university['long_name']) . ' (' . htmlspecialchars($university['location']) . ')'; ?></option>
                    <?php endforeach; ?>
                    <option value="other">Other</option>
                </select>
            </div>
            <div id="newUniversityFields" class="hidden">
                <div class="form-group">
                    <label for="long_name">University Long Name:</label>
                    <input type="text" class="form-control" id="long_name" name="long_name">
                </div>
                <div class="form-group">
                    <label for="short_name">University Short Name:</label>
                    <input type="text" class="form-control" id="short_name" name="short_name">
                </div>
                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" class="form-control" id="location" name="location">
                </div>
                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" class="form-control" id="country" name="country">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <script>
        document.getElementById('university_id').addEventListener('change', function() {
            var newUniversityFields = document.getElementById('newUniversityFields');
            if (this.value === 'other') {
                newUniversityFields.classList.remove('hidden');
                document.getElementById('long_name').required = true;
                document.getElementById('short_name').required = true;
                document.getElementById('location').required = true;
                document.getElementById('country').required = true;
            } else {
                newUniversityFields.classList.add('hidden');
                document.getElementById('long_name').required = false;
                document.getElementById('short_name').required = false;
                document.getElementById('location').required = false;
                document.getElementById('country').required = false;
            }
        });
    </script>
</body>
</html>