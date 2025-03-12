<!-- filepath: /c:/xampp/htdocs/eyebook_update/views/studentRegisterView.php -->
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

        .form-row {
            margin-left: -10px;
            margin-right: -10px;
        }

        .form-row > .col,
        .form-row > [class*="col-"] {
            padding-left: 10px;
            padding-right: 10px;
        }

        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 15px;
            color: #6c757d;
        }

        .input-icon input {
            padding-left: 40px;
        }

        @media (max-width: 768px) {
            .registration-container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2 class="form-title">Student Registration</h2>
        <p class="form-subtitle">Join our academic community and start your learning journey</p>
        
        <form action="/register_student" method="POST">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h3 class="section-title">Personal Information</h3>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="regd_no">Registration Number</label>
                            <div class="input-icon">
                                <i class="fas fa-id-card"></i>
                                <input type="text" class="form-control" id="regd_no" name="regd_no" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="name">Full Name</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="email">Email Address</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="phone">Phone Number</label>
                            <div class="input-icon">
                                <i class="fas fa-phone"></i>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information Section -->
            <div class="form-section">
                <h3 class="section-title">Academic Information</h3>
                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="section">Section</label>
                            <div class="input-icon">
                                <i class="fas fa-layer-group"></i>
                                <input type="text" class="form-control" id="section" name="section" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="stream">Stream</label>
                            <div class="input-icon">
                                <i class="fas fa-graduation-cap"></i>
                                <input type="text" class="form-control" id="stream" name="stream" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="year">Year</label>
                            <div class="input-icon">
                                <i class="fas fa-calendar-alt"></i>
                                <input type="text" class="form-control" id="year" name="year" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="required-field" for="dept">Department</label>
                            <div class="input-icon">
                                <i class="fas fa-building"></i>
                                <input type="text" class="form-control" id="dept" name="dept" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- University Information Section -->
            <div class="form-section">
                <h3 class="section-title">University Information</h3>
                <div class="form-group">
                    <label class="required-field" for="university_id">Select University</label>
                    <div class="input-icon">
                        <i class="fas fa-university"></i>
                        <select class="form-control" id="university_id" name="university_id" required>
                            <option value="">Select University</option>
                            <?php foreach ($universities as $university): ?>
                                <option value="<?php echo $university['id']; ?>">
                                    <?php echo htmlspecialchars($university['long_name']) . ' (' . htmlspecialchars($university['location']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="other">Other University</option>
                        </select>
                    </div>
                </div>

                <div id="newUniversityFields" class="hidden">
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field" for="long_name">University Full Name</label>
                                <div class="input-icon">
                                    <i class="fas fa-university"></i>
                                    <input type="text" class="form-control" id="long_name" name="long_name">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field" for="short_name">University Short Name</label>
                                <div class="input-icon">
                                    <i class="fas fa-university"></i>
                                    <input type="text" class="form-control" id="short_name" name="short_name">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field" for="location">Location</label>
                                <div class="input-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input type="text" class="form-control" id="location" name="location">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="required-field" for="country">Country</label>
                                <div class="input-icon">
                                    <i class="fas fa-globe"></i>
                                    <input type="text" class="form-control" id="country" name="country">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Section -->
            <div class="form-section">
                <h3 class="section-title">Security</h3>
                <div class="form-group">
                    <label class="required-field" for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-register">
                    <i class="fas fa-user-plus mr-2"></i>Register
                </button>
            </div>
        </form>
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
                requiredFields.forEach(field => {
                    document.getElementById(field).required = false;
                });
            }
        });
    </script>
</body>
</html>