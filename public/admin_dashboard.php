<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Handle form submission (adding course)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'])) {
    $courseName = trim($_POST['course_name']);
    $courseCode = trim($_POST['course_code']);

    if ($courseName !== '') {
        $stmt = $pdo->prepare("INSERT INTO courses (name, code) VALUES (?, ?)");
        $stmt->execute([$courseName, $courseCode]);
        $message = ["✅ Course added!", "success"];
    } else {
        $message = ["❌ Course name required!", "danger"];
    }
}

// Fetch all courses
$stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="assets/style.css">

</head>
<body>

<!-- =======================
     SIDEBAR MENU
======================= -->
<div id="sidebar" class="sidebar">
    <h3 class="sidebar-title">Admin</h3>

    <a href="admin_dashboard.php" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <a href="#" class="sidebar-link">
        <i class="bi bi-journal-plus"></i> Add Courses
    </a>

    <a href="#" class="sidebar-link">
        <i class="bi bi-list-ul"></i> View Courses
    </a>

    <a href="../includes/logout.php" class="sidebar-link logout">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- Sidebar Toggle Button -->
<button id="toggle-btn" class="toggle-btn">
    <i class="bi bi-list"></i>
</button>

<!-- =======================
     MAIN CONTENT
======================= -->
<div class="container mt-5">

    <!-- PAGE TITLE -->
    <h2 class="mb-4">
        <i class="bi bi-speedometer2 text-primary"></i>
        Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Admin)
    </h2>

    <!-- Add Course Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-journal-plus"></i> Add a New Course
        </div>

        <div class="card-body">

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message[1]; ?>">
                    <?php echo htmlspecialchars($message[0]); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="course_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control">
                </div>

                <button class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Add Course
                </button>
            </form>

        </div>
    </div>

    <!-- Existing Courses -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-list-ul"></i> Existing Courses
        </div>

        <div class="card-body">

            <?php if (count($courses) === 0): ?>
                <p class="text-muted">No courses added yet.</p>
            <?php else: ?>

                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo $course['id']; ?></td>
                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                <td><?php echo htmlspecialchars($course['code']); ?></td>
                                <td><?php echo $course['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>

            <a href="../includes/export_feedback.php" class="btn btn-success mt-3">
                <i class="bi bi-download"></i> Export Feedback CSV
            </a>

        </div>
    </div>

</div>

<!-- SIDEBAR JAVASCRIPT -->
<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');

    toggleBtn.onclick = function () {
        sidebar.classList.toggle('show');
        document.body.classList.toggle('body-shift');
    };
</script>

</body>
</html>
