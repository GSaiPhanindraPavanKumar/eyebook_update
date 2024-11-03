<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPOC Profile</title>
    <link rel="stylesheet" href="path/to/your/css/styles.css">
</head>
<body>
    <h1>SPOC Profile</h1>
    <?php if (isset($spoc)): ?>
        <p>Name: <?php echo htmlspecialchars($spoc['name']); ?></p>
        <p>Email: <?php echo htmlspecialchars($spoc['email']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($spoc['phone']); ?></p>
    <?php else: ?>
        <p>No SPOC data available.</p>
    <?php endif; ?>
</body>
</html>