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
    <title>Login | Masaka Initiative Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --cream: #F5F2F2;
            --orange: #FEB05D;
            --blue: #5A7ACD;
            --dark: #2B2A2A;
            --dark-80: rgba(43, 42, 42, 0.8);
            --dark-12: rgba(43, 42, 42, 0.12);
            --dark-06: rgba(43, 42, 42, 0.06);
            --orange-light: rgba(254, 176, 93, 0.15);
            --blue-light: rgba(90, 122, 205, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--cream);
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }

        /* ── LEFT PANEL (60%) ── */
        .left-panel {
            width: 60%;
            background-color: var(--dark);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .left-panel::before {
            content: '';
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            border: 80px solid rgba(254, 176, 93, 0.08);
            top: -120px;
            right: -100px;
            pointer-events: none;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            border: 60px solid rgba(90, 122, 205, 0.1);
            bottom: -80px;
            left: -60px;
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 1;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: var(--orange);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon .material-icons {
            color: var(--dark);
            font-size: 22px;
        }

        .brand-name {
            font-family: 'Sora', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .brand-name span {
            color: var(--orange);
        }

        /* Hero content */
        .hero {
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(254, 176, 93, 0.12);
            border: 1px solid rgba(254, 176, 93, 0.25);
            color: var(--orange);
            font-size: 12px;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 100px;
            margin-bottom: 24px;
            letter-spacing: 0.5px;
        }

        .hero-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--orange);
            border-radius: 50%;
        }

        .hero h1 {
            font-family: 'Sora', sans-serif;
            font-size: 42px;
            font-weight: 700;
            color: #fff;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 16px;
        }

        .hero h1 em {
            font-style: normal;
            color: var(--orange);
        }

        .hero p {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.7;
            max-width: 380px;
        }

        /* Stats row */
        .stats {
            display: flex;
            gap: 32px;
            z-index: 1;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .stat-value {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }

        .stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 0.3px;
        }

        .stat-divider {
            width: 1px;
            background: rgba(255, 255, 255, 0.1);
            align-self: stretch;
        }

        /* ── RIGHT PANEL (40%) ── */
        .right-panel {
            width: 40%;
            background-color: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px;
        }

        .login-box {
            width: 100%;
            max-width: 360px;
        }

        .login-title {
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 14px;
            color: var(--dark-80);
            margin-bottom: 36px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
            letter-spacing: 0.1px;
        }

        .form-group input {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid var(--dark-12);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            background: #fff;
            color: var(--dark);
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-group input::placeholder {
            color: rgba(43, 42, 42, 0.3);
        }

        .form-group input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(90, 122, 205, 0.1);
        }

        .password-toggle {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 40px;
            cursor: pointer;
            color: rgba(43, 42, 42, 0.35);
            font-size: 18px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--dark-80);
        }

        /* Alerts */
        .error-message {
            background: #FFF0F0;
            color: #C0392B;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid #E74C3C;
            font-size: 13px;
        }

        .error-message.hidden {
            display: none;
        }

        .alert {
            background: #F0FFF6;
            color: #1A7A44;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 3px solid #2ECC71;
            font-size: 13px;
        }

        .alert.hidden {
            display: none;
        }

        /* Options row */
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .options label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: var(--dark-80);
            cursor: pointer;
        }

        .options input[type="checkbox"] {
            accent-color: var(--blue);
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        /* Submit button */
        .btn {
            width: 100%;
            background: var(--blue);
            border: none;
            padding: 14px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Sora', sans-serif;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: -0.2px;
            transition: background 0.2s, transform 0.15s;
        }

        .btn:hover {
            background: #4A6BBD;
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
            background: #3D5BAD;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Accent strip below button */
        .accent-strip {
            margin-top: 12px;
            height: 3px;
            border-radius: 100px;
            background: linear-gradient(90deg, var(--orange) 0%, var(--blue) 100%);
            opacity: 0.35;
        }

        /* Footer */
        .footer {
            margin-top: 28px;
            font-size: 12px;
            color: rgba(43, 42, 42, 0.4);
            text-align: center;
        }

        .footer strong {
            color: var(--dark-80);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
                overflow: auto;
            }

            .left-panel {
                width: 100%;
                padding: 32px 28px;
                min-height: 240px;
            }

            .left-panel::before {
                width: 280px;
                height: 280px;
            }

            .hero h1 {
                font-size: 28px;
            }

            .stats {
                gap: 20px;
            }

            .right-panel {
                width: 100%;
                padding: 36px 24px;
            }
        }
    </style>
</head>

<body>

    <!-- LEFT PANEL — 60% -->
    <div class="left-panel">
        <div class="brand">
            <div class="brand-icon">
                <span class="material-icons">dashboard</span>
            </div>
            <div class="brand-name">Masaka <span>Admin</span></div>
        </div>

        <div class="hero">
            <div class="hero-badge">Admin Dashboard</div>
            <h1>Manage with<br><em>confidence.</em></h1>
            <p>Secure, streamlined access to the Masaka Initiative control panel. Everything you need, right where you need it.</p>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-value">100%</div>
                <div class="stat-label">Secure login</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Availability</div>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <div class="stat-value">v2.0</div>
                <div class="stat-label">Dashboard</div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL — 40% -->
    <div class="right-panel">
        <div class="login-box">
            <h2 class="login-title">Welcome back</h2>
            <p class="login-subtitle">Sign in to your admin account</p>

            <div id="errorMessage" class="error-message hidden">
                <strong>Error:</strong> <span id="errorText"></span>
            </div>

            <div id="successMessage" class="alert hidden">
                <strong>Success:</strong> <span id="successText"></span>
            </div>

            <form id="loginForm">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="admin@example.com">
                </div>

                <div class="form-group password-toggle">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Your password">
                    <span class="material-icons toggle-password" onclick="togglePassword()">visibility</span>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>

                <button type="submit" class="btn" id="submitBtn">Sign In</button>
                <div class="accent-strip"></div>
            </form>

            <div class="footer">
                &copy; 2025 <strong>Masaka Initiative</strong>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const icon = event.target;
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordField.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');

            // Hide success message if visible
            document.getElementById('successMessage').classList.add('hidden');

            // Auto hide after 5 seconds
            setTimeout(() => {
                errorDiv.classList.add('hidden');
            }, 5000);
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('successMessage');
            const successText = document.getElementById('successText');
            successText.textContent = message;
            successDiv.classList.remove('hidden');

            // Hide error message if visible
            document.getElementById('errorMessage').classList.add('hidden');
        }

        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(document.getElementById('loginForm'));
            const btn = document.getElementById('submitBtn');
            const originalText = btn.textContent;

            // Disable button and show loading state
            btn.disabled = true;
            btn.textContent = 'Signing in...';

            try {
                const response = await fetch('../API/auth_handler.php', {
                    method: 'POST',
                    body: formData
                });

                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Invalid response format:', text);
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
                    showError(data.message || 'Login failed. Please try again.');
                    btn.disabled = false;
                    btn.textContent = originalText;

                    // Clear password field for security
                    document.getElementById('password').value = '';
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Connection error: ' + error.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        // Optional: Add demo credentials hint (remove in production)
        console.log('Demo credentials: admin@example.com / password');
    </script>
</body>

</html>