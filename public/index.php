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
<html>
<head>
    <link rel="stylesheet" href="assets/style.css">

    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="login-bg d-flex justify-content-center align-items-center vh-100">
  <div class="login-card p-4 shadow-lg">
    <h2 class="text-center mb-4 text-primary">Student Feedback System</h2>

    <form method="post" action="../includes/login.php">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control form-control-lg" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control form-control-lg" required>
      </div>

      <button class="btn btn-primary w-100 btn-lg">Login</button>
    </form>
  </div>
</body>

</html>
