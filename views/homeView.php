<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
</head>
<body>
    <h1>Welcome to the Home Page</h1>
    <ul>
        <?php if (isset($students) && is_array($students)): ?>
            <?php foreach ($students as $student): ?>
                <li><?php echo htmlspecialchars($student['name']) . ' - ' . htmlspecialchars($student['email']); ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No students found.</li>
        <?php endif; ?>
    </ul>
</body>
</html>