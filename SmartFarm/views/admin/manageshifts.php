<?php
    session_start();
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Admin") {
        header("location:../Auth/login.php");
        exit;}


    require_once __DIR__ . "/../../Controllers/AdminController.php";
    $controller = new AdminController();

    /* ADD SHIFT */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['title'], $_POST['start_time'], $_POST['end_time'])) {

            $title = htmlspecialchars(trim($_POST['title']));
            $start = str_replace("T", " ", $_POST['start_time']) . ":00";
            $end   = str_replace("T", " ", $_POST['end_time']) . ":00";

            if ($controller->addShift($title, $start, $end)) {
                $msg = "Shift added successfully";
            } else {
                $error = "Error adding shift";
            }
        }
    }

    /* DELETE SHIFT */
    if (isset($_GET['delete'])) {
        $id = (int) $_GET['delete'];

        if ($controller->deleteShift($id)) {
            $msg = "Shift deleted successfully";
        } else {
            $error = "Error deleting shift";
        }
    }

    /* GET ALL SHIFTS*/
    $shifts = $controller->getAllShifts();

?>

<?php include __DIR__ . "/../layout/header.php"; ?>
<?php include __DIR__ . "/../layout/sidebar.php"; ?>

<style>.mb-4 {
    margin-top: 70px;
    margin-bottom: 1.5rem !important;}
</style>

<div class="container-fluid py-4">

    <h2 class="mb-4">🛠 Manage Shifts</h2>

    <!-- MESSAGES -->
    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- ADD SHIFT -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Add New Shift</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Time</label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                        <button class="btn btn-success w-100">Add Shift</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- SHIFTS TABLE -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">All Shifts</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($shifts)): ?>
                            <?php foreach ($shifts as $shift): ?>
                                <tr>
                                    <td><?= $shift['id'] ?></td>
                                    <td><?= htmlspecialchars($shift['title']) ?></td>
                                    <td><?= $shift['start_time'] ?></td>
                                    <td><?= $shift['end_time'] ?></td>
                                    <td>
                                        <a href="?delete=<?= (int)$shift['id'] ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Delete this shift?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center p-3">
                                    No shifts found
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../layout/footer.php"; ?>