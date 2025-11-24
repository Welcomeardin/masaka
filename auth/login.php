<?php
session_start();

// Read messages
$error = $_SESSION['login_error'] ?? '';
$success = $_SESSION['login_success'] ?? '';

// Clear after reading
unset($_SESSION['login_error'], $_SESSION['login_success']);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../asserts/favicon.jpg">
    <title>Se connecter | Masaka Initiative</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #fff;
            padding: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        .log {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .log span {
            color: #16a34a;
            vertical-align: middle;
        }

        .subtitle {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
            top: 4px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #16a34a;
        }

        /* Eye icon inside password field */
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 29px;
            cursor: pointer;
            color: #666;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .options label {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .options a {
            color: #16a34a;
            text-decoration: none;
        }

        .options a:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            background: #16a34a;
            border: none;
            padding: 12px;
            color: #fff;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background: #15803d;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .footer strong {
            color: #000;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="log"><span class="material-icons">bar_chart</span>Se connecter</div>
        <div class="subtitle mt-3">Accédez à votre compte</div>
        <!-- Alert Section -->
        <?php if (!empty($error)): ?>
            <div id="alertBox" class="alert-box error mb-4" role="alert">
                <strong>Erreur :</strong>
                <span><?= htmlspecialchars($error) ?></span>
                <span class="close-alert" onclick="closeAlert()">&times;</span>
            </div>
        <?php elseif (!empty($success)): ?>
            <div id="alertBox" class="alert-box success mb-4" role="alert">
                <strong>Succès :</strong>
                <span><?= htmlspecialchars($success) ?></span>
                <span class="close-alert" onclick="closeAlert()">&times;</span>
            </div>
        <?php endif; ?>

        <script>
            function closeAlert() {
                var alertBox = document.getElementById('alertBox');
                if (alertBox) alertBox.remove();
            }

            // Auto redirect after 5 seconds for success alert
            <?php if (!empty($success)): ?>
                setTimeout(function() {
                    window.location.href = window.location.href;
                }, 5000);
            <?php endif; ?>
        </script>


        <style>
            .alert-box {
                padding: 14px 18px;
                border-radius: 3px;
                font-size: 15px;
                position: relative;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
                margin-bottom: 16px;
            }

            .alert-box.success {
                background: #d1fae5;
                border: 1px solid #10b981;
                color: #065f46;
            }

            .alert-box.error {
                background: #fee2e2;
                border: 1px solid #f87171;
                color: #b91c1c;
            }

            .alert-box.info {
                background: #dbeafe;
                border: 1px solid #3b82f6;
                color: #1e40af;
            }

            .close-alert {
                position: absolute;
                top: 12px;
                right: 12px;
                font-size: 18px;
                color: #707070ff;
                cursor: pointer;
                font-weight: bold;
                background: none;
                border: none;
                outline: none;
            }

            .close-alert:hover {
                color: #000;
            }
        </style>
        <script>
            function closeAlert() {
                var alertBox = document.getElementById('alertBox');
                if (alertBox && alertBox.parentNode) {
                    alertBox.parentNode.removeChild(alertBox);
                }
            }
        </script>


        <form id="loginForm" action="../API/login.php" method="POST" enctype="multipart/form-data">
            <?php if (!empty($redirect)): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="login_id">Identifiant ou E-mail <span style="color:red">*</span></label>
                <input type="text" name="login_id" id="login_id" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe <span style="color:red">*</span></label>
                <input type="password" id="password" name="password" required>
                <span class="material-icons toggle-password" onclick="togglePassword()">visibility</span>
            </div>

            <div class="options">
                <label><input type="checkbox"> Se souvenir de moi</label>
                <a href="#">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn">Se connecter</button>
        </form>

        <div class="footer">
            &copy; 2025 - 2025 <strong>Masaka Initiative</strong>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const toggleIcon = document.querySelector(".toggle-password");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.textContent = "visibility_off";
            } else {
                passwordField.type = "password";
                toggleIcon.textContent = "visibility";
            }
        }
    </script>

</body>

</html>