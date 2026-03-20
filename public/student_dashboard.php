<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$message = "";

// If student submits feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {

    $courseId = intval($_POST['course_id']);
    $ratingOverall = intval($_POST['rating_overall']);
    $ratingTeaching = intval($_POST['rating_teaching']);
    $ratingMaterial = intval($_POST['rating_material']);
    $comment = trim($_POST['comment']);

    // Fetch deadline
    $stmt = $pdo->prepare("SELECT deadline FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $deadline = $stmt->fetchColumn();

    // Check if deadline exists AND is past
    if ($deadline && strtotime($deadline) < time()) {
        $message = ["⚠️ Deadline has passed. You cannot submit feedback.", "danger"];
    } else {
        // Insert feedback (multiple submissions allowed)
        $stmt = $pdo->prepare("
            INSERT INTO feedback (course_id, rating_overall, rating_teaching, rating_material, comment)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$courseId, $ratingOverall, $ratingTeaching, $ratingMaterial, $comment]);

        $message = ["✅ Feedback submitted successfully!", "success"];
    }
}

// Fetch courses with deadline included
$stmt = $pdo->query("SELECT id, name, code, created_at, deadline FROM courses ORDER BY created_at DESC");
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/style.css?v=2">
</head>

<body>

<!-- =======================
     SIDEBAR MENU
======================= -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="sidebar-brand-text">
            <h3 class="sidebar-title mb-0">Student</h3>
            <p class="sidebar-subtitle mb-0">Feedback Portal</p>
        </div>
    </div>

    <a href="student_dashboard.php" class="sidebar-link active">
        <i class="bi bi-book"></i><span>Courses</span>
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
                <span class="hero-badge">Student Workspace</span>
                <h2 class="hero-title mt-3 mb-2">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </h2>
                <p class="hero-subtitle mb-0">
                    Submit course evaluations and help improve teaching quality through structured feedback.
                </p>
            </div>
            <div class="hero-icon">
                <i class="bi bi-stars"></i>
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
            <h4 class="mb-1">Available Courses</h4>
            <p class="section-subtext mb-0">Select a course below and complete the evaluation form.</p>
        </div>
        <span class="section-chip">Live Evaluation</span>
    </div>

    <!-- Courses -->
    <?php foreach ($courses as $course): ?>
        <div class="card course-card p-4 mb-4">

            <div class="course-card-header mb-3">
                <div>
                    <h4 class="course-title">
                        <?php echo htmlspecialchars($course['name']); ?>
                    </h4>
                    <p class="course-code mb-0">(<?php echo htmlspecialchars($course['code']); ?>)</p>
                </div>

                <div class="deadline-pill">
                    <i class="bi bi-clock-history"></i>
                    <span>
                        <?php echo $course['deadline'] ? 'Deadline: ' . htmlspecialchars($course['deadline']) : 'No deadline set'; ?>
                    </span>
                </div>
            </div>

            <!-- Modern Rating Scale Box -->
            <div class="rating-scale-box p-3 mb-4">
                <div class="rating-scale-title">
                    <i class="bi bi-stars"></i> Rating Scale
                </div>

                <div class="rating-scale-grid">
                    <div class="rating-item">
                        <span class="rating-number">1</span>
                        <span class="rating-label">Poor</span>
                    </div>
                    <div class="rating-item">
                        <span class="rating-number">2</span>
                        <span class="rating-label">Fair</span>
                    </div>
                    <div class="rating-item">
                        <span class="rating-number">3</span>
                        <span class="rating-label">Good</span>
                    </div>
                    <div class="rating-item">
                        <span class="rating-number">4</span>
                        <span class="rating-label">Very Good</span>
                    </div>
                    <div class="rating-item">
                        <span class="rating-number">5</span>
                        <span class="rating-label">Excellent</span>
                    </div>
                </div>
            </div>

            <!-- Feedback Form -->
            <form method="post">
                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Overall Rating</label>
                        <input type="number" name="rating_overall" min="1" max="5" class="form-control modern-input" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teaching Rating</label>
                        <input type="number" name="rating_teaching" min="1" max="5" class="form-control modern-input" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Materials Rating</label>
                        <input type="number" name="rating_material" min="1" max="5" class="form-control modern-input" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Comments (optional)</label>
                    <textarea name="comment" rows="3" class="form-control modern-input" placeholder="Write your thoughts about this course..."></textarea>
                </div>

                <button class="btn btn-success submit-btn">
                    <i class="bi bi-check-circle"></i> Submit Feedback
                </button>
            </form>

        </div>
    <?php endforeach; ?>

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