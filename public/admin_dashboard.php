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
    <link rel="stylesheet" href="assets/style.css?v=3">
</head>

<body>

<!-- =======================
     SIDEBAR MENU
======================= -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
        <div class="sidebar-brand-text">
            <h3 class="sidebar-title mb-0">Admin</h3>
            <p class="sidebar-subtitle mb-0">Control Center</p>
        </div>
    </div>

    <a href="admin_dashboard.php" class="sidebar-link active">
        <i class="bi bi-grid"></i><span>Dashboard</span>
    </a>

    <a href="../includes/logout.php" class="sidebar-link logout">
        <i class="bi bi-box-arrow-right"></i><span>Logout</span>
    </a>
</div>

<!-- Sidebar Toggle Button -->
<button id="toggle-btn" class="toggle-btn">
    <i class="bi bi-list"></i>
</button>

<!-- =======================
     MAIN CONTENT
======================= -->
<div class="container-fluid main-content">

    <div class="dashboard-hero mb-4">
        <div class="dashboard-hero-content">
            <div>
                <span class="hero-badge">Administrator Workspace</span>
                <h2 class="hero-title mt-3 mb-2">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </h2>
                <p class="hero-subtitle mb-0">
                    Manage courses, update evaluation deadlines, and export feedback records from the platform.
                </p>
            </div>
            <div class="hero-icon">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
    </div>

    <!-- Message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message[1]; ?> modern-alert">
            <?php echo htmlspecialchars($message[0]); ?>
        </div>
    <?php endif; ?>

    <div class="section-head mb-4">
        <div>
            <h4 class="mb-1">Administrative Controls</h4>
            <p class="section-subtext mb-0">Create new courses, manage deadlines, and review current course records.</p>
        </div>
        <span class="section-chip">Admin Panel</span>
    </div>

    <!-- Add Course Form -->
    <div class="card p-4 mb-4 course-card">
        <div class="course-card-header mb-3">
            <div>
                <h4 class="course-title mb-1">Add a New Course</h4>
                <p class="course-code mb-0">Create a course record and optionally assign a feedback deadline.</p>
            </div>
            <div class="deadline-pill">
                <i class="bi bi-plus-circle"></i>
                <span>Course Setup</span>
            </div>
        </div>

        <form method="post">
            <input type="hidden" name="add_course" value="1">

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Course Name</label>
                    <input type="text" name="course_name" class="form-control modern-input" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Course Code</label>
                    <input type="text" name="course_code" class="form-control modern-input">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" class="form-control modern-input">
                </div>
            </div>

            <button class="btn btn-success submit-btn">
                <i class="bi bi-check-circle"></i> Add Course
            </button>
        </form>
    </div>

    <!-- Existing Courses -->
    <div class="card p-4 course-card">
        <div class="course-card-header mb-3">
            <div>
                <h4 class="course-title mb-1">Existing Courses</h4>
                <p class="course-code mb-0">View existing course records and update evaluation deadlines.</p>
            </div>
            <div class="deadline-pill">
                <i class="bi bi-table"></i>
                <span>Course Records</span>
            </div>
        </div>

        <?php if (count($courses) === 0): ?>
            <p class="text-muted">No courses added yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle modern-table">
                    <thead>
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
                            <td><?php echo $course['deadline'] ? $course['deadline'] : '—'; ?></td>
                            <td>
                                <form method="post" class="d-flex flex-wrap gap-2 align-items-center">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="update_deadline" value="1">

                                    <input type="date"
                                           name="new_deadline"
                                           value="<?php echo $course['deadline']; ?>"
                                           class="form-control form-control-sm modern-input admin-deadline-input">

                                    <button class="btn btn-sm btn-primary">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="../includes/export_feedback.php" class="btn btn-success submit-btn">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Feedback to CSV
            </a>
        </div>
    </div>

</div>

<!-- Sidebar JS -->
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