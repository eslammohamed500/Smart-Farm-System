
<?php
    require_once __DIR__ . '/../../models/user.php';
    require_once __DIR__ . '/../../controllers/AuthController.php';
    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!isset($_POST['terms'])) {
            echo "<h3 style='color:red;text-align:center;'>يجب الموافقة على الشروط</h3>";
            exit();
        }

        if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_conf']) && isset($_POST['first_name']) && isset($_POST['last_name'])) {

            // ✅ check password match
            if ($_POST['password'] !== $_POST['password_conf']) {
                echo "<h3 style='color:red;text-align:center;'>كلمة المرور غير متطابقة</h3>";
            } 
            else{
                $user = new User();
                $user->name = $_POST['first_name'] . " " . $_POST['last_name'];
                $user->email = $_POST['email'];
                $user->password = $_POST['password'];
                $auth = new AuthController();

                $isRegistered = $auth->register($user);
                if ($isRegistered) {
                    header("refresh:2; url=login.php");
                    echo "<h3 style='color:green;text-align:center;'>تم إنشاء الحساب بنجاح! جاري تحويلك...</h3>";
                    exit();
                } 
                else{
                    echo "<h3 style='color:red;text-align:center;'>الإيميل مستخدم بالفعل</h3>";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smart Farm — إنشاء حساب</title>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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
                overflow-x: hidden;
                position: relative;
                padding: 24px 16px;
            }

            .bg-scene { 
                position: fixed; inset: 0; z-index: 0; overflow: hidden; 
            }

            .sky {
                position: absolute; top:0; left:0; right:0; height:55%;
                background: linear-gradient(180deg, #0f2a1a 0%, #1e4a2e 35%, #3a6e45 65%, #5a9060 100%);
            }

            /* Stars for night sky feel */
            .star {
                position: absolute; border-radius: 50%;
                background: white; opacity: 0;
                animation: twinkle 3s ease-in-out infinite;
            }

            @keyframes twinkle {
                0%,100%{opacity:0;} 50%{opacity:0.8;}
            }

            .moon {
                position: absolute; top:6%; right:10%;
                width:60px; height:60px;
                background: radial-gradient(circle at 35% 35%, #f5e6a0, #d4bc5a);
                border-radius: 50%;
                box-shadow: 0 0 40px #d4bc5a60, 0 0 80px #d4bc5a30;
                animation: moonGlow 5s ease-in-out infinite alternate;
            }

            @keyframes moonGlow {
                from{box-shadow:0 0 40px #d4bc5a60, 0 0 80px #d4bc5a30;}
                to  {box-shadow:0 0 60px #f5e6a080, 0 0 120px #d4bc5a50;}
            }

            .mountains { position:absolute; bottom:36%; left:0; right:0; height:220px; }
            .mountain  { position:absolute; bottom:0; clip-path:polygon(50% 0%,0% 100%,100% 100%); }
            .m1{width:320px;height:200px;left:-40px; background:#0f2a1a;}
            .m2{width:260px;height:160px;left:130px; background:#162f1f;}
            .m3{width:380px;height:220px;right:-50px;background:#0f2a1a;}
            .m4{width:290px;height:170px;right:90px; background:#1a3824;}

            .ground {
                position:absolute; bottom:0; left:0; right:0; height:40%;
                background:linear-gradient(180deg,#3a6228 0%,#2e4d20 25%,#1f3515 60%,#0f1f0a 100%);
            }

            .crops {
                position:absolute; bottom:36%; left:0; right:0; height:80px;
                display:flex; align-items:flex-end; gap:16px; padding:0 20px; overflow:hidden;
            }

            .crop-row { display:flex; gap:10px; animation:sway 3.5s ease-in-out infinite alternate; }
            .crop-row:nth-child(even){animation-delay:0.6s;}
            .crop { font-size:20px; transform-origin:bottom center; animation:cropSway 3s ease-in-out infinite alternate; }
            .crop:nth-child(odd){animation-delay:0.4s;}
            @keyframes cropSway{from{transform:rotate(-3deg);}to{transform:rotate(3deg);}}

            .firefly {
                position:absolute; width:4px; height:4px;
                background:#aaff88; border-radius:50%;
                box-shadow:0 0 8px #aaff88, 0 0 16px #aaff8860;
                opacity:0; animation:fireflyFloat 5s ease-in-out infinite;
            }

            @keyframes fireflyFloat {
                0%  {opacity:0; transform:translate(0,0);}
                20% {opacity:1;}
                50% {opacity:0.7; transform:translate(30px,-40px);}
                80% {opacity:0.4; transform:translate(-10px,-70px);}
                100%{opacity:0; transform:translate(20px,-100px);}
            }

            /*  CARD  */
            .card-wrapper {
                position:relative; z-index:10;
                display:flex; align-items:center; justify-content:center;
                width:100%; min-height:100vh;
                padding: 24px 16px;
            }

            .register-card {
                background: linear-gradient(150deg, rgba(240,232,210,0.97) 0%, rgba(228,240,220,0.95) 100%);
                backdrop-filter: blur(20px);
                border-radius: 24px;
                padding: 40px 44px 36px;
                width: 100%; max-width: 460px;
                box-shadow: 0 32px 80px rgba(10,30,15,0.55), 0 8px 24px rgba(10,30,15,0.4),
                            inset 0 1px 0 rgba(255,255,255,0.8);
                border: 1px solid rgba(139,175,106,0.3);
                animation: cardIn 0.8s cubic-bezier(0.16,1,0.3,1) both;
                position: relative; overflow: hidden;
            }

            @keyframes cardIn {
                from{opacity:0; transform:translateY(40px) scale(0.95);}
                to  {opacity:1; transform:translateY(0) scale(1);}
            }

            .register-card::after {
                content:''; position:absolute; top:0; left:-100%; width:60%; height:100%;
                background:linear-gradient(90deg,transparent,rgba(255,255,255,0.12),transparent);
                animation:shimmer 5s 1.5s infinite;
            }
            @keyframes shimmer{0%{left:-60%;}100%{left:120%;}}

            .card-corner{
                position:absolute; width:140px; height:140px;
                background:radial-gradient(circle,rgba(92,138,60,0.1) 0%,transparent 70%);
            }
            .card-corner.tl{top:-30px;right:-30px;}
            .card-corner.br{bottom:-30px;left:-30px;}

            .night-badge {
                position:absolute; top:16px; left:16px;
                background: linear-gradient(135deg, #264d70, #1a3350);
                color:#a8d4f0; font-size:11px; font-weight:700;
                padding:5px 12px; border-radius:20px; letter-spacing:0.5px;
                box-shadow:0 3px 10px rgba(26,51,80,0.5);
            }

            .logo-area { text-align:center; margin-bottom:24px; }
            .logo-icon {
                width:68px; height:68px;
                background:linear-gradient(135deg, #1a3824, var(--moss));
                border-radius:20px;
                display:inline-flex; align-items:center; justify-content:center;
                font-size:30px; margin-bottom:12px;
                box-shadow:0 8px 24px rgba(10,30,15,0.5), inset 0 1px 0 rgba(255,255,255,0.15);
                animation:logoFloat 4s ease-in-out infinite;
            }

            @keyframes logoFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-6px);}}
            .logo-title{font-size:26px; font-weight:800; color:var(--soil); letter-spacing:-0.5px;}
            .logo-en   {font-size:12px; font-weight:700; color:var(--leaf); letter-spacing:3px; text-transform:uppercase; margin-top:2px;}
            .logo-sub  {font-size:12px; color:var(--moss); font-weight:500; margin-top:4px;}

            .section-label {
                font-size:11px; font-weight:700; color:var(--sage);
                letter-spacing:1.5px; text-transform:uppercase;
                margin: 20px 0 12px;
                display:flex; align-items:center; gap:8px;
            }

            .section-label::after {
                content:''; flex:1; height:1px;
                background:linear-gradient(90deg, rgba(92,138,60,0.3), transparent);
            }

            .form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

            .form-group {
                margin-bottom:14px;
                animation:fieldIn 0.6s cubic-bezier(0.16,1,0.3,1) both;
            }

            .form-group.full { grid-column:1/-1; }

            @keyframes fieldIn{
                from{opacity:0; transform:translateY(15px);}
                to  {opacity:1; transform:translateY(0);}
            }

            .form-group:nth-child(1){animation-delay:0.05s;}
            .form-group:nth-child(2){animation-delay:0.1s;}
            .form-group:nth-child(3){animation-delay:0.15s;}
            .form-group:nth-child(4){animation-delay:0.2s;}
            .form-group:nth-child(5){animation-delay:0.25s;}
            .form-group:nth-child(6){animation-delay:0.3s;}

            label{
                display:block; 
                font-size:12px; 
                font-weight:700; 
                color:var(--bark); 
                margin-bottom:6px; 
                letter-spacing:0.3px;
            }

            .input-wrap{position:relative;}
            .input-icon{
                position:absolute; 
                right:13px; top:50%; 
                transform:translateY(-50%); 
                font-size:15px; 
                pointer-events:none;
            }

            input[type="text"],input[type="password"],input[type="email"],select {
                width:100%; padding:12px 42px 12px 14px;
                background:rgba(255,255,255,0.8);
                border:2px solid rgba(139,175,106,0.25);
                border-radius:11px;
                font-family:'Tajawal',sans-serif; font-size:14px; color:var(--soil);
                outline:none; transition:all 0.25s ease; direction:rtl;
                appearance:none;
            }

            select { cursor:pointer; background-image:none; }
            input:focus, select:focus {
                border-color:var(--leaf);
                background:rgba(255,255,255,0.95);
                box-shadow:0 0 0 4px rgba(92,138,60,0.1), 0 2px 8px rgba(92,138,60,0.1);
            }
            input::placeholder{color:#aab89a;}

            /* Password strength */
            .strength-bar { display:flex; gap:4px; margin-top:6px; }
            .strength-seg {
                height:3px; flex:1; border-radius:2px;
                background:rgba(139,175,106,0.2); transition:background 0.3s;
            }

            .strength-seg.active-weak   { background:#e05c5c; }
            .strength-seg.active-medium { background:var(--wheat); }
            .strength-seg.active-strong { background:var(--leaf); }

            /* Terms */
            .terms {
                display:flex; align-items:flex-start; gap:10px;
                margin-top:6px; animation:fieldIn 0.6s 0.35s both;
            }

            .terms input[type="checkbox"] {
                width:18px; height:18px; min-width:18px;
                accent-color:var(--leaf); cursor:pointer; margin-top:2px;
                padding:0; border-radius:4px;
            }
            .terms label {
                font-size:12px; color:#7a9268; font-weight:400;
                cursor:pointer; line-height:1.5;
                margin:0;
            }
            .terms label a { color:var(--moss); font-weight:700; text-decoration:none; }
            .terms label a:hover { text-decoration:underline; }

            .btn-register {
                width:100%; padding:15px;
                background:linear-gradient(135deg, #1a3824 0%, var(--moss) 40%, var(--leaf) 100%);
                color:white; border:none; border-radius:12px;
                font-family:'Tajawal',sans-serif; font-size:16px; font-weight:700;
                cursor:pointer; margin-top:18px; position:relative; overflow:hidden;
                transition:transform 0.2s, box-shadow 0.2s;
                box-shadow:0 6px 20px rgba(10,30,15,0.4);
                animation:fieldIn 0.6s 0.4s both;
            }

            .btn-register::before {
                content:''; position:absolute; inset:0;
                background:linear-gradient(135deg,rgba(255,255,255,0.12) 0%,transparent 50%);
            }
            .btn-register:hover{transform:translateY(-2px); box-shadow:0 10px 28px rgba(10,30,15,0.5);}
            .btn-register:active{transform:translateY(0);}
            .btn-inner{display:flex; align-items:center; justify-content:center; gap:8px; position:relative;}

            .footer-note{
                text-align:center; margin-top:16px; font-size:13px; color:#8a9e7a;
                animation:fieldIn 0.6s 0.45s both;
            }

            .footer-note a{color:var(--moss); font-weight:700; text-decoration:none; transition:color 0.2s;}
            .footer-note a:hover{color:var(--leaf); text-decoration:underline;}

            .divider{display:flex; align-items:center; gap:12px; margin:4px 0 20px;}
            .divider-line{flex:1; height:1px; background:linear-gradient(90deg,transparent,rgba(92,138,60,0.3),transparent);}
        </style>
    </head>
    <body>

        <div class="bg-scene">
            <div class="sky"></div>
            <div class="moon"></div>

            <!-- Stars -->
            <div class="star" style="width:2px;height:2px;top:5%;left:20%;animation-delay:0s;animation-duration:2.5s"></div>
            <div class="star" style="width:3px;height:3px;top:8%;left:40%;animation-delay:0.7s;animation-duration:3.5s"></div>
            <div class="star" style="width:2px;height:2px;top:12%;left:65%;animation-delay:1.2s;animation-duration:2s"></div>
            <div class="star" style="width:2px;height:2px;top:4%;left:75%;animation-delay:0.3s;animation-duration:4s"></div>
            <div class="star" style="width:3px;height:3px;top:15%;left:30%;animation-delay:1.8s;animation-duration:3s"></div>
            <div class="star" style="width:2px;height:2px;top:10%;left:55%;animation-delay:0.9s;animation-duration:2.8s"></div>
            <div class="star" style="width:2px;height:2px;top:7%;left:85%;animation-delay:2s;animation-duration:3.2s"></div>

            <div class="mountains">
                <div class="mountain m1"></div><div class="mountain m2"></div>
                <div class="mountain m3"></div><div class="mountain m4"></div>
            </div>
            <div class="crops" id="crops"></div>
            <div class="ground"></div>

            <!-- Fireflies -->
            <div class="firefly" style="left:15%;bottom:30%;animation-delay:0s;animation-duration:6s"></div>
            <div class="firefly" style="left:35%;bottom:25%;animation-delay:1.5s;animation-duration:7s"></div>
            <div class="firefly" style="left:60%;bottom:35%;animation-delay:0.8s;animation-duration:5.5s"></div>
            <div class="firefly" style="left:80%;bottom:28%;animation-delay:2.5s;animation-duration:6.5s"></div>
            <div class="firefly" style="left:50%;bottom:20%;animation-delay:3.2s;animation-duration:5s"></div>
        </div>

        <div class="card-wrapper">
            <div class="register-card">
                <div class="card-corner tl"></div>
                <div class="card-corner br"></div>
                <div class="night-badge">🌙 تسجيل جديد</div>

                <div class="logo-area">
                    <div class="logo-icon">🌱</div>
                    <div class="logo-title">Smart Farm</div>
                    <div class="logo-en">SMART FARM</div>
                    <div class="logo-sub">إنشاء حساب جديد</div>
                </div>

                <div class="divider">
                    <div class="divider-line"></div>
                    <span style="font-size:16px">🌾</span>
                    <div class="divider-line"></div>
                </div>

                <!-- Personal Info -->
                <form method="POST" action="register.php">
                    <div class="section-label">📋 البيانات الشخصية</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>الاسم الأول</label>
                            <div class="input-wrap">
                            <span class="input-icon">👤</span>
                            <input type="text" name="first_name" placeholder="محمد" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>الاسم الأخير</label>
                            <div class="input-wrap">
                            <span class="input-icon">👤</span>
                            <input type="text" name="last_name" placeholder="أحمد" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <div class="input-wrap">
                            <span class="input-icon">📧</span>
                            <input type="email" name="email" placeholder="example@smartfarm.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <div class="input-wrap">
                            <span class="input-icon">📱</span>
                            <input type="text" placeholder="+20 1XX XXX XXXX">
                        </div>
                    </div>

                    <!-- Farm Info -->
                    <div class="section-label">🚜 بيانات المزرعة</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>اسم المزرعة</label>
                            <div class="input-wrap">
                            <span class="input-icon">🏡</span>
                            <input type="text" placeholder="مزرعة النيل">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>نوع المحصول</label>
                            <div class="input-wrap">
                                <span class="input-icon">🌽</span>
                                <select>
                                    <option value="" disabled selected>اختر...</option>
                                    <option>قمح</option>
                                    <option>ذرة</option>
                                    <option>خضروات</option>
                                    <option>فاكهة</option>
                                    <option>أخرى</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Account Info -->
                    <div class="section-label">🔑 بيانات الحساب</div>
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <div class="input-wrap">
                            <span class="input-icon">🪪</span>
                            <input type="text" placeholder="username123">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password" id="passwordInput" placeholder="••••••••" oninput="checkStrength(this.value)">
                        </div>
                        <div class="strength-bar">
                            <div class="strength-seg" id="s1"></div>
                            <div class="strength-seg" id="s2"></div>
                            <div class="strength-seg" id="s3"></div>
                            <div class="strength-seg" id="s4"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>تأكيد كلمة المرور</label>
                        <div class="input-wrap">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password_conf" id="passwordConf">
                        </div>
                    </div>

                    <div class="terms">
                        <input type="checkbox" id="terms" name="terms">
                        <label for="terms">أوافق على <a href="#">الشروط والأحكام</a> و<a href="#">سياسة الخصوصية</a> الخاصة بـ Smart Farm</label>
                    </div>

                    <button type="submit" class="btn-register">
                        <div class="btn-inner">
                            <span>إنشاء الحساب</span>
                            <span>🌱</span>
                        </div>
                    </button>
                </form>

                <div class="footer-note">
                    عندك حساب بالفعل؟ <a href="login.php">سجّل دخولك</a>
                </div>
            </div>
        </div>

        <script>
            // Crop rows
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
                row.appendChild(crop);
                }
                crops.appendChild(row);
            }

            // Password strength checker
            function checkStrength(val) {
                const segs = [document.getElementById('s1'), document.getElementById('s2'),
                            document.getElementById('s3'), document.getElementById('s4')];
                segs.forEach(s => s.className = 'strength-seg');

                if (val.length === 0) return;

                let score = 0;
                if (val.length >= 8)              score++;
                if (/[A-Z]/.test(val))            score++;
                if (/[0-9]/.test(val))            score++;
                if (/[^A-Za-z0-9]/.test(val))     score++;

                const cls = score <= 1 ? 'active-weak' : score <= 2 ? 'active-medium' : 'active-strong';
                for (let i = 0; i < score; i++) segs[i].classList.add(cls);
            }
        </script>
    </body>
</html>
