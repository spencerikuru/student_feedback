<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="3;url=../public/index.php">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">

    <div class="card shadow p-4 text-center" style="max-width: 400px; width: 100%;">
        <h3 class="text-success">You have been logged out</h3>
        <p class="text-muted">Thank you for using the Student Feedback System.</p>
        <p class="small text-muted">Redirecting you to the login page in 3 seconds...</p>
        <a href="../public/index.php" class="btn btn-primary mt-3">Go to Login Now</a>
    </div>

</body>
</html>
