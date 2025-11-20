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
        $stmt = $pdo->prepare("INSERT INTO feedback (course_id, rating_overall, rating_teaching, rating_material, comment) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$courseId, $ratingOverall, $ratingTeaching, $ratingMaterial, $comment]);

        // Save token so they can’t submit again
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Student)</h2>
        <a href="../includes/logout.php" class="btn btn-secondary">Logout</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message[1]; ?>">
            <?php echo htmlspecialchars($message[0]); ?>
        </div>
    <?php endif; ?>

    <h3 class="mb-3">Available Courses</h3>
    <?php foreach ($courses as $course): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label">Overall Rating (1–5)</label>
                        <input type="number" name="rating_overall" min="1" max="5" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teaching Rating (1–5)</label>
                        <input type="number" name="rating_teaching" min="1" max="5" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Material Rating (1–5)</label>
                        <input type="number" name="rating_material" min="1" max="5" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comments (optional)</label>
                        <textarea name="comment" class="form-control" rows="3"></textarea>
                    </div>

                    <button class="btn btn-success">Submit Feedback</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

</body>
</html>
