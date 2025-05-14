<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not authenticated']));
}

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'No task ID provided']));
}

$taskId = $_GET['id'];
$userId = $_SESSION['user_id'];

$query = "SELECT * FROM task WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $taskId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['error' => 'Task not found']));
}

echo json_encode($result->fetch_assoc());
?>