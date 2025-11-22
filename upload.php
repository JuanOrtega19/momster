<?php
header('Content-Type: application/json');
require_once "functions.php";

/**
 * Koneksi database (ubah sesuai config cPanel kamu)
 */

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

/**
 * Folder upload (pastikan writable)
 */
$upload_dir = __DIR__ . "/uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/**
 * File extensions yang diizinkan
 */
$allowed_ext = ["txt","log","doc","docx","xls","xlsx","pdf"];

/**
 * Proses file upload
 */
if (!isset($_FILES["file"])) {
    echo json_encode(["status" => "error", "message" => "No file uploaded"]);
    exit;
}

$file = $_FILES["file"];
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

$max_size = 10 * 1024 * 1024; // 20 MB
if ($file["size"] > $max_size) {
    echo json_encode(["status" => "error", "message" => "Max file 10MB"]);
    exit;
}

if (!in_array($ext, $allowed_ext)) {
    echo json_encode(["status" => "error", "message" => "File type not allowed"]);
    exit;
}

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "Upload error"]);
    exit;
}

$md5 = md5_file($file["tmp_name"]);

// Cek apakah sudah ada file dengan md5 sama
$stmt = $pdo->prepare("SELECT id FROM files WHERE md5 = ?");
$stmt->execute([$md5]);
if ($stmt->fetch()) {
    echo json_encode(["status" => "error", "message" => "File already exists"]);
    exit;
}

// Simpan dengan nama unik
$stored_name = uniqid("doc_") . "." . $ext;
$target = $upload_dir . $stored_name;

if (!move_uploaded_file($file["tmp_name"], $target)) {
    echo json_encode(["status" => "error", "message" => "Failed to save file"]);
    exit;
}

// Ekstrak text + hashtag
$result = extractTextWithHashtags($target, $ext);
$text = $result["text"];
$hashtags = implode(",", $result["hashtags"]);
$upload_date = date("Y-m-d H:i:s");

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO files (name, stored_name, text, hashtag, md5, upload_date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $file["name"],   // nama asli file
    $stored_name,    // nama file fisik
    $text,
    $hashtags,
    $md5,
    $upload_date
]);

echo json_encode([
    "status" => "success",
    "message" => "File uploaded successfully",
    "name" => $file["name"],
    "upload_date" => $upload_date,
    "hashtags" => $hashtags
]);
