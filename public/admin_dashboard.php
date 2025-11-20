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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Admin)</h2>
        <a href="../includes/logout.php" class="btn btn-secondary">Logout</a>
    </div>

    <!-- Add Course Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Add a New Course
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message[1]; ?>">
                    <?php echo htmlspecialchars($message[0]); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="course_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control">
                </div>
                <button class="btn btn-success">Add Course</button>
            </form>
        </div>
    </div>

    <!-- Existing Courses -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            Existing Courses
        </div>
        <div class="card-body">
            <?php if (count($courses) === 0): ?>
                <p class="text-muted">No courses have been added yet.</p>
            <?php else: ?>
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Course Name</th>
                            <th>Course Code</th>
                            <th>Created At</th>
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
            <a href="../includes/export_feedback.php" class="btn btn-success mt-3">Export Feedback to CSV</a>
        </div>
    </div>

</body>
</html>
