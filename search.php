<?php
require "config.php";

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo "<p>Masukkan kata pencarian.</p>";
    exit;
}

$words = preg_split('/\s+/', $q);
$conditions = [];
$params = [];

foreach ($words as $i => $word) {
    $conditions[] = " (LOWER(CONCAT(name,' ',text,' ',COALESCE(hashtag,''))) LIKE :w$i) ";
    $params[":w$i"] = "%" . strtolower($word) . "%";
}

$sql = "SELECT id, name, stored_name, text, upload_date 
        FROM files 
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY upload_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$results) {
    echo "<p>Tidak ada hasil.</p>";
    exit;
}

echo "<table>";
echo "<tr><th>No</th><th>Nama</th><th>Upload Date</th><th>Preview</th></tr>";

foreach ($results as $i => $row) {
    $preview = mb_substr($row['text'], 0, 200);

    // Highlight kata
    foreach ($words as $w) {
        $preview = preg_replace(
            "/(" . preg_quote($w, "/") . ")/i",
            "<mark>$1</mark>",
            $preview
        );
    }

    echo "<tr>";
    echo "<td>" . ($i+1) . "</td>";
    echo "<td><a href='download.php?id={$row['id']}'>" . htmlspecialchars($row['name']) . "</a></td>";
    echo "<td>" . $row['upload_date'] . "</td>";
    echo "<td>" . $preview . "...</td>";
    echo "</tr>";
}
echo "</table>";
