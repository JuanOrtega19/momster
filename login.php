<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['password'] === md5($password)) {
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Momster</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Animated gradient background */
        body::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, #0f172a, #1e293b, #3b82f6, #6366f1);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            z-index: -2;
        }
        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Cube effect */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        .cube {
            position: absolute;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            transform-style: preserve-3d;
            animation: moveCube linear infinite, rotateCube 6s linear infinite;
        }
        @keyframes moveCube {
            from { transform: translate(0,0) rotateX(0) rotateY(0); opacity: 0.9; }
            to { transform: translate(var(--dx), var(--dy)) rotateX(360deg) rotateY(360deg); opacity: 0; }
        }
        @keyframes rotateCube {
            0%   { transform: rotateX(0deg) rotateY(0deg); }
            50%  { transform: rotateX(180deg) rotateY(180deg); }
            100% { transform: rotateX(360deg) rotateY(360deg); }
        }

        /* Login card */
        .login-card {
            background: rgba(30, 41, 59, 0.9);
            border-radius: 12px;
            padding: 40px;
            width: 360px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.6);
            animation: fadeIn 1s ease-out;
            position: relative;
            z-index: 1;
        }
        @keyframes fadeIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Logo with eye inside O */
        .logo {
            text-align: center;
            margin-bottom: 25px;
            font-size: 32px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 2px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 4px;
        }
        .letter {
            position: relative;
        }
        .eye {
            display: inline-block;
            width: 38px;
            height: 38px;
            background: #fff;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
        }
        .pupil {
            width: 14px;
            height: 14px;
            background: #0f172a;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: transform 0.1s;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            color: #cbd5e1;
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #334155;
            background: #0f172a;
            color: #f1f5f9;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 6px rgba(99, 102, 241, 0.6);
        }

        .login-card button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, #6366f1, #3b82f6);
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .login-card button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4);
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Cube background -->
    <div class="particles">
        <?php for($i=0;$i<25;$i++): 
            $dx = rand(-200,200).'px';
            $dy = rand(-200,200).'px';
        ?>
            <div class="cube" style="
                width: <?=rand(15,30)?>px;
                height: <?=rand(15,30)?>px;
                top: <?=rand(0,100)?>%;
                left: <?=rand(0,100)?>%;
                animation-duration: <?=rand(15,30)?>s;
                animation-delay: -<?=rand(0,20)?>s;
                --dx: <?=$dx?>;
                --dy: <?=$dy?>;
            "></div>
        <?php endfor; ?>
    </div>

    <!-- Login card -->
    <div class="login-card">
        <div class="logo">
            <span class="letter">M</span>
            <div class="eye"><div class="pupil" id="pupil"></div></div>
            <span class="letter">M</span>
            <span class="letter">S</span>
            <span class="letter">T</span>
            <span class="letter">E</span>
            <span class="letter">R</span>
        </div>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit">Masuk</button>
        </form>
    </div>

    <script>
        const pupil = document.getElementById("pupil");
        document.addEventListener("mousemove", (e) => {
            const x = (e.clientX / window.innerWidth - 0.5) * 14;
            const y = (e.clientY / window.innerHeight - 0.5) * 14;
            pupil.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;
        });
    </script>
</body>
</html>
