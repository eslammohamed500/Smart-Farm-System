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

    $products = $farmer_con->getMarketplaceItems($_SESSION['userId']);



    include '../layout/header.php';
    include '../layout/sidebar.php';

?>

<div class="container mt-4">

    <h3 class="text-success">
        🛒 Karma Store
    </h3>

    <div class="row mt-3">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success text-center">
                <?php echo $_GET['success']; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <h5 class="fw-bold text-primary">
                                <?php echo $item['produce_name']; ?>
                            </h5>
                            <p class="text-muted mb-1">
                                Quantity: <?php echo $item['quantity']; ?>
                            </p>
                            <p class="text-muted mb-2">
                                Allergy: <?php echo $item['allergy_tags']; ?>
                            </p>
                            <h6 class="text-warning">
                                ⭐ Price: <?php echo $item['price']; ?> Karma
                            </h6>
                            <form method="POST" action="../../controllers/FarmerController.php">
                                <input type="hidden" name="action" value="buyProduct">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button class="btn btn-success w-100 mt-2">
                                    Buy with Karma
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">No products available right now 🌾</p>
        <?php endif; ?>

    </div>

</div>

<?php include '../layout/footer.php'; ?>