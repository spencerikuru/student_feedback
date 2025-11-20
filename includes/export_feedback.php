<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/index.php");
    exit;
}

// Set headers so browser downloads as CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="feedback_export.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write header row
fputcsv($output, ['Course Name', 'Course Code', 'Overall Rating', 'Teaching Rating', 'Material Rating', 'Comment', 'Created At']);

// Fetch all feedback with course info
$stmt = $pdo->query("SELECT c.name as course_name, c.code as course_code,
                            f.rating_overall, f.rating_teaching, f.rating_material,
                            f.comment, f.created_at
                     FROM feedback f
                     JOIN courses c ON f.course_id = c.id
                     ORDER BY f.created_at DESC");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
