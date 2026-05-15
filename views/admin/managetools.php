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

    if (isset($_GET['delete'])) {
        $admin->deleteTool($_GET['delete']);
        header("Location: managetools.php");
        exit();}

    if (isset($_POST['add'])) {
        $admin->addTool($_POST['name'], $_POST['description'], $_POST['quantity']);
        header("Location: managetools.php");
        exit();}

    $tools = $admin->getAllTools();
?>

<?php include __DIR__ . "/../layout/header.php"; ?>
<?php include __DIR__ . "/../layout/sidebar.php"; ?>

<style>
    .mb-4 { margin-top: 70px; }
</style>

<div class="container-fluid">

    <h2 class="mb-4">🛠️ Manage Tools</h2>

    <!-- add a tool -->
    <div class="card-ui mb-3">
        <form method="POST" enctype="multipart/form-data" class="row g-2">

            <div class="col-md-3">
                <input type="text" name="name" class="form-control" placeholder="Tool Name" required>
            </div>

            <div class="col-md-4">
                <input type="text" name="description" class="form-control" placeholder="Description">
            </div>

            <div class="col-md-2">
                <input type="number" name="quantity" class="form-control" placeholder="Quantity" required>
            </div>

            <div class="col-md-2">
                <input type="file" name="image" class="form-control">
            </div>


            <div class="col-md-2">
                <button type="submit" name="add" class="btn btn-success w-100">Add</button>
            </div>

        </form>
    </div>

<!-- table -->
    <div class="card-ui">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Availability</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tools as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= $t['name'] ?></td>
                    <td><?= $t['description'] ?></td>
                    <td><?= $t['quantity'] ?></td>
                    <td>
                        <?php if($t['availability'] == 'available'): ?>
                            <span class="badge bg-success">Available</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Unavailable</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="?delete=<?= $t['id'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete tool?')">
                            🗑️
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>

    </div>
</div>

<?php include __DIR__ . "/../layout/footer.php"; ?>