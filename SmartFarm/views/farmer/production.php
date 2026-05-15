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

    $insights = $farmer_con->getSmartInsights();
    $spoilageRisk = $insights['spoilageRisk']; 
    $predictionMsg = $insights['message'];


    // // Dummy user data for the dashboard view
    // $karmaPoints = 1000; // Earned from produce donations
    // $communityPoints = 400; // Earned from volunteer contributions

    $myProduce = $farmer_con->getMyProduce($_SESSION['userId']);
    $bankSeeds = $farmer_con->getAllBankSeeds();
    $userData = $farmer_con->getUserData($_SESSION['userId']);
    // $karmaPoints = $userData['karma_points'] ?? 0;
    // $communityPoints = $userData['community_points'] ?? 0;
    // $availableTools = $farmer_con->getAvailableTools();
    // $myBorrowed = $farmer_con->getMyBorrowedTools($_SESSION['userId']);


    include '../layout/header.php';
    include '../layout/sidebar.php';
?>

<style>
    .mb-4 {
    /* margin-top: 70px; */
    margin-bottom: 1.5rem !important;}
    .tool-card {
        border-radius: 10px;
        transition: 0.3s;
    }
    .tool-card:hover {
        transform: translateY(-5px);
    }
</style>




<div class="container-fluid mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Production </h2>
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

        <!-- Inventory & Seed Bank Section -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Inventory & Seed Bank</h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold"><i class="bi bi-arrow-left-right text-success me-2"></i>Seed Exchange Program</h6>
                    <p class="text-muted small">Deposit high-quality seeds to withdraw at a 1:2 ratio!</p>

                    <form class="mb-4" action="../../controllers/FarmerController.php" method="POST">
                        <input type="hidden" name="action" value="exchangeSeeds">
                        <div class="row g-2">
                            <div class="col-md-12 mb-2">
                                <select class="form-select rounded-pill shadow-sm" name="seedType" required>
                                    <option value="" disabled selected>Which seed are you depositing?</option>
                                    <?php if (isset($_SESSION['my_seeds']) && !empty($_SESSION['my_seeds'])): 
                                        foreach ($_SESSION['my_seeds'] as $seed): ?>
                                            <option value="<?php echo $seed['seed_name']; ?>">
                                                <?php echo $seed['seed_name']; ?> (Available: <?php echo $seed['quantity']; ?>)
                                            </option>
                                    <?php endforeach; else: ?>
                                        <option value="" disabled>You have no seeds to deposit</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select class="form-select rounded-pill shadow-sm" name="seedQuality" required>
                                    <option value="Standard">Standard Quality (1:1)</option>
                                    <option value="High">High-Quality (1:2 Ratio)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="number" class="form-control rounded-pill shadow-sm" placeholder="Qty" name="quantity" required min="1">
                            </div>
                            <div class="col-12 mt-3">
                                <button class="btn btn-success w-100 rounded-pill shadow-sm fw-bold" type="submit">DEPOSIT & EXCHANGE</button>
                            </div>
                        </div>
                    </form>


                    <hr>

                    <h6 class="fw-bold mb-3"><i class="bi bi-archive text-primary me-2"></i>My Seed Stock</h6>
                    <div class="p-3 bg-light rounded-4 border shadow-sm mb-3">
                        <?php if (isset($_SESSION['my_seeds']) && !empty($_SESSION['my_seeds'])): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($_SESSION['my_seeds'] as $seed): ?>
                                    <div class="badge bg-white text-dark border p-2 px-3 rounded-pill shadow-sm">
                                        <i class="bi bi-flower1 text-success me-1"></i>
                                        <?php echo htmlspecialchars($seed['seed_name']); ?>: <strong><?php echo $seed['quantity']; ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="small text-muted text-center mb-0">Your seed bag is empty.</p>
                        <?php endif; ?>
                    </div>

                    <button class="btn btn-outline-primary w-100 rounded-pill fw-bold mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#seedStoreSection">
                        <i class="bi bi-cart-plus me-2"></i>OPEN SEED STORE
                    </button>

                    
                    <div class="collapse" id="seedStoreSection">
                        <div class="p-3 bg-white border rounded-4 shadow-sm mb-4">
                            <h6 class="small fw-bold text-uppercase text-primary mb-3">
                                <i class="bi bi-bank me-2"></i>Official Seed Bank Stock
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Seed Variety</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php foreach ($bankSeeds as $bSeed): 
                                            $isAvailable = $bSeed['available_quantity'] > 0;
                                        ?>
                                            <tr>
                                                <td><strong><?php echo $bSeed['seed_name']; ?></strong></td>
                                                <td><span class="badge bg-light text-dark"><?php echo $bSeed['price_points']; ?> pts</span></td>
                                                <td>
                                                    <?php if ($isAvailable): ?>
                                                        <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i>In Stock (<?php echo $bSeed['available_quantity']; ?>)</span>
                                                    <?php else: ?>
                                                        <span class="text-danger small"><i class="bi bi-x-circle-fill me-1"></i>Out of Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($isAvailable): ?>
                                                        <form action="../../controllers/FarmerController.php" method="POST" class="d-flex gap-1">
                                                            <input type="hidden" name="action" value="buySeeds">
                                                            <input type="hidden" name="seedName" value="<?php echo $bSeed['seed_name']; ?>">
                                                            <input type="hidden" name="seedCost" value="<?php echo $bSeed['price_points']; ?>">
                                                            <input type="number" name="buyQuantity" value="1" min="1" max="<?php echo $bSeed['available_quantity']; ?>" class="form-control form-control-sm rounded-pill" style="width: 60px;">
                                                            <button type="submit" class="btn btn-primary btn-sm rounded-pill"><i class="bi bi-cart-fill"></i></button>
                                                        </form>
                                                    <?php else: ?>
                                                        <button class="btn btn-secondary btn-sm rounded-pill" disabled>N/A</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marketplace & Surplus -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Marketplace & Surplus</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-uppercase small fw-bold mb-3 text-muted">Post Produce to Marketplace</h6>
                    <form action="../../controllers/FarmerController.php" method="POST">
                        <input type="hidden" name="action" value="postProduce">

                        <div class="mb-3">
                            <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-basket text-success"></i></span>
                                <input type="text" class="form-control border-0 py-2" name="produceName" placeholder="Produce Name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-box-seam text-success"></i></span>
                                <input type="number" class="form-control border-0 py-2" name="produceQuantity" placeholder="Quantity" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group border rounded-pill overflow-hidden bg-white shadow-sm">
                                <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-currency-dollar text-success"></i></span>
                                <input type="number" class="form-control border-0 py-2" name="price" placeholder="Price per Unit" step="0.01" min="0" required>
                            </div>
                        </div>

                        <!-- Allergy Guard Tags -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted ps-2">ALLERGY GUARD TAGS (MANDATORY)</label>
                            <div class="p-3 border rounded-4 bg-light shadow-sm">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="allergyTags[]" value="Nut-Free" id="tagNutFree">
                                    <label class="form-check-label small" for="tagNutFree">Nut-Free Facility</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="allergyTags[]" value="Organic" id="tagOrganic">
                                    <label class="form-check-label small" for="tagOrganic">100% Organic</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allergyTags[]" value="Nightshade" id="tagNightshade">
                                    <label class="form-check-label small" for="tagNightshade">Nightshade Family</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold shadow-sm" style="background-color: #27ae60;">
                            POST PRODUCE
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-shop me-2"></i>My Marketplace Listings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produce Name</th>
                                        <th>Quantity</th>
                                        <th>Allergy Guard Tags</th>
                                        <th>Post Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($myProduce)): 
                                        foreach ($myProduce as $item): 
                                    ?>
                                        <tr>

                                            <td class="fw-bold"><?php echo $item['produce_name']; ?></td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo $item['quantity']; ?> Kg
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $tags = explode(", ", $item['allergy_tags']);
                                                foreach ($tags as $tag): 
                                                ?>
                                                    <span class="badge bg-light text-dark border"><?php echo $tag; ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo $item['post_date']; ?>
                                            </td>

                                            <td>
                                                <form action="../../controllers/FarmerController.php" method="POST" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="action" value="deleteProduce">
                                                    <input type="hidden" name="produceName" value="<?php echo $item['produce_name']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php 
                                        endforeach; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">You haven't posted any produce yet.</td>
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
</div>
<?php include '../layout/footer.php'; ?>
