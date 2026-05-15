<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();}
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

        if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Client") {
            header("location:../Auth/login.php");
            exit;}

    if (!isset($_SESSION['current_date'])) {
        $_SESSION['current_date'] = date('Y-m-d');}

    $userId = $_SESSION['userId'];
    $userName = $_SESSION['userName'];
    $userRole = $_SESSION['userRole'];

    require_once __DIR__ . '/../../controllers/VolunteerController.php';
    $controller = new VolunteerController();


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // CANCEL JOIN
        if (isset($_POST['action']) && $_POST['action'] === 'cancel_join') {
            $shiftId = (int) $_POST['shift_id'];
            if ($controller->cancelJoin($userId, $shiftId)) {
                $_SESSION['msg'] = "Cancelled successfully";} 
            else {
                $_SESSION['error'] = "Error cancelling";}
            header("Location: volunteer.php?page=volunteer");
            exit();
        }

        // JOIN SHIFT
        if (isset($_POST['shift_id'])) {
            $shiftId = (int) $_POST['shift_id'];
            $result = $controller->joinShift($userId, $shiftId, $userRole);
            if ($result === "joined") {
                $_SESSION['msg'] = "Joined +3 hours 🎉";}
            elseif ($result === "max_reached") {
                $_SESSION['error'] = "⚠️ You reached maximum hours";}
            elseif ($result === "already") {
                $_SESSION['error'] = "Already joined";}
            else {
                $_SESSION['error'] = "Something went wrong";}
            header("Location: volunteer.php?page=volunteer");
            exit();
        }

        // ADD HOUR
        // if (isset($_POST['action']) && $_POST['action'] === 'add_hour') {
        //     if ($controller->updateVolunteerHours($userId, 1.0)) {
        //         $_SESSION['msg'] = "Hour added";} 
        //     else {
        //         $_SESSION['error'] = "No shift to update";}
        //     header("Location: volunteer.php?page=volunteer");
        //     exit();
        // }

        // NEXT DAY
        if (isset($_POST['action']) && $_POST['action'] === 'next_day') {
            $_SESSION['current_date'] = date('Y-m-d', strtotime($_SESSION['current_date'] . ' +1 day'));
            $controller->advanceDay($userId, $_SESSION['current_date']);
            $_SESSION['msg'] = "New day started";
            header("Location: volunteer.php?page=volunteer");
            exit();}

        // WEATHER
        if (isset($_POST['action']) && $_POST['action'] === 'weather_override') {
            $weather = $_POST['weather_condition'] ?? 'Normal';
            $date = $_POST['date'];
            $controller->reportWeather($userId,$weather,$date);
            $_SESSION['msg'] = "Weather report sent to admin";
            header("Location: volunteer.php?page=volunteer");
            exit();
        }
    }

/*DATA */
    $userStats = $controller->serviceHourTracker($userId);
    $shifts = $controller->getAvailableShifts();
    $progressPercent = ($userStats['mandatory_hours'] > 0)? ($userStats['current_hours'] / $userStats['mandatory_hours']) * 100: 0;
    if ($progressPercent > 100) $progressPercent = 100;
?>
<?php include __DIR__ . "/../layout/header.php"; ?>
<?php include __DIR__ . "/../layout/sidebar.php"; ?>

<style>
    .main {
    margin-left: 130px;
    padding: 20px;}

    .mb-4 {
        /* margin-top: 70px; */
        margin-bottom: 1.5rem !important;}

</style>


<div class="main">

<!-- MESSAGES -->
    <?php if (!empty($_SESSION['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['msg']); endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['error']); endif; ?>
<!-- HEADER -->
<div class="container-fluid mb-4">
    <h2>🌿 Volunteer Dashboard</h2>
</div>

<div class="row">

    <!-- SHIFTS -->
    <div class="col-md-8">
        <div class="card-ui p-3">
            <h4>Available Shifts</h4>

            <table class="table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($shifts && count($shifts) > 0): ?>
                        <?php foreach ($shifts as $shift): ?>
                            <tr>
                                <td><?= htmlspecialchars($shift['title']) ?></td>
                                <td><?= date('Y-m-d', strtotime($shift['start_time'])) ?></td>

                                <?php
                                    $joined = $controller->isJoined($userId, $shift['id']);
                                ?>

                                <td>
                                    <?php if ($shift['status'] == 'open'): ?>

                                        <!-- JOIN -->
                                        <?php if ($joined): ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                Joined
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="shift_id" value="<?= $shift['id'] ?>">
                                                <button class="btn btn-success btn-sm">Join</button>
                                            </form>
                                        <?php endif; ?>
                                        <!-- CANCEL SHIFT -->
                                        <form method="POST" style="display:inline; margin-left:5px;">
                                            <input type="hidden" name="action" value="cancel_join">
                                            <input type="hidden" name="shift_id" value="<?= $shift['id'] ?>">

                                            <button class="btn btn-danger btn-sm"
                                                <?= !$joined ? 'disabled' : '' ?>
                                                onclick="return confirm('Cancel this shift?')">
                                                Cancel
                                            </button>
                                        </form>

                                    <?php else: ?>
                                        <button class="btn btn-dark btn-sm" disabled>Closed</button>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No shifts available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="col-md-4">

        <div class="card-ui p-3 mb-3">
            <h4>My Stats</h4>

            <p>
                <?= $userStats['current_hours'] ?> / <?= $userStats['mandatory_hours'] ?> hours
            </p>

            <div class="progress">
                <div class="progress-bar"
                    style="width: <?= $progressPercent ?>%">
                </div>
            </div>

            <form method="POST" class="mt-2">
                <input type="hidden" name="action" value="next_day">
                <button class="btn btn-warning w-100">Next Day</button>
            </form>
        </div>

        <div class="card-ui p-3">
            <h4>Weather Override</h4>

            <form method="POST">
                <input type="hidden" name="action" value="weather_override">
                <input type="hidden" name="date" value="<?= date('Y-m-d', strtotime($shift['start_time'])) ?>">

                <select name="weather_condition" class="form-control mb-2">
                    <option>Extreme Heat</option>
                    <option>Heavy Rain</option>
                    <option>Normal</option>
                </select>

                <button class="btn btn-danger w-100">Report</button>
            </form>
        </div>

    </div>

</div>
</div>

<?php include __DIR__ . "/../layout/footer.php"; ?>