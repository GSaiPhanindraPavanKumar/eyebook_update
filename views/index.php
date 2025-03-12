<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowbots - Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4B49AC',
                        'primary-hover': '#3f3e91',
                        'gradient-start': '#4B49AC',
                        'gradient-end': '#6366F1',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-border {
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(to right, #4B49AC, #6366F1) border-box;
            border: 2px solid transparent;
        }
        
        .bg-blur {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Decorative Elements -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-primary/5 to-gradient-end/5 rotate-12"></div>
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-gradient-start/10 rounded-full filter blur-3xl animate-float"></div>
            <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-gradient-end/10 rounded-full filter blur-3xl animate-float" style="animation-delay: 2s"></div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="fixed w-full top-0 z-50 bg-white/80 bg-blur border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="/views/public/assets/images/logo1.png" alt="Knowbots Logo" class="h-9">
                    <div>
                        <h2 class="text-2xl font-bold bg-gradient-to-r from-primary to-gradient-end bg-clip-text text-transparent">
                            Knowbots
                        </h2>
                        <p class="text-xs text-gray-500">Learning Platform</p>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-16">
        <div class="max-w-6xl w-full space-y-8 flex flex-col lg:flex-row lg:space-y-0 lg:space-x-8 items-center">
            <!-- Left side - Hero Content -->
            <div class="w-full lg:w-1/2 space-y-6 text-center lg:text-left">
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900">
                    Welcome to the Future of
                    <span class="bg-gradient-to-r from-primary to-gradient-end bg-clip-text text-transparent">
                        Learning
                    </span>
                </h1>
                <p class="text-lg text-gray-600 max-w-lg mx-auto lg:mx-0">
                    Join thousands of students in their journey of knowledge and discovery.
                </p>
                <div class="relative w-full max-w-lg mx-auto lg:mx-0">
                    <img style="border-radius: 10px;" src="https://i.ibb.co/RkFMyDw1/studentss.png" alt="Learning Illustration" 
                         class="w-full h-auto animate-float">
                </div>
            </div>

            <!-- Right side - Login Form -->
            <div class="w-full lg:w-1/2 max-w-md">
                <div class="bg-white/80 bg-blur rounded-2xl shadow-xl p-8 gradient-border">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-900">Sign In</h2>
                        <p class="mt-2 text-gray-600">Access your learning journey</p>
                    </div>

                    <?php if (!empty($warning)): ?>
                        <div class="mb-6 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-700">
                            <?php echo htmlspecialchars($warning); ?>
                        </div>
                    <?php endif; ?>

                    <form id="loginForm" action="/login" method="POST" class="space-y-6">
                        <!-- Email Input -->
                        <div class="space-y-2">
                            <label for="username" class="block text-sm font-medium text-gray-700">Email address</label>
                            <div class="relative">
                                <input type="text" id="username" name="username" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors placeholder:text-gray-400"
                                    placeholder="Enter your email">
                                <span class="absolute right-3 top-3 text-gray-400">
                                    <i class="fas fa-envelope"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors placeholder:text-gray-400"
                                    placeholder="Enter your password">
                                <button type="button" onclick="togglePasswordVisibility()" 
                                    class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Privacy Policy -->
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="agree" name="agree" required
                                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/20">
                            <label for="agree" class="text-sm text-gray-600">
                                I agree to the <a href="/#privacy-policy" class="text-primary hover:text-primary-hover font-medium">Privacy Policy</a>
                            </label>
                        </div>

                        <?php if (!empty($message)): ?>
                            <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Sign In Button -->
                        <button type="submit" name="login" id="login"
                            class="w-full py-3 px-4 rounded-lg font-medium text-white bg-gradient-to-r from-primary to-gradient-end hover:from-primary-hover hover:to-gradient-end focus:outline-none focus:ring-2 focus:ring-primary/50 transform transition-all duration-200 hover:scale-[1.02]">
                            Sign in
                        </button>

                        <!-- Forgot Password -->
                        <div class="text-center">
                            <a href="/forgot_password" 
                               class="text-sm text-gray-600 hover:text-primary transition-colors">
                                Forgot your password?
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="fixed bottom-0 w-full py-4 bg-white/80 bg-blur border-t border-gray-200/50">
        <div class="text-center">
            <p class="text-sm text-gray-600">
                Developed and maintained by
                <a href="about.html" class="font-medium text-primary hover:text-primary-hover transition-colors">
                    Phemesoft
                </a>
            </p>
        </div>
    </footer>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.fa-eye, .fa-eye-slash');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>