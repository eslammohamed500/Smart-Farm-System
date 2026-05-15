<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Client") {
    header("location:../Auth/login.php");
    exit;
}

require_once '../../controllers/FarmerController.php';

$farmer_con = new FarmerController();
$tools = $farmer_con->getAvailableTools();
$myBorrowed = $farmer_con->getMyBorrowedTools($_SESSION['userId']);
?>

<?php include __DIR__ . "/../layout/header.php"; ?>
<?php include __DIR__ . "/../layout/sidebar.php"; ?>

<style>
    .mb-4 { 
        /* margin-top: 70px;  */
        margin-bottom: 1.5rem !important; }
    .tool-card { border-radius: 10px; transition: 0.3s; }
    .tool-card:hover { transform: translateY(-5px); }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tools Library</h2>
        <form action="../../controllers/FarmerController.php" method="POST" class="d-flex gap-2">
            <input type="hidden" name="action" value="borrowTool">
            <select class="form-select rounded-pill" name="toolId" required style="width: 200px;">
                <option value="" disabled selected>Quick Borrow...</option>
                <?php foreach ($tools as $tool): ?>
                    <option value="<?= $tool['id']; ?>"><?= htmlspecialchars($tool['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary rounded-pill">Borrow</button>
        </form>
    </div>

    <?php if (!empty($myBorrowed)): ?>
    <div class="card shadow-sm border-0 mb-5 bg-light">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-clock-history me-2"></i>My Currently Borrowed Tools</h5>
            <div class="row g-3">
                <?php foreach ($myBorrowed as $bTool): ?>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                            <div>
                                <strong><?= htmlspecialchars($bTool['name']); ?></strong><br>
                                <small class="text-danger">Due: <?= date('M d', strtotime($bTool['due_date'])); ?></small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger rounded-pill" 
                                    onclick="setupReturnModal('<?= $bTool['tool_id']; ?>', '<?= htmlspecialchars($bTool['name']); ?>')" 
                                    data-bs-toggle="modal" data-bs-target="#returnToolModal">
                                Return
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <hr>
    <?php endif; ?>

    <h5 class="mb-3">Available for Rent</h5>
    <div class="row g-4">
        <?php if (!empty($tools)): ?>
            <?php foreach ($tools as $tool): ?>
                <div class="col-md-3">
                    <div class="card tool-card shadow-sm h-100">
                        <img src="../../uploads/tools/<?= $tool['image'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($tool['name']) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($tool['description'] ?? 'No description') ?></p>
                            <span class="badge bg-success"><?= $tool['status'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No tools found</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="returnToolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow border-0">
            <div class="modal-header bg-dark text-white rounded-top-4">
                <h5 class="modal-title">Return Tool: <span id="displayToolName" class="text-info"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div id="qrSection">
                    <i class="bi bi-qr-code-scan display-1 text-primary mb-3"></i>
                    <p class="fw-bold">Cleaning Station Verification Required</p>
                    <p class="text-muted small">Please scan the QR code at the station to verify the tool is cleaned.</p>
                    <button type="button" class="btn btn-primary rounded-pill px-5 shadow-sm" onclick="simulateScan()">
                        <i class="bi bi-camera me-2"></i>Scan QR Code
                    </button>
                </div>
                <div id="confirmSection" style="display: none;">
                    <i class="bi bi-check-circle-fill display-1 text-success mb-3"></i>
                    <p class="fw-bold text-success">Verification Successful!</p>
                    <form action="../../controllers/FarmerController.php" method="POST">
                        <input type="hidden" name="action" value="returnTool">
                        <input type="hidden" name="toolId" id="modalToolId">
                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow">CONFIRM RETURN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function setupReturnModal(id, name) {
        document.getElementById('modalToolId').value = id;
        document.getElementById('displayToolName').innerText = name;
        document.getElementById('qrSection').style.display = 'block';
        document.getElementById('confirmSection').style.display = 'none';
    }
    function simulateScan() {
        document.getElementById('qrSection').style.display = 'none';
        document.getElementById('confirmSection').style.display = 'block';
    }
</script>

<?php include __DIR__ . "/../layout/footer.php"; ?>