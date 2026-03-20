<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow professors
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'professor') {
    header("Location: index.php");
    exit;
}

// Fetch courses assigned to this professor
$stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll();

// Prepare course rating + comment data
$courseData = [];
foreach ($courses as $c) {

    // Fetch average ratings
    $stmt = $pdo->prepare("
        SELECT AVG(rating_overall) AS avg_overall,
               AVG(rating_teaching) AS avg_teaching,
               AVG(rating_material) AS avg_material
        FROM feedback WHERE course_id = ?
    ");
    $stmt->execute([$c['id']]);
    $ratings = $stmt->fetch();

    // Fetch comments
    $stmt = $pdo->prepare("SELECT comment FROM feedback WHERE course_id = ?");
    $stmt->execute([$c['id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $courseData[] = [
        'id' => $c['id'],
        'name' => $c['name'],
        'avg_overall' => $ratings['avg_overall'],
        'avg_teaching' => $ratings['avg_teaching'],
        'avg_material' => $ratings['avg_material'],
        'comments' => $comments
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Professor Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/style.css?v=4">
</head>

<body>

<!-- =======================
     SIDEBAR MENU
======================= -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-graph-up-arrow"></i>
        </div>
        <div class="sidebar-brand-text">
            <h3 class="sidebar-title mb-0">Professor</h3>
            <p class="sidebar-subtitle mb-0">Analytics Hub</p>
        </div>
    </div>

    <a href="professor_dashboard.php" class="sidebar-link active">
        <i class="bi bi-graph-up"></i><span>Analytics</span>
    </a>

    <a href="../includes/logout.php" class="sidebar-link logout">
        <i class="bi bi-box-arrow-right"></i><span>Logout</span>
    </a>
</div>

<!-- Sidebar Toggle -->
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
                <span class="hero-badge">Professor Workspace</span>
                <h2 class="hero-title mt-3 mb-2">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
                </h2>
                <p class="hero-subtitle mb-0">
                    Review course ratings, analyze student feedback, and monitor evaluation trends across your assigned courses.
                </p>
            </div>
            <div class="hero-icon">
                <i class="bi bi-bar-chart-line"></i>
            </div>
        </div>
    </div>

    <div class="section-head mb-4">
        <div>
            <h4 class="mb-1">Course Analytics</h4>
            <p class="section-subtext mb-0">Monitor average ratings and anonymous student comments for each assigned course.</p>
        </div>
        <span class="section-chip">Insights Panel</span>
    </div>

    <?php if (empty($courseData)): ?>
        <div class="alert alert-info modern-alert">No courses assigned to you yet.</div>

    <?php else: ?>

        <?php foreach ($courseData as $c): ?>
            <div class="card p-4 mb-4 course-card">

                <div class="course-card-header mb-3">
                    <div>
                        <h4 class="course-title mb-1">
                            <?php echo htmlspecialchars($c['name']); ?>
                        </h4>
                        <p class="course-code mb-0">Course performance summary and student feedback overview.</p>
                    </div>

                    <div class="deadline-pill">
                        <i class="bi bi-bar-chart"></i>
                        <span>Live Analytics</span>
                    </div>
                </div>

                <!-- Average Ratings Summary -->
                <div class="analytics-summary-box mb-4">
                    <div class="rating-scale-title mb-3">
                        <i class="bi bi-speedometer2"></i> Average Ratings
                    </div>

                    <div class="analytics-grid">
                        <div class="analytics-metric">
                            <span class="metric-label">Overall</span>
                            <span class="metric-badge primary-badge">
                                <?php echo round($c['avg_overall'] ?? 0, 2); ?>
                            </span>
                        </div>

                        <div class="analytics-metric">
                            <span class="metric-label">Teaching</span>
                            <span class="metric-badge success-badge">
                                <?php echo round($c['avg_teaching'] ?? 0, 2); ?>
                            </span>
                        </div>

                        <div class="analytics-metric">
                            <span class="metric-label">Materials</span>
                            <span class="metric-badge warning-badge">
                                <?php echo round($c['avg_material'] ?? 0, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Chart Display -->
                <div class="chart-card mb-4">
                    <div class="rating-scale-title mb-3">
                        <i class="bi bi-pie-chart"></i> Course Ratings Overview
                    </div>
                    <canvas id="chart_<?php echo md5($c['id']); ?>" height="100"></canvas>
                </div>

                <script>
                    new Chart(document.getElementById("chart_<?php echo md5($c['id']); ?>"), {
                        type: 'bar',
                        data: {
                            labels: ['Overall', 'Teaching', 'Materials'],
                            datasets: [{
                                label: 'Average Rating',
                                data: [
                                    <?php echo round($c['avg_overall'] ?? 0, 2); ?>,
                                    <?php echo round($c['avg_teaching'] ?? 0, 2); ?>,
                                    <?php echo round($c['avg_material'] ?? 0, 2); ?>
                                ],
                                backgroundColor: ['#0d6efd','#16a34a','#f59e0b'],
                                borderRadius: 10
                            }]
                        },
                        options: {
                            scales: { y: { beginAtZero: true, max: 5 } },
                            responsive: true,
                            animation: { duration: 900 },
                            plugins: {
                                legend: {
                                    display: true
                                }
                            }
                        }
                    });
                </script>

                <!-- Comments Section -->
                <div class="comments-card">
                    <div class="rating-scale-title mb-3">
                        <i class="bi bi-chat-left-text"></i> Student Comments
                    </div>

                    <?php if (empty($c['comments'])): ?>
                        <div class="alert alert-secondary modern-alert mb-0">
                            <i class="bi bi-chat-left-dots"></i> No comments yet.
                        </div>

                    <?php else: ?>
                        <ul class="list-group comment-list">
                            <?php foreach ($c['comments'] as $comment): ?>
                                <li class="list-group-item">
                                    <i class="bi bi-chat-quote text-primary me-2"></i>
                                    <?php echo htmlspecialchars($comment); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<!-- SIDEBAR JS -->
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