<?php
include "../layout/header.php"; 
include "../layout/sidebar.php";

require_once __DIR__ . '/../../controllers/AdminController.php';
$admin = new AdminController();
require_once __DIR__ . '/../../Controllers/AuthController.php';
$auth = new AuthController();

// delet
if (isset($_GET['delete'])) {
    $admin->deleteUser($_GET['delete']);
    header("Location: users.php");
    exit();
}

// update
if (isset($_POST['update'])) {
    $admin->updateUser($_POST['id'],$_POST['name'],$_POST['email'],$_POST['role']);
    if ($_POST['id'] == $_SESSION['userId']) {
        session_unset();
        session_destroy();
        header("Location: ../Auth/login.php");
        exit();}
    header("Location: users.php");
    exit();}

$users = $admin->getAllUsers();
?>


<style>
    .mb-4{
        margin-top: 70px;}
</style>


<div class="container-fluid">

    <h2 class="mb-4">👥 إدارة المستخدمين</h2>

    <div class="card-ui">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>الاسم</th>
                    <th>الإيميل</th>
                    <th>الدور</th>
                    <th>تحكم</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= $u['name'] ?></td>
                    <td><?= $u['email'] ?></td>
                    <td><?= $u['role'] ?></td>

                    <td>
                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
                        onclick="return confirm('متأكد؟')">🗑️</a>

                        <button class="btn btn-primary btn-sm"
                            onclick="editUser(<?= $u['id'] ?>,'<?= $u['name'] ?>','<?= $u['email'] ?>','<?= $u['role'] ?>')">
                            ✏️
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>

<!-- Modal تعديل -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">
<div class="modal-header">
    <h5 class="modal-title">✏️ تعديل المستخدم</h5>
</div>

<div class="modal-body">
    <input type="hidden" name="id" id="id">

    <input class="form-control mb-2" name="name" id="name" placeholder="الاسم">
    <input class="form-control mb-2" name="email" id="email" placeholder="الإيميل">

    <select class="form-control" name="role" id="role">
        <option>user</option>
        <option>admin</option>
    </select>
</div>

<div class="modal-footer">
    <button type="submit" name="update" class="btn btn-success">حفظ</button>
</div>
</form>

</div>
</div>
</div>

<script>
    function editUser(id,name,email,role){
        document.getElementById('id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;
        document.getElementById('role').value = role;

        var modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();}
</script>

<?php include "../layout/footer.php"; ?>