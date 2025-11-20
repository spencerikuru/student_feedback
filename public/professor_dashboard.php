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

// Prepare data for each course
$courseData = [];
foreach ($courses as $c) {
    $stmt = $pdo->prepare("SELECT AVG(rating_overall) AS avg_overall,
                                  AVG(rating_teaching) AS avg_teaching,
                                  AVG(rating_material) AS avg_material
                           FROM feedback WHERE course_id = ?");
    $stmt->execute([$c['id']]);
    $ratings = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT comment FROM feedback WHERE course_id = ?");
    $stmt->execute([$c['id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $courseData[] = [
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Professor)</h2>
        <a href="../includes/logout.php" class="btn btn-secondary">Logout</a>
    </div>

    <?php if (empty($courseData)): ?>
        <div class="alert alert-info">No courses assigned to you yet.</div>
    <?php else: ?>
        <?php foreach ($courseData as $c): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <?php echo htmlspecialchars($c['name']); ?>
                </div>
                <div class="card-body">
                    <canvas id="chart_<?php echo md5($c['name']); ?>" height="100"></canvas>
                    <script>
                        const ctx_<?php echo md5($c['name']); ?> = document.getElementById("chart_<?php echo md5($c['name']); ?>");
                        new Chart(ctx_<?php echo md5($c['name']); ?>, {
                            type: 'bar',
                            data: {
                                labels: ['Overall', 'Teaching', 'Material'],
                                datasets: [{
                                    label: 'Average Ratings',
                                    data: [<?php echo round($c['avg_overall'] ?? 0, 2); ?>,
                                           <?php echo round($c['avg_teaching'] ?? 0, 2); ?>,
                                           <?php echo round($c['avg_material'] ?? 0, 2); ?>],
                                    backgroundColor: ['#0d6efd','#198754','#ffc107']
                                }]
                            },
                            options: {scales: {y: {beginAtZero:true,max:5}}}
                        });
                    </script>

                    <h5 class="mt-4">Student Comments</h5>
                    <?php if (empty($c['comments'])): ?>
                        <div class="alert alert-info">No comments yet.</div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($c['comments'] as $comment): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($comment); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
