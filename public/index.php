<?php
session_start();
if (isset($_SESSION['user_id'])) {
    // If already logged in, send to dashboard
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($_SESSION['role'] === 'professor') {
        header("Location: professor_dashboard.php");
        exit;
    } else {
        header("Location: student_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="login-bg">
    <div class="login-page-wrapper">
        <div class="login-left-panel">
            <div class="login-brand-box">
                <div class="login-icon-circle">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1 class="login-system-title">Student Feedback System</h1>
                <p class="login-system-text">
                    A secure platform for students to evaluate courses, support teaching improvement,
                    and strengthen academic quality assurance.
                </p>
            </div>
        </div>

        <div class="login-right-panel">
            <div class="login-card p-4 shadow-lg">
                <div class="text-center mb-4">
                    <h2 class="login-title">Welcome Back</h2>
                    <p class="login-subtitle">Please sign in to continue</p>
                </div>

                <form method="post" action="../includes/login.php">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" class="form-control form-control-lg" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 btn-lg login-btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>