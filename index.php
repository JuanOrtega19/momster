<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
require_once "config.php";

// koneksi DB untuk pencarian
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Database connection failed");
}

$results = [];
$keywords = [];
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $q = trim($_GET['q']);
    $keywords = preg_split('/\s+/', $q);
    
    // buat kondisi dynamic: setiap kata harus ada di name/text/hashtag
    $conditions = [];
    $params = [];
    foreach ($keywords as $word) {
        $conditions[] = "(name LIKE ? OR text LIKE ? OR hashtag LIKE ?)";
        $params[] = "%$word%";
        $params[] = "%$word%";
        $params[] = "%$word%";
    }
    $sql = "SELECT id, name, stored_name, upload_date, text FROM files
            WHERE " . implode(" AND ", $conditions) . "
            ORDER BY upload_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// fungsi highlight kata
function highlightWords($text, $keywords){
    foreach ($keywords as $word){
        if (trim($word) === '') continue;
        $text = preg_replace(
            "/(" . preg_quote($word, '/') . ")/i",
            '<span class="highlight">$1</span>',
            $text
            );
    }
    return $text;
}

// fungsi ambil multi snippet dari semua keyword
function makeSnippet($fullText, $keywords, $radius = 80, $maxSnippets = 3){
    if (!$fullText) return "";
    $snippets = [];
    $usedPositions = [];
    
    foreach ($keywords as $word){
        if (trim($word) === '') continue;
        
        if (preg_match_all("/".preg_quote($word, "/")."/i", $fullText, $matches, PREG_OFFSET_CAPTURE)){
            foreach ($matches[0] as $m){
                $pos = $m[1];
                
                // skip jika posisi sudah dekat snippet lain
                $tooClose = false;
                foreach ($usedPositions as $p){
                    if (abs($p - $pos) < $radius*2){
                        $tooClose = true; break;
                    }
                }
                if ($tooClose) continue;
                
                $start = max(0, $pos - $radius);
                $snippet = mb_substr($fullText, $start, $radius*2);
                if ($start > 0) $snippet = "..." . $snippet;
                if ($start + $radius*2 < mb_strlen($fullText)) $snippet .= "...";
                $snippets[] = $snippet;
                $usedPositions[] = $pos;
                
                if (count($snippets) >= $maxSnippets) break 2;
            }
        }
    }
    
    if (!$snippets){
        // fallback ke awal
        $snippets[] = mb_substr($fullText, 0, $radius*2) . "...";
    }
    
    $joined = implode(" <br> ", $snippets);
    return highlightWords(htmlspecialchars($joined), $keywords);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Momster</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/style.css">

  <link rel="icon" type="image/x-icon" href="image/favicon.png">
</head>
<body>

<header>
  <button class="upload-btn" onclick="openModal()">Upload</button>
  <button class="upload-btn" onclick="location.href='logout.php'">Logout</button>

</header>

<div class="container"> <a href="./">
<svg id="momster-logo" width="200" height="150" viewBox="0 0 300 150" xmlns="http://www.w3.org/2000/svg" style="position:absolute">
  <!-- Tulisan Momster -->
 

  <!-- Mata kiri -->
  <circle id="eye-left" cx="110" cy="55" r="20" fill="white" stroke="black" stroke-width="0"/>
  <circle id="pupil-left" cx="110" cy="55" r="8" fill="#224657"/>

  <!-- Mata kanan -->
</svg>

<script>
const svg = document.getElementById("momster-logo");
const pupils = [
  { eye: document.getElementById("eye-left"), pupil: document.getElementById("pupil-left") },
  { eye: document.getElementById("eye-right"), pupil: document.getElementById("pupil-right") }
];

document.addEventListener("mousemove", (e) => {
  pupils.forEach(({ eye, pupil }) => {
    const rect = svg.getBoundingClientRect();
    const eyeX = rect.left + parseFloat(eye.getAttribute("cx"));
    const eyeY = rect.top + parseFloat(eye.getAttribute("cy"));

    const angle = Math.atan2(e.clientY - eyeY, e.clientX - eyeX);
    const maxMove = 8; // seberapa jauh pupil bisa bergerak

    const dx = Math.cos(angle) * maxMove;
    const dy = Math.sin(angle) * maxMove;

    pupil.setAttribute("cx", parseFloat(eye.getAttribute("cx")) + dx);
    pupil.setAttribute("cy", parseFloat(eye.getAttribute("cy")) + dy);
  });
});
</script>

  <img src="image/logo.png" alt="Momster"></a>
  <form method="get" class="search-box">
    <input type="text" name="q" placeholder="Search..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
  </form>

  <div class="results">
  <?php if ($results): ?>
    <?php foreach ($results as $i=>$row): ?>
      <div class="result-item">
     <a href="download.php?id=<?= $row['id'] ?>">
  <?= highlightWords(htmlspecialchars($row['name']), $keywords) ?>
</a>
        <div class="date"><?= htmlspecialchars($row['upload_date']) ?></div>
        <div class="snippet"><?= makeSnippet($row['text'], $keywords) ?></div>
      </div>
    <?php endforeach; ?>
  <?php elseif(isset($_GET['q'])): ?>
    <p>Not found criteria.</p>
  <?php endif; ?>
  </div>
</div>

<!-- Modal Upload -->
<div id="uploadModal" class="modal">
  <div class="modal-content">
    <h3>Upload Document</h3>
    <div id="dropArea" class="drop-area">Drag & Drop file here or click to select</div>
    <input type="file" id="fileInput" style="display:none">
    <div id="uploadStatus"></div>
    <br>
    <button onclick="closeModal()">Close</button>
  </div>
</div>
<div style="
    width:100%;
    background:#111;
    color:#fff;
    padding:15px;
    text-align:center;
    font-family:Arial, sans-serif;
    font-size:14px;
    position:fixed;
    bottom:0;
    left:0;
    z-index:9999;
">
    Support this project ❤️  
    <a target="_blank" href="https://github.com/sponsors/JuanOrtega19" 
       style="color:#4fc3f7; text-decoration:underline;">
       Become a Sponsor
    </a>
</div>
<script src="assets/script.js"></script>
</body>
</html>
