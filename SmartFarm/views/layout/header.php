<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = $_GET['page'] ?? 'dashboard';

require_once __DIR__ . '/../../Controllers/AdminController.php';
require_once __DIR__ . '/../../Controllers/FarmerController.php';

$adminController = new AdminController();
$farmerController = new FarmerController();


$adminNotifications = [];
$clientNotifications = [];
$notifCount = 0;

if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin') {
    $adminNotifications = $adminController->getWeatherRequests();
    $notifCount = is_array($adminNotifications) ? count($adminNotifications) : 0;
}

elseif (isset($_SESSION["userRole"]) && $_SESSION["userRole"] === "Client") {
    $clientNotifications = $farmerController->getNotifications($_SESSION["userId"]);
    $notifCount = is_array($clientNotifications) ? count($clientNotifications) : 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Smart Farm</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Poppins',sans-serif;
            background:
                radial-gradient(circle at top left,#1d4ed8 0%,transparent 25%),
                radial-gradient(circle at bottom right,#22c55e 0%,transparent 25%),
                #edf2f7;
            background-attachment:fixed;
            overflow-x:hidden;
        }

        :root{
            --green:#22c55e;
            --green-dark:#16a34a;
            --dark:#0f172a;
            --dark2:#1e293b;
            --white:#fff;
            --gray:#94a3b8;
        }

        /* ================= TOPBAR ================= */

        .topbar{

            position:fixed;

            top:0;
            left:0;

            width:100%;
            height:78px;

            padding:0 30px;

            display:flex;
            justify-content:space-between;
            align-items:center;

            background:rgba(15,23,42,0.82);

            backdrop-filter:blur(14px);

            border-bottom:1px solid rgba(255,255,255,0.08);

            z-index:9999;
        }

        .logo{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .logo-icon{

            width:52px;
            height:52px;

            border-radius:16px;

            display:flex;
            align-items:center;
            justify-content:center;

            background:linear-gradient(135deg,#22c55e,#16a34a);

            color:white;

            font-size:22px;

            box-shadow:
                0 10px 30px rgba(34,197,94,0.4);
        }

        .logo-text h2{
            color:white;
            font-size:23px;
            font-weight:700;
            margin:0;
        }

        .logo-text span{
            color:#cbd5e1;
            font-size:12px;
            letter-spacing:2px;
        }

        .topbar-right{
            display:flex;
            align-items:center;
            gap:18px;
        }

        /* ================= NOTIFICATION ================= */

        .notif-btn{

            width:46px;
            height:46px;

            border:none;

            border-radius:14px;

            background:rgba(255,255,255,0.08);

            color:white;

            font-size:18px;

            position:relative;

            transition:0.3s;
        }

        .notif-btn:hover{
            background:rgba(255,255,255,0.18);
            transform:translateY(-2px);
        }

        .notif-count{

            position:absolute;

            top:-5px;
            right:-5px;

            width:20px;
            height:20px;

            border-radius:50%;

            background:#ef4444;

            display:flex;
            justify-content:center;
            align-items:center;

            font-size:11px;
            font-weight:600;
        }

        .notif-box{

            display:none;

            position:absolute;

            top:70px;
            right:0;

            width:350px;

            border-radius:22px;

            overflow:hidden;

            background:rgba(255,255,255,0.96);

            backdrop-filter:blur(12px);

            box-shadow:
                0 20px 45px rgba(0,0,0,0.18);

            animation:fadeDown .25s ease;
        }

        .notif-header{

            padding:18px;

            background:linear-gradient(135deg,#22c55e,#16a34a);

            color:white;

            font-weight:600;
        }

        .notif-item{

            padding:16px 18px;

            border-bottom:1px solid #f1f5f9;

            transition:0.25s;
        }

        .notif-item:hover{
            background:#f8fafc;
        }

        .notif-date{
            color:#64748b;
            font-size:12px;
            margin-top:6px;
        }

        .notif-actions{
            margin-top:8px;
        }

        .approve{
            color:#16a34a;
            text-decoration:none;
            font-size:13px;
            font-weight:600;
        }

        .reject{
            color:#ef4444;
            text-decoration:none;
            margin-left:12px;
            font-size:13px;
            font-weight:600;
        }

        /* ================= LOGOUT ================= */

        .logout-btn{

            padding:11px 18px;

            border-radius:14px;

            text-decoration:none;

            background:linear-gradient(135deg,#ef4444,#dc2626);

            color:white;

            font-size:14px;
            font-weight:600;

            transition:0.3s;

            box-shadow:
                0 10px 25px rgba(239,68,68,0.3);
        }

        .logout-btn:hover{

            transform:
                translateY(-2px)
                scale(1.02);

            color:white;
        }

        /* ================= MAIN ================= */

        .main{
            margin-left:270px;
            margin-top:95px;
            padding:35px;
            min-height:100vh;
        }

        /* ================= CARD ================= */

        .dashboard-card{

            background:rgba(255,255,255,0.72);

            backdrop-filter:blur(14px);

            border-radius:28px;

            padding:28px;

            border:1px solid rgba(255,255,255,0.28);

            box-shadow:
                0 12px 35px rgba(0,0,0,0.08);

            transition:0.35s;
        }

        .dashboard-card:hover{

            transform:translateY(-8px);

            box-shadow:
                0 20px 45px rgba(0,0,0,0.12);
        }

        /* ================= ANIMATION ================= */

        @keyframes fadeDown{

            from{
                opacity:0;
                transform:translateY(-12px);
            }

            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        /* ================= MOBILE ================= */

        @media(max-width:768px){

            .main{
                margin-left:95px;
                padding:20px;
            }

            .logo-text{
                display:none;
            }

            .notif-box{
                width:300px;
            }
        }

    </style>
</head>

<body>

<div class="topbar">
    <div class="logo">
        <div class="logo-icon">
            <i class="fa-solid fa-seedling"></i>
        </div>
        <div class="logo-text">
            <h2>Smart Farm</h2>
            <span>Control Center</span>
        </div>
    </div>
    <div class="topbar-right">
        <?php if (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Admin'): ?>
            <div style="position:relative;">
                <button onclick="toggleNotif()" class="notif-btn">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="notif-count"><?= $notifCount ?></span>
                    <?php endif; ?>
                </button>
                <div id="notifBox" class="notif-box">
                    <div class="notif-header">🌦 Weather Requests</div>
                    <?php if (!empty($adminNotifications)): ?>
                        <?php foreach ($adminNotifications as $r): ?>
                            <div class="notif-item">
                                <div>🌤 <?= htmlspecialchars($r['weather_condition']) ?></div>
                                <div class="notif-date"><?= $r['request_date'] ?></div>
                                <div class="notif-actions">
                                    <a href="../../views/admin/admin.php?approve=<?= $r['id'] ?>" class="approve">Approve</a>
                                    <a href="../../views/admin/admin.php?reject=<?= $r['id'] ?>" class="reject">Reject</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:18px;">No pending requests</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (isset($_SESSION['userRole']) && $_SESSION['userRole'] === 'Client'): ?>
            <div style="position:relative;">
                <button onclick="toggleNotif()" class="notif-btn">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($notifCount > 0): ?>
                        <span class="notif-count"><?= $notifCount ?></span>
                    <?php endif; ?>
                </button>
                <div id="notifBox" class="notif-box">
                    <div class="notif-header">🔔 Alerts</div>
                    <?php if (!empty($clientNotifications)): ?>
                        <?php foreach ($clientNotifications as $n): ?>
                            <div class="notif-item">
                                <div><?= htmlspecialchars($n['message']) ?></div>
                                <div class="notif-date"><?= $n['created_at'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:18px;">No new alerts</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <a href="../../Controllers/AuthController.php?action=logout" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i>
            Logout
        </a>
    </div>
</div>

<script>

    function toggleNotif(){

        const box = document.getElementById("notifBox");

        box.style.display =
            box.style.display === "block"
            ? "none"
            : "block";
    }

    document.addEventListener("click", function(e){

        const box = document.getElementById("notifBox");

        const btn = e.target.closest(".notif-btn");

        if(!e.target.closest("#notifBox") && !btn){

            box.style.display = "none";
        }
    });

</script>