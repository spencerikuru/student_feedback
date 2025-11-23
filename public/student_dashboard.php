<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$message = "";

// If form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $courseId = intval($_POST['course_id']);
    $ratingOverall = intval($_POST['rating_overall']);
    $ratingTeaching = intval($_POST['rating_teaching']);
    $ratingMaterial = intval($_POST['rating_material']);
    $comment = trim($_POST['comment']);

    // Prevent duplicate submissions
    $token = $_SESSION['user_id'] . '-' . $courseId;
    $tokenHash = hash('sha256', $token);

    $stmt = $pdo->prepare("SELECT id FROM submission_tokens WHERE token_hash=? AND course_id=?");
    $stmt->execute([$tokenHash, $courseId]);
    $alreadySubmitted = $stmt->fetch();

    if ($alreadySubmitted) {
        $message = ["⚠️ You already submitted feedback for this course.", "warning"];
    } else {
        // Insert feedback
        $stmt = $pdo->prepare("INSERT INTO feedback 
            (course_id, rating_overall, rating_teaching, rating_material, comment)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$courseId, $ratingOverall, $ratingTeaching, $ratingMaterial, $comment]);

        // Save token
        $stmt = $pdo->prepare("INSERT INTO submission_tokens (course_id, token_hash) VALUES (?, ?)");
        $stmt->execute([$courseId, $tokenHash]);

        $message = ["✅ Feedback submitted successfully!", "success"];
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
    <title>Student Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<!-- =======================
     SIDEBAR MENU
======================= -->
<div id="sidebar" class="sidebar">
    <h3 class="sidebar-title">Student Panel</h3>

    <a href="student_dashboard.php" class="sidebar-link">
        <i class="bi bi-journal-text"></i> My Courses
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

    <h2 class="mb-4 section-title">
        <i class="bi bi-person-check text-success"></i>
        Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
    </h2>

    <!-- Message -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message[1]; ?>">
            <?php echo htmlspecialchars($message[0]); ?>
        </div>
    <?php endif; ?>

    <h4 class="mb-4">Available Courses</h4>

    <!-- Course Cards -->
    <?php foreach ($courses as $course): ?>
        <div class="card shadow-sm p-4 mb-4">

            <h4 class="text-success mb-1">
                <?php echo htmlspecialchars($course['name']); ?>
            </h4>
            <p class="text-muted small">(<?php echo htmlspecialchars($course['code']); ?>)</p>

            <form method="post">
                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Overall Rating</label>
                        <input type="number" name="rating_overall" min="1" max="5" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Teaching Rating</label>
                        <input type="number" name="rating_teaching" min="1" max="5" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Materials Rating</label>
                        <input type="number" name="rating_material" min="1" max="5" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Comments (optional)</label>
                    <textarea name="comment" rows="3" class="form-control"></textarea>
                </div>

                <button class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Submit Feedback
                </button>
            </form>

        </div>
    <?php endforeach; ?>

</div>

<!-- Sidebar JavaScript -->
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
