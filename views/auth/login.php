<?php 
    require_once __DIR__ . '/../../models/user.php';
    require_once __DIR__ . '/../../controllers/AuthController.php';
    session_start();
$errMsg = "";

/* 🔓 Logout */
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* 🔐 Login */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['email']) && !empty($_POST['password'])) {

        $user = new User();
        $user->email = $_POST['email'];
        $user->password = $_POST['password'];

        $auth = new AuthController();

        if (!$auth->login($user)) {
            // login failed
            $errMsg = $_SESSION["errMsg"] ?? "خطأ في تسجيل الدخول";} 
        else {
            // login success
            if ($_SESSION["userRole"] === "Admin") {header("Location: ../admin/admin.php");} 
            else {header("Location: ../farmer/farmer.php");}
            exit();}} 
    else {
        $errMsg = "يرجى ملء جميع الحقول";}
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smart Farm — تسجيل الدخول</title>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

        <style>
            *, *::before, *::after{
                box-sizing: border-box; 
                margin: 0; 
                padding: 0;
            }


            :root {
                --soil: #2C1810;
                --bark: #4A2E1A;
                --moss: #3D5A2E;
                --leaf: #5C8A3C;
                --sage: #8BAF6A;
                --wheat: #D4A843;
                --cream: #F5EDD8;
                --mist: #EAF2E3;
            }

            body {
                font-family: 'Tajawal', sans-serif;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: var(--soil);
                overflow: hidden;
                position: relative;
            }

            .bg-scene { position: fixed; inset: 0; z-index: 0; overflow: hidden; }

            .sky {
                position: absolute; top: 0; left: 0; right: 0; height: 60%;
                background: linear-gradient(180deg, #1a3a2a 0%, #2d5a3d 40%, #4a7a50 70%, #6d9e5a 100%);
            }

            .sun {
                position: absolute; top: 8%; left: 12%;
                width: 80px; height: 80px;
                background: radial-gradient(circle, #f7d060 0%, #e8a820 50%, transparent 70%);
                border-radius: 50%;
                box-shadow: 0 0 60px #e8a82080, 0 0 120px #e8a82040;
                animation: sunGlow 4s ease-in-out infinite alternate;
            }

            @keyframes sunGlow {
                from { box-shadow: 0 0 60px #e8a82080, 0 0 120px #e8a82040; }
                to   { box-shadow: 0 0 80px #f7d06090, 0 0 160px #e8a82060; }
            }

            .mountains { position: absolute; bottom: 38%; left: 0; right: 0; height: 200px; }
            .mountain  { position: absolute; bottom: 0; clip-path: polygon(50% 0%, 0% 100%, 100% 100%); }
            .m1 { width:300px; height:180px; left:-30px;  background:#1e3d28; }
            .m2 { width:250px; height:150px; left:120px;  background:#264d32; }
            .m3 { width:350px; height:200px; right:-40px; background:#1e3d28; }
            .m4 { width:280px; height:160px; right:100px; background:#2d5e3a; }

            .ground {
                position: absolute; bottom:0; left:0; right:0; height:42%;
                background: linear-gradient(180deg, #4a7a35 0%, #3a6228 20%, #2e4d20 50%, #1f3515 100%);
            }

            .crops {
                position: absolute; bottom:38%; left:0; right:0; height:80px;
                display:flex; align-items:flex-end; gap:18px; padding:0 20px; overflow:hidden;
            }

            .crop-row { display:flex; gap:10px; animation: sway 3s ease-in-out infinite alternate; }
            .crop-row:nth-child(even) { animation-delay:0.5s; }
            .crop {
                font-size:22px;
                filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
                transform-origin: bottom center;
                animation: cropSway 2.5s ease-in-out infinite alternate;
            }

            .crop:nth-child(odd) { animation-delay:0.3s; }

            @keyframes cropSway {
                from { transform: rotate(-3deg); }
                to   { transform: rotate(3deg);  }
            }

            .particle {
                position:absolute; font-size:14px; opacity:0;
                animation: float 6s ease-in-out infinite; pointer-events:none;
            }

            @keyframes float {
                0%   { opacity:0; transform:translateY(0) rotate(0deg); }
                20%  { opacity:0.6; }
                80%  { opacity:0.3; }
                100% { opacity:0; transform:translateY(-120px) rotate(360deg); }
            }

            /* CARD  */
            .card-wrapper {
                position:relative; z-index:10;
                display:flex; align-items:center; justify-content:center;
                width:100%; padding:20px;
            }

            .login-card {
                background: linear-gradient(145deg, rgba(245,237,216,0.97) 0%, rgba(234,242,227,0.95) 100%);
                backdrop-filter: blur(20px);
                border-radius: 24px;
                padding: 48px 44px 40px;
                width: 100%; max-width: 420px;
                box-shadow: 0 32px 80px rgba(30,60,20,0.4), 0 8px 24px rgba(30,60,20,0.3),
                            inset 0 1px 0 rgba(255,255,255,0.8);
                border: 1px solid rgba(139,175,106,0.3);
                animation: cardIn 0.8s cubic-bezier(0.16,1,0.3,1) both;
                position: relative; overflow: hidden;
            }

            @keyframes cardIn {
                from { opacity:0; transform:translateY(40px) scale(0.95); }
                to   { opacity:1; transform:translateY(0) scale(1); }
            }

            .login-card::after {
                content:''; position:absolute; top:0; left:-100%; width:60%; height:100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
                animation: shimmer 4s 1s infinite;
            }

            @keyframes shimmer { 0%{left:-60%;} 100%{left:120%;} }

            .card-corner { position:absolute; width:120px; height:120px;
                background: radial-gradient(circle, rgba(92,138,60,0.12) 0%, transparent 70%); 
            }
            .card-corner.tl { top:-20px; right:-20px; }
            .card-corner.br { bottom:-20px; left:-20px; }

            .season-badge {
                position:absolute; top:16px; left:16px;
                background: linear-gradient(135deg, var(--wheat), #c8922a);
                color:white; font-size:11px; font-weight:700;
                padding:5px 12px; border-radius:20px; letter-spacing:0.5px;
                box-shadow: 0 3px 10px rgba(212,168,67,0.4);
            }

            .logo-area { text-align:center; margin-bottom:28px; }
            .logo-icon {
                width:72px; height:72px;
                background: linear-gradient(135deg, var(--moss), var(--leaf));
                border-radius:20px;
                display:inline-flex; align-items:center; justify-content:center;
                font-size:32px; margin-bottom:14px;
                box-shadow: 0 8px 24px rgba(61,90,46,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
                animation: logoFloat 4s ease-in-out infinite;
            }

            @keyframes logoFloat {
                0%,100%{transform:translateY(0);} 50%{transform:translateY(-6px);}
            }

            .logo-title { font-size:26px; font-weight:800; color:var(--soil); letter-spacing:-0.5px; }
            .logo-en    { font-size:13px; font-weight:700; color:var(--leaf); letter-spacing:3px; text-transform:uppercase; margin-top:2px; }
            .logo-sub   { font-size:12px; color:var(--moss); font-weight:500; margin-top:4px; }

            .divider { display:flex; align-items:center; gap:12px; margin:4px 0 24px; }
            .divider-line { flex:1; height:1px; background:linear-gradient(90deg,transparent,rgba(92,138,60,0.3),transparent); }

            .form-group { margin-bottom:16px; animation: fieldIn 0.6s cubic-bezier(0.16,1,0.3,1) both; }
            .form-group:nth-child(1){animation-delay:0.1s;}
            .form-group:nth-child(2){animation-delay:0.2s;}
            @keyframes fieldIn {
                from{opacity:0;transform:translateX(20px);}
                to  {opacity:1;transform:translateX(0);}
            }

            label { 
                display:block; 
                font-size:13px; 
                font-weight:700; 
                color:var(--bark); 
                margin-bottom:7px; 
                letter-spacing:0.3px; 
            }

            .input-wrap { position:relative; }
            .input-icon { 
                position:absolute; right:14px; top:50%; 
                transform:translateY(-50%); 
                font-size:16px; 
                pointer-events:none; 
            }

            input[type="text"], input[type="password"], input[type="email"] {
                width:100%; padding:13px 44px 13px 16px;
                background:rgba(255,255,255,0.8);
                border:2px solid rgba(139,175,106,0.25);
                border-radius:12px;
                font-family:'Tajawal',sans-serif; font-size:15px; color:var(--soil);
                outline:none; transition:all 0.25s ease; direction:rtl;
            }

            input:focus {
                border-color:var(--leaf);
                background:rgba(255,255,255,0.95);
                box-shadow:0 0 0 4px rgba(92,138,60,0.12), 0 2px 8px rgba(92,138,60,0.1);
            }
            input::placeholder { color:#aab89a; }

            .forgot { text-align:left; margin-top:7px; }
            .forgot a { font-size:12px; color:var(--moss); text-decoration:none; font-weight:500; transition:color 0.2s; }
            .forgot a:hover { color:var(--leaf); }

            .btn-login {
                width:100%; padding:15px;
                background: linear-gradient(135deg, var(--moss) 0%, var(--leaf) 60%, var(--sage) 100%);
                color:white; border:none; border-radius:12px;
                font-family:'Tajawal',sans-serif; font-size:16px; font-weight:700;
                cursor:pointer; margin-top:20px; position:relative; overflow:hidden;
                transition:transform 0.2s, box-shadow 0.2s;
                box-shadow:0 6px 20px rgba(61,90,46,0.35);
                letter-spacing:0.3px;
                animation: fieldIn 0.6s 0.3s both;
            }

            .btn-login::before {
                content:''; position:absolute; inset:0;
                background:linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%);
            }

            .btn-login:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(61,90,46,0.45); }
            .btn-login:active{ transform:translateY(0); }
            .btn-inner { display:flex; align-items:center; justify-content:center; gap:8px; position:relative; }

            .footer-note {
                text-align:center; margin-top:18px; font-size:13px; color:#8a9e7a;
                animation: fieldIn 0.6s 0.4s both;
            }
            .footer-note a { color:var(--moss); font-weight:700; text-decoration:none; transition:color 0.2s; }
            .footer-note a:hover { color:var(--leaf); text-decoration:underline; }
        </style>
    </head>

    <body>
        <div class="bg-scene">
            <div class="sky"></div>
            <div class="sun"></div>
            <div class="mountains">
                <div class="mountain m1"></div><div class="mountain m2"></div>
                <div class="mountain m3"></div><div class="mountain m4"></div>
            </div>
            <div class="crops" id="crops"></div>
            <div class="ground"></div>
            <div class="particle" style="left:10%;top:30%;animation-delay:0s;animation-duration:7s">🍃</div>
            <div class="particle" style="left:25%;top:50%;animation-delay:1.5s;animation-duration:6s">✨</div>
            <div class="particle" style="left:70%;top:40%;animation-delay:0.8s;animation-duration:8s">🌿</div>
            <div class="particle" style="left:85%;top:60%;animation-delay:2.5s;animation-duration:5.5s">🍃</div>
            <div class="particle" style="left:50%;top:20%;animation-delay:3s;animation-duration:7s">✨</div>
        </div>

        <div class="card-wrapper">
            <div class="login-card">
                <div class="card-corner tl"></div>
                <div class="card-corner br"></div>
                <div class="season-badge">🌾 موسم الحصاد</div>

                <div class="logo-area">
                    <div class="logo-icon">🌱</div>
                    <div class="logo-title">Smart Farm</div>
                    <div class="logo-en">SMART FARM</div>
                    <div class="logo-sub">نظام إدارة المزارع الذكي</div>
                </div>

                <div class="divider">
                    <div class="divider-line"></div>
                    <span style="font-size:16px">🌻</span>
                    <div class="divider-line"></div>
                </div>

                <form method="POST" action="login.php">
                <div class="form-group">
                    <label>البريد الإلكتروني</label>
                    <div class="input-wrap">
                        <span class="input-icon">👤</span>
                        <input type="text" name="email" placeholder=" ادخل البريد الإلكتروني" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>كلمة المرور</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                <div class="forgot"><a href="#">نسيت كلمة المرور؟</a></div>
                </div>

                <button type="submit" class="btn-login" onclick="this.querySelector('.btn-inner span:first-child').textContent='جاري الدخول...'">
                    <div class="btn-inner">
                        <span>دخول للمزرعة</span>
                        <span>🚜</span>
                    </div>
                </button>
            </form>

                <div class="footer-note">
                    مش عندك حساب؟ <a href="register.php">سجّل دلوقتي</a>
                </div>
            </div>
        </div>

        <script>
            const crops = document.getElementById('crops');
            const emojis = ['🌽','🌾','🥬','🥕','🌿','🌻','🥦'];
            for (let r = 0; r < 8; r++) {
                const row = document.createElement('div');
                row.className = 'crop-row';
                row.style.animationDelay = (r * 0.15) + 's';
                for (let c = 0; c < 5; c++) {
                    const crop = document.createElement('div');
                    crop.className = 'crop';
                    crop.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                    crop.style.animationDelay = (Math.random() * 1.5) + 's';
                    row.appendChild(crop);}
                crops.appendChild(row);
            }
        </script>
    </body>
</html>
