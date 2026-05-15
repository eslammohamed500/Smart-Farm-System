<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();}
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

        if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Client") {
            header("location:../Auth/login.php");
            exit;}

    require_once '../../controllers/FarmerController.php';
    $farmer_con = new FarmerController();



include '../layout/header.php';
include '../layout/sidebar.php';
require_once '../../controllers/RentController.php';

$rent_con = new RentController();
$av = $rent_con->getAvailablePlots();

$currentFarmerId = $_SESSION['userId'] ?? 'F-123';
$currentFarmerName = $_SESSION['userName'] ?? 'Guest Farmer';
$myPlots = $farmer_con->getMyLeasedPlots($_SESSION['userId']);
$sharedPlots = $farmer_con->getMySharedPlots($_SESSION['userId']);
?>

<style>
    .p-4 {
    margin-bottom: 40px;
    padding: 1.5rem !important;}
    .mb-4 {
    margin-bottom: 1.5rem !important;}
</style>


    <div class="container-fluid mt-4 mb-5">

        <div class="p-4">
            <h4 class="mb-4 text-muted">Details</h4>
            <form action="../../controllers/FarmerController.php" method="POST">
                <input type="hidden" name="farmer_id" value="<?php echo $currentFarmerId; ?>">
                <input type="hidden" name="action" value="rentPlot">

                <!-- لو عاوز تعرض الاسم للمزارع كنوع من التأكيد بس -->
                <div class="row mb-4 align-items-center">
                    <label class="col-sm-3 col-form-label fw-bold small text-muted text-uppercase">Welcome,</label>
                    <div class="col-sm-9">
                        <span class="fw-bold"><?php echo $currentFarmerName; ?></span>
                    </div>
                </div>

                <div class="row mb-4 align-items-center">
                    <label class="col-sm-3 col-form-label fw-bold text-uppercase small">Rent Duration</label>
                    <div class="col-sm-9">
                        <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-calendar-event text-muted"></i></span>
                            <input type="text" class="form-control border-0 py-2" name="duration" placeholder="e.g. 6 Months">
                        </div>
                    </div>
                </div>

                <div class="row mb-5 align-items-center">
                    <label class="col-sm-3 col-form-label fw-bold text-uppercase small">Select Plot</label>
                    <div class="col-sm-9">
                        <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                            <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-grid text-muted"></i></span>
                            <select class="form-select border-0 py-2 shadow-none" name="plot_id">
                                <option value="" selected disabled>Choose a plot...</option>
                                <?php 
                                    foreach ($av as $av){
                                        ?>
                                            <option value="<?php echo $av["id"] ?>"> <?php echo $av["id"] ?> </option>
                                        <?php
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-start mt-4 ps-1">
                    <button type="submit" name="confirmRental" class="btn btn-primary px-5 rounded-3 shadow-sm py-2 fw-bold" style="background-color: #6c5ce7; border: none;">CONFIRM</button>
                    <a href="farmer.php" class="btn btn-warning px-5 rounded-3 shadow-sm py-2 fw-bold text-white">BACK</a>
                </div>
            </form>
        </div>


        <div class="row">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <!-- Land & Rental Section -->
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Land & Rental</h5>
                    </div>
                    <div class="card-body">
                        <h6>Leased Plots</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Soil Type</th>
                                        <th>Moisture Level</th>
                                        <th>Sunlight Hours</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($myPlots)): 
                                        foreach ($myPlots as $plot): 
                                    ?>
                                        <tr>
                                            <td><?php echo $plot['soil_type'] ?? 'N/A'; ?></td>
                                            <td><?php echo $plot['moisture_level'].'%' ?? 'N/A'; ?></td>
                                            <td><?php echo $plot['soil_type']; ?></td>
                                            <td><span class="badge bg-success"><?php echo $plot['status']; ?></span></td>
                                            <td>
                                                <form action="../../controllers/FarmerController.php" method="POST" onsubmit="return confirm('Are you sure you want to return this plot?');">
                                                    <input type="hidden" name="action" value="returnPlot">
                                                    <input type="hidden" name="plot_id" value="<?php echo $plot['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                        <i class="bi bi-arrow-return-left me-1"></i>Return
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No plots leased yet. Start by renting one!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                                <div class="d-flex gap-2 mb-4">
                                    <a href="share_plot.php" class="btn btn-outline-primary">
                                        <i class="bi bi-share me-2"></i>Share Your Plot
                                    </a>
                                </div>
                        </div>
                        <h6 class="mt-4">Shared Plots</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Plot ID</th>
                                        <th>Partner Name</th>
                                        <th>Your Share %</th>
                                        <th>Soil Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($sharedPlots)): 
                                        foreach ($sharedPlots as $s_plot): 
                                    ?>
                                        <tr>
                                            <td><?php echo $s_plot['id']; ?></td>
                                            <td>
                                                <?php 
                                                // لو أنا المالك، اعرض اسم الشريك، ولو أنا الشريك اعرض اسم المالك
                                                echo ($_SESSION['userName'] == $s_plot['owner_name']) ? $s_plot['partner_name'] : $s_plot['owner_name']; 
                                                ?>
                                            </td>
                                            <td><?php echo $s_plot['percentage']."%"; ?></td>
                                            <td><?php echo $s_plot['soil_type']; ?></td>
                                            <td><span class="badge bg-info">Shared</span></td>
                                        </tr>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No shared plots found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    </div>
<?php include '../layout/footer.php'; ?>