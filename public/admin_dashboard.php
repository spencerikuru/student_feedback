<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = "";

// Handle new course submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $courseName = trim($_POST['course_name']);
    $courseCode = trim($_POST['course_code']);
    $deadline = $_POST['deadline'] ?: null; // allow NULL

    if ($courseName !== '') {
        $stmt = $pdo->prepare("
            INSERT INTO courses (name, code, deadline)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$courseName, $courseCode, $deadline]);

        $message = ["✅ Course added successfully!", "success"];
    } else {
        $message = ["❌ Course name is required.", "danger"];
    }
}

// Handle deadline update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_deadline'])) {
    $courseId = intval($_POST['course_id']);
    $newDeadline = $_POST['new_deadline'] ?: null;

    $stmt = $pdo->prepare("UPDATE courses SET deadline = ? WHERE id = ?");
    $stmt->execute([$newDeadline, $courseId]);

    $message = ["🕒 Deadline updated successfully!", "info"];
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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/style.css">
</head>

<body class="container mt-5">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Admin)</h2>
        <a href="../includes/logout.php" class="btn btn-secondary">Logout</a>
    </div>

    <!-- Message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message[1]; ?>">
            <?php echo htmlspecialchars($message[0]); ?>
        </div>
    <?php endif; ?>

    <!-- Add Course Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-plus-circle"></i> Add a New Course
        </div>

        <div class="card-body">
            <form method="post">
                <input type="hidden" name="add_course" value="1">

                <div class="mb-3">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="course_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control">
                </div>

                <!-- NEW: Deadline date picker -->
                <div class="mb-3">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" class="form-control">
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
            <i class="bi bi-table"></i> Existing Courses
        </div>

        <div class="card-body">

            <?php if (count($courses) === 0): ?>
                <p class="text-muted">No courses added yet.</p>

            <?php else: ?>

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Created</th>
                            <th>Deadline</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo htmlspecialchars($course['name']); ?></td>
                            <td><?php echo htmlspecialchars($course['code']); ?></td>
                            <td><?php echo $course['created_at']; ?></td>

                            <td>
                                <?php echo $course['deadline'] ? $course['deadline'] : '—'; ?>
                            </td>

                            <td>
                                <!-- Update Deadline Form -->
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="update_deadline" value="1">

                                    <input type="date" name="new_deadline"
                                           value="<?php echo $course['deadline']; ?>"
                                           class="form-control form-control-sm me-2">

                                    <button class="btn btn-sm btn-primary">
                                        Update
                                    </button>
                                </form>
                            </td>

                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>

            <?php endif; ?>

            <a href="../includes/export_feedback.php" class="btn btn-success mt-3">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Feedback to CSV
            </a>

        </div>
    </div>

</body>
</html>
