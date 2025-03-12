<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
        }

        .registration-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            text-align: center;
            color: #4B49AC;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .section-title {
            color: #4B49AC;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4B49AC;
        }

        .form-group label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px 15px;
            height: auto;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4B49AC;
            box-shadow: 0 0 0 0.2rem rgba(75, 73, 172, 0.25);
        }

        .btn-register {
            background-color: #4B49AC;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            background-color: #3f3e8e;
            transform: translateY(-1px);
        }

        .hidden {
            display: none;
        }
        .required-field::after {
            content: " *";
            color: red;
            font-weight: bold;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-header {
            margin-bottom: 25px;
            text-align: center;
        }
        .note {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="form-container">
            <div class="form-header">
                <h2>Student Registration</h2>
                <p class="note">Fields marked with <span style="color: red; font-weight: bold;">*</span> are required</p>
            </div>
            
            <?php if(isset($errorMessage)): ?>
                <div class="alert alert-danger"><?= $errorMessage ?></div>
            <?php endif; ?>
            
            <form action="/register_student" method="POST" novalidate>
                <div class="form-group">
                    <label for="regd_no" class="required-field">Username/Registration Number:</label>
                    <input type="text" class="form-control" id="regd_no" name="regd_no" required>
                </div>
                <div class="form-group">
                    <label for="name" class="required-field">Name:</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email" class="required-field">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone" class="required-field">Phone:</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="section">Section:</label>
                    <input type="text" class="form-control" id="section" name="section">
                </div>
                <div class="form-group">
                    <label for="stream">Stream:</label>
                    <input type="text" class="form-control" id="stream" name="stream">
                </div>
                <div class="form-group">
                    <label for="year">Year:</label>
                    <input type="number" class="form-control" id="year" name="year" min="1" max="6" placeholder="Enter academic year (1-6)">
                    <small class="form-text text-muted">Enter a number between 1 and 6</small>
                </div>
                <div class="form-group">
                    <label for="dept">Department:</label>
                    <input type="text" class="form-control" id="dept" name="dept">
                </div>
                <div class="form-group">
                    <label for="university_id" class="required-field">University:</label>
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
                        <label for="long_name" class="required-field">University Long Name:</label>
                        <input type="text" class="form-control" id="long_name" name="long_name">
                    </div>
                    <div class="form-group">
                        <label for="short_name" class="required-field">University Short Name:</label>
                        <input type="text" class="form-control" id="short_name" name="short_name">
                    </div>
                    <div class="form-group">
                        <label for="location" class="required-field">Location:</label>
                        <input type="text" class="form-control" id="location" name="location">
                    </div>
                    <div class="form-group">
                        <label for="country" class="required-field">Country:</label>
                        <input type="text" class="form-control" id="country" name="country">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="required-field">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="form-text text-muted">Choose a strong password</small>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <a href="/login" class="btn btn-outline-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('university_id').addEventListener('change', function() {
            var newUniversityFields = document.getElementById('newUniversityFields');
            var requiredFields = ['long_name', 'short_name', 'location', 'country'];
            
            if (this.value === 'other') {
                newUniversityFields.classList.remove('hidden');
                requiredFields.forEach(field => {
                    document.getElementById(field).required = true;
                });
            } else {
                newUniversityFields.classList.add('hidden');
                document.getElementById('long_name').required = false;
                document.getElementById('short_name').required = false;
                document.getElementById('location').required = false;
                document.getElementById('country').required = false;
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            var form = this;
            var isValid = true;
            
            // Check all required fields
            form.querySelectorAll('[required]').forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // Validate email format
            var email = document.getElementById('email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.value && !emailPattern.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>