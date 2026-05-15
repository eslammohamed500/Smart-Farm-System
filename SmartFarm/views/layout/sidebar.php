<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = $_GET['page'] ?? 'dashboard';

$role = strtolower($_SESSION['userRole'] ?? '');



?>

<style>

    .sidebar{

        position:fixed;

        top:78px;
        left:0;

        width:270px;
        height:100vh;

        padding:22px 16px;

        background:rgba(15,23,42,0.88);

        backdrop-filter:blur(14px);

        border-right:1px solid rgba(255,255,255,0.08);

        z-index:999;
    }

    .sidebar-title{

        color:#94a3b8;

        font-size:12px;

        letter-spacing:2px;

        margin:10px 14px 18px;

        text-transform:uppercase;
    }

    .sidebar a{

        display:flex;
        align-items:center;
        gap:14px;

        padding:15px 18px;

        margin-bottom:12px;

        border-radius:18px;

        color:#cbd5e1;

        text-decoration:none;

        font-size:15px;
        font-weight:500;

        transition:0.3s;
    }

    .sidebar a:hover{

        background:rgba(255,255,255,0.08);

        color:white;

        transform:translateX(6px);
    }

    .sidebar a.active{

        background:
            linear-gradient(135deg,#22c55e,#16a34a);

        color:white;

        box-shadow:
            0 10px 25px rgba(34,197,94,0.3);
    }

    .sidebar a i{
        width:20px;
        text-align:center;
    }

    /* MOBILE */

    @media(max-width:768px){

        .sidebar{
            width:95px;
            padding:20px 10px;
        }

        .sidebar-title{
            display:none;
        }

        .sidebar a span{
            display:none;
        }

        .sidebar a{
            justify-content:center;
        }
    }

</style>

<div class="sidebar">

    <div class="sidebar-title">
        Navigation
    </div>

    <?php if ($role == 'client'): ?>

        <a href="../farmer/farmer.php"
            class="<?= $currentPage=='farmer' ? 'active' : '' ?>">
            <i class="fa-solid fa-leaf"></i>
            <span>Farmer Dashboard</span>
        </a>

        <a href="../farmer/production.php"
            class="<?= $currentPage=='production' ? 'active' : '' ?>">
            <i class="fa-solid fa-seedling"></i>
            <span>Production</span>
        </a>

        <a href="../tools/tools.php"
            class="<?= $currentPage=='tools' ? 'active' : '' ?>">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <span>Tools</span>
        </a>

        <a href="../farmer/store.php" class="<?= $currentPage=='store' ? 'active' : '' ?>">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Karma Store</span>
        </a>

        <a href="../volunteer/volunteer.php" class="<?= $currentPage=='volunteer' ? 'active' : '' ?>">
            <i class="fa-solid fa-handshake-angle"></i>
            <span>Volunteer</span>
        </a>

        <a href="../farmer/rent_plot.php" class="<?= $currentPage=='Rent a Plot' ? 'active' : '' ?>">
            <i class="fa-solid fa-tractor"></i>
            <span>Rent a Plot</span>    
        </a>

    <?php endif; ?>

    <?php if ($role == 'admin'): ?>

        <a href="../admin/admin.php"
           class="<?= $currentPage=='admin' ? 'active' : '' ?>">

            <i class="fa-solid fa-chart-line"></i>
            <span>Dashboard</span>

        </a>

        <a href="../users/users.php"
           class="<?= $currentPage=='users' ? 'active' : '' ?>">

            <i class="fa-solid fa-users"></i>
            <span>Users</span>

        </a>

        <a href="../admin/manageshifts.php"
           class="<?= $currentPage=='manageshifts' ? 'active' : '' ?>">

            <i class="fa-solid fa-calendar-check"></i>
            <span>Manage Shifts</span>

        </a>

        <a href="../admin/managetools.php"
           class="<?= $currentPage=='managetools' ? 'active' : '' ?>">

            <i class="fa-solid fa-toolbox"></i>
            <span>Manage Tools</span>

        </a>

    <?php endif; ?>

</div>

<div class="main">