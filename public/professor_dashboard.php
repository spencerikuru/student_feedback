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
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<!-- =======================
     SIDEBAR MENU
======================= -->
<div id="sidebar" class="sidebar">
    <h3 class="sidebar-title">Professor</h3>

    <a href="professor_dashboard.php" class="sidebar-link">
        <i class="bi bi-graph-up"></i> Analytics
    </a>

    <a href="../includes/logout.php" class="sidebar-link logout">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- Sidebar Toggle -->
<button id="toggle-btn" class="toggle-btn">
    <i class="bi bi-list"></i>
</button>

<!-- =======================
     MAIN CONTENT
======================= -->
<div class="container mt-5">

    <h2 class="mb-4">
        <i class="bi bi-person-badge text-primary"></i>
        Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Professor)
    </h2>

    <?php if (empty($courseData)): ?>
        <div class="alert alert-info">No courses assigned to you yet.</div>

    <?php else: ?>

        <?php foreach ($courseData as $c): ?>
            <div class="card shadow-sm mb-5">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-book-half"></i>
                    <?php echo htmlspecialchars($c['name']); ?>
                </div>

                <div class="card-body">

                    <!-- =====================
                        AVERAGE RATINGS SUMMARY
                    ====================== -->
                    <div class="mb-4 p-3 rounded shadow-sm"
                        style="background:#f8f9fa; border-left:5px solid #198754;">

                        <div class="fw-bold text-success mb-2">
                            <i class="bi bi-bar-chart-line"></i> Average Ratings
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <span class="small fw-bold">Overall:</span>
                                <span class="badge bg-primary">
                                    <?php echo round($c['avg_overall'] ?? 0, 2); ?>
                                </span>
                            </div>

                            <div class="col-md-4">
                                <span class="small fw-bold">Teaching:</span>
                                <span class="badge bg-success">
                                    <?php echo round($c['avg_teaching'] ?? 0, 2); ?>
                                </span>
                            </div>

                            <div class="col-md-4">
                                <span class="small fw-bold">Materials:</span>
                                <span class="badge bg-warning text-dark">
                                    <?php echo round($c['avg_material'] ?? 0, 2); ?>
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- =====================
                        CHART DISPLAY
                    ====================== -->
                    <h5 class="mb-3">Course Ratings Overview</h5>
                    <canvas id="chart_<?php echo md5($c['id']); ?>" height="100"></canvas>

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
                                    backgroundColor: ['#0d6efd','#198754','#ffc107'],
                                }]
                            },
                            options: {
                                scales: { y: { beginAtZero: true, max: 5 } },
                                responsive: true,
                                animation: { duration: 900 }
                            }
                        });
                    </script>

                    <!-- =====================
                        COMMENTS SECTION
                    ====================== -->
                    <h5 class="mt-4">Student Comments</h5>

                    <?php if (empty($c['comments'])): ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-chat-left-dots"></i> No comments yet.
                        </div>

                    <?php else: ?>
                        <ul class="list-group">
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
