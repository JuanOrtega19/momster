<?php
require_once "config.php"; // koneksi PDO

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];
$stmt = $pdo->prepare("SELECT name, stored_name FROM files WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    die("File not found in database");
}

$filePath = __DIR__ . "/uploads/" . $file['stored_name'];

if (!file_exists($filePath)) {
    die("File missing on server");
}

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"" . basename($file['name']) . "\"");
header("Content-Length: " . filesize($filePath));
readfile($filePath);
exit;
