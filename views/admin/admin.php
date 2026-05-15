<?php
    session_start();
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Admin") {
        header("location:../Auth/login.php");
        exit;}

    require_once __DIR__ . '/../../Controllers/AdminController.php';
    $admin = new AdminController();
    $stats = $admin->getStats();
    $logs = $adminController->getAuditLogs();


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'schedule_irrigation') {
            $admin->schedule_irrigation($_POST);}}

        if (isset($_GET['approve'])) {
            $id = (int)$_GET['approve'];
            $req = $admin->getRequestById($id);
            if ($req) {
                $admin->approveWeather($id, $req['request_date']);}
            header("Location: admin.php");
            exit();}

        if (isset($_GET['reject'])) {
            $id = (int)$_GET['reject'];
            $admin->rejectWeather($id);
            header("Location: admin.php");
            exit();}
        if (isset($_GET['pdf'])) {
            $adminController->exportPDF();
            exit();}


?>
<?php include __DIR__ . "/../layout/header.php"; ?>
<?php include __DIR__ . "/../layout/sidebar.php"; ?>

<style>
    .mt-4 {
    margin-top: 3.5rem !important;}
</style>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <?php unset($_SESSION['success']); ?>
<?php endif; ?>


<div class="container mt-4">
    <h2>🌿 Admin Dashboard</h2>

    <div class="row mt-4">

        <div class="col-md-4">
            <div class="card p-3 shadow">
                <h4>👤 Users</h4>
                <h2><?= $stats['users'] ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow">
                <h4>🛠 Tools</h4>
                <h2><?= $stats['tools'] ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 shadow">
                <h4>📜 Audit Logs</h4>
                <h2><?= $stats['logs'] ?></h2>
            </div>
        </div>


        <div class="dashboard-card" style="margin-top:30px;">
            <h3 style="font-weight:700; margin-bottom:15px;">
            💧 Schedule Irrigation
            </h3>
            <form method="POST" action="admin.php">
                <input type="hidden" name="action" value="schedule_irrigation">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
                <br>
                <label>Time</label>
                <input type="time" name="time" class="form-control" required>
                <br>
                <label>Message</label>
                <textarea name="message" class="form-control" placeholder="Irrigation will start soon..."></textarea>
                <br>
                <button class="btn btn-success w-100">
                    🚀 Schedule & Notify All Users
                </button>
            </form>
        </div>
        <a href="?pdf=1" class="btn btn-success w-20"  style="margin-top:70px;">
            <button>Export PDF</button>
        </a>
    </div>
</div>

<?php include __DIR__ . "/../layout/footer.php"; ?>