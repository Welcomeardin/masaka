<?php
session_start();

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location:../admin/index.php');
    exit;
}

$error = $_GET['error'] ?? '';
$errorMessages = [
    'invalid' => 'Invalid session. Please login again.',
    'inactive' => 'Your account is inactive. Please contact administrator.',
];
$redirect = $_GET['redirect'] ?? '../admin/index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../assert/favicon.jpg">
    <title>Admin Login | Masaka Initiative</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-gold': '#FEB05D',
                        'dark-blue': '#2B2A2A',
                        'light-gray': '#F5F2F2'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        input:focus {
            outline: none;
            ring: 2px solid #FEB05D;
        }
        .transition-smooth {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-light-gray to-gray-100 font-sans">

    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-6xl w-full">
            
            <!-- Main Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="flex flex-col lg:flex-row">
                    
                    <!-- Left Side - Branding Section -->
                    <div class="lg:w-2/5 bg-gradient-to-br from-dark-blue to-gray-800 p-8 lg:p-10 text-white">
                        <!-- Logo -->
                        <div class="flex items-center gap-3 mb-12">
                            <div class="w-12 h-12 bg-primary-gold rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-dark-blue" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="font-bold text-xl">Masaka Initiative</div>
                                <div class="text-xs text-white/50">Admin Portal</div>
                            </div>
                        </div>

                        <!-- Welcome Message -->
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold mb-3">Welcome Back!</h1>
                            <p class="text-white/70 text-sm leading-relaxed">
                                Access the administrative dashboard to manage content, users, and monitor your organization's impact.
                            </p>
                        </div>

                        <!-- Features List -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary-gold/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-white/80">Secure Email Notifications</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary-gold/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-white/80">Real-time Analytics Dashboard</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-primary-gold/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-gold" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                    </svg>
                                </div>
                                <span class="text-sm text-white/80">Multi-location Management</span>
                            </div>
                        </div>

                        <!-- Security Badge -->
                        <div class="mt-10 pt-6 border-t border-white/10">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                </svg>
                                <span class="text-xs text-white/50">256-bit SSL Encrypted</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Login Form -->
                    <div class="lg:w-3/5 p-8 lg:p-10">
                        <div class="max-w-md mx-auto">
                            <!-- Header -->
                            <div class="text-center lg:text-left mb-8">
                                <h2 class="text-2xl font-bold text-dark-blue mb-2">Sign In</h2>
                                <p class="text-gray-500 text-sm">Enter your credentials to access your account</p>
                            </div>

                            <!-- Error Message -->
                            <?php if (!empty($error) && isset($errorMessages[$error])): ?>
                                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                        </svg>
                                        <span class="text-red-700 text-sm"><?php echo htmlspecialchars($errorMessages[$error]); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Dynamic Message Container -->
                            <div id="errorMessage" class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg hidden">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                    <span id="errorText" class="text-red-700 text-sm"></span>
                                </div>
                            </div>

                            <div id="successMessage" class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg hidden">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                    </svg>
                                    <span id="successText" class="text-green-700 text-sm"></span>
                                </div>
                            </div>

                            <!-- Login Form -->
                            <form id="loginForm" class="space-y-5">
                                <input type="hidden" name="action" value="login">
                                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

                                <!-- Email Field -->
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-dark-blue mb-2">
                                        Email Address
                                    </label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           required 
                                           placeholder="admin@example.com"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-primary-gold focus:ring-2 focus:ring-primary-gold/20 transition-all duration-300">
                                </div>

                                <!-- Password Field -->
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-dark-blue mb-2">
                                        Password
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               id="password" 
                                               name="password" 
                                               required 
                                               placeholder="Enter your password"
                                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:border-primary-gold focus:ring-2 focus:ring-primary-gold/20 transition-all duration-300">
                                        <button type="button"
                                                onclick="togglePassword()"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                            <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Remember Me & Forgot Password -->
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" 
                                               name="remember" 
                                               class="w-4 h-4 text-primary-gold border-gray-300 rounded focus:ring-primary-gold">
                                        <span class="text-sm text-gray-600">Remember me</span>
                                    </label>
                                    <a href="#" class="text-sm text-primary-gold hover:text-primary-gold/80 transition-colors">
                                        Forgot password?
                                    </a>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" 
                                        id="submitBtn"
                                        class="w-full bg-primary-gold hover:bg-primary-gold/90 text-dark-blue font-bold py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                                    Sign In
                                </button>

                                <!-- Divider -->
                                <div class="relative my-6">
                                    <div class="absolute inset-0 flex items-center">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-center text-sm">
                                        <span class="px-4 bg-white text-gray-500">Secure Access</span>
                                    </div>
                                </div>

                                <!-- Help Text -->
                                <p class="text-center text-xs text-gray-400">
                                    Need help? <a href="#" class="text-primary-gold hover:underline">Contact support</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-xs text-gray-400">
                    &copy; <?php echo date('Y'); ?> Masaka Initiative. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                passwordField.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }

        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
            document.getElementById('successMessage').classList.add('hidden');
            
            setTimeout(() => {
                errorDiv.classList.add('hidden');
            }, 5000);
        }

        // Show success message
        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            const successText = document.getElementById('successText');
            successText.textContent = message;
            successDiv.classList.remove('hidden');
            document.getElementById('errorMessage').classList.add('hidden');
        }

        // Handle form submission
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(document.getElementById('loginForm'));
            const btn = document.getElementById('submitBtn');
            const originalText = btn.textContent;

            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="inline animate-spin w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            `;

            try {
                const response = await fetch('../API/auth_handler.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server returned invalid response format');
                }

                const data = await response.json();

                if (data.success) {
                    showSuccess(data.message || 'Login successful! Redirecting...');
                    const redirect = formData.get('redirect') || '/admin/';
                    setTimeout(() => {
                        window.location.href = redirect;
                    }, 1000);
                } else {
                    showError(data.message || 'Invalid email or password. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    document.getElementById('password').value = '';
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Connection error. Please check your internet connection and try again.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });

        // Add Enter key support
        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });
    </script>
</body>
</html>