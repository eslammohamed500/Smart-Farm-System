<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();}
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

        if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Client") {
            header("location:../Auth/login.php");
            exit;}

include '../layout/header.php';
include '../layout/sidebar.php';

require_once '../../controllers/FarmerController.php';
$farmer_con = new FarmerController();

$currentFarmerId = $_SESSION['userId'] ?? 'F-123';
$currentFarmerName = $_SESSION['userName'] ?? 'Guest Farmer';
$myPlots = $farmer_con->getMyLeasedPlots($_SESSION['userId']);
$sharedPlots = $farmer_con->getMySharedPlots($_SESSION['userId']);

?>


<style>
    .mb-4 {
    margin-top: 70px;
    margin-bottom: 1.5rem !important;}
</style>


<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <?php echo htmlspecialchars($_GET['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($_GET['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form action="../../controllers/FarmerController.php" method="POST">
    <input type="hidden" name="action" value="sharePlot">
    
    <input type="hidden" name="farmer_id" value="<?php echo $currentFarmerId; ?>">

    <div class="row mb-4 align-items-center">
        <label class="col-sm-3 col-form-label fw-bold small text-muted text-uppercase">Welcome,</label>
        <div class="col-sm-9">
            <span class="fw-bold"><?php echo $currentFarmerName; ?></span>
        </div>
    </div>

    <div id="partners-container">
        <div class="partner-row mb-4">
            <div class="row align-items-center">
                <div class="col-sm-5">
                    <label class="fw-bold small text-uppercase mb-1">Partner Name</label>
                    <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-person-plus text-muted"></i></span>
                        <input type="text" class="form-control border-0 py-2" name="partner_names[]" placeholder="Partner Name" required>
                    </div>
                </div>
                <div class="col-sm-5">
                    <label class="fw-bold small text-uppercase mb-1">Share %</label>
                    <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-percent text-muted"></i></span>
                        <input type="number" class="form-control border-0 py-2" name="percentages[]" placeholder="e.g. 25" min="1" max="100" required>
                    </div>
                </div>
                <!-- زر الحذف سيظهر هنا فقط للشركاء الإضافيين -->
                <div class="col-sm-2 pt-4"></div>
            </div>
        </div>
    </div>

    <!-- زر الإضافة -->
    <button type="button" class="btn btn-sm btn-outline-secondary mb-4 rounded-pill px-3" onclick="addPartnerRow()">
        <i class="bi bi-plus-circle me-1"></i> Add Another Partner
    </button>


    <!-- اختيار الأرض من أراضي المزارع الحالية -->
    <div class="row mb-5 align-items-center">
        <label class="col-sm-3 col-form-label fw-bold text-uppercase small">Select Your Plot</label>
        <div class="col-sm-9">
            <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-grid"></i></span>
                <select class="form-select border-0 py-2" name="plot_id" required>
                    <option value="" selected disabled>Select one of your leased plots...</option>
                    <?php 
                    if (!empty($myPlots)) {
                        foreach ($myPlots as $p) {
                            echo "<option value='{$p['id']}'>Plot {$p['id']} ({$p['soil_type']})</option>";
                        }
                    } else {
                        echo "<option disabled>No leased plots available to share</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background-color: #6c5ce7; border: none;">SHARE</button>
        <a href="rent_plot.php" class="btn btn-warning px-5 py-2 fw-bold text-white">BACK</a>
    </div>
</form>

<script>
    function addPartnerRow() {
        const container = document.getElementById('partners-container');
        const newRow = document.createElement('div');
        newRow.className = 'partner-row mb-4 animate__animated animate__fadeIn'; // أضفنا أنيميشن بسيط
        
        newRow.innerHTML = `
            <div class="row align-items-center">
                <div class="col-sm-5">
                    <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-person-plus text-muted"></i></span>
                        <input type="text" class="form-control border-0 py-2" name="partner_names[]" placeholder="Partner Name" required>
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                        <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-percent text-muted"></i></span>
                        <input type="number" class="form-control border-0 py-2" name="percentages[]" placeholder="Share %" min="1" max="100" required>
                    </div>
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('.partner-row').remove()">
                        <i class="bi bi-trash fs-5"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newRow);
    }
</script>