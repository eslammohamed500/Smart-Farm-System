<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();}
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");

    if (!isset($_SESSION["userId"]) || $_SESSION["userRole"] != "Client") {
        header("location:../Auth/login.php");
        exit;
    }

    require_once '../../controllers/FarmerController.php';
    $farmer_con = new FarmerController();

    $insights = $farmer_con->getSmartInsights();
    $spoilageRisk = $insights['spoilageRisk']; 
    $predictionMsg = $insights['message'];


    // // Dummy user data for the dashboard view
    $karmaPoints = 1000; // Earned from produce donations
    $communityPoints = 400; // Earned from volunteer contributions

    $bankSeeds = $farmer_con->getAllBankSeeds();
    $userData = $farmer_con->getUserData($_SESSION['userId']);
    $karmaPoints = $userData['karma_points'] ?? 0;
    $communityPoints = $userData['community_points'] ?? 0;
    $availableTools = $farmer_con->getAvailableTools();
    $myBorrowed = $farmer_con->getMyBorrowedTools($_SESSION['userId']);
    $pendingRequests = $farmer_con->getPendingMentorships(); 
    $activeMentorships = $farmer_con->getActiveMentorships($_SESSION['userId']);
    // $role = $_SESSION['userRole'] ?? 'Client';

    include '../layout/header.php';
    include '../layout/sidebar.php';
?>

<style>
    .mb-4 {
    margin-top: 70px;
    margin-bottom: 1.5rem !important;}
</style>


<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Farmer Dashboard</h2>
        <div class="badges">
            <span class="badge bg-success fs-6 me-2" title="Earned from produce donations">Karma Points: <?php echo $karmaPoints; ?></span>
            <span class="badge bg-primary fs-6" title="Earned from volunteer contributions">Community Points: <?php echo $communityPoints; ?></span>
        </div>
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

        <!-- Community & Mentorship Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Community & Mentorship</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($karmaPoints >= 1000): ?>
                            <!-- واجهة المزارع الخبير (Expert View) -->
                            <div class="text-center mb-4">
                                <h6 class="fw-bold text-primary text-uppercase small tracking-wider">Expert Mentorship Panel</h6>
                                <p class="text-muted small">You are an Expert! Your guidance helps our community grow.</p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle border-top">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Beginner Farmer</th>
                                            <th>Topic / Issue</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php                                         
                                        if (!empty($pendingRequests)): 
                                            foreach ($pendingRequests as $request): 
                                        ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-light rounded-circle me-2 p-2 text-center" style="width: 35px;">
                                                            <i class="bi bi-person text-secondary"></i>
                                                        </div>
                                                        <strong><?php echo $request['beginner_name']; ?></strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill bg-warning text-dark px-3">
                                                        <?php echo $request['topic']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <form action="../../controllers/FarmerController.php" method="POST">
                                                        <input type="hidden" name="action" value="acceptMentorship">
                                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 shadow-sm">
                                                            <i class="bi bi-hand-thumbs-up me-1"></i>Help Now
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php 
                                            endforeach; 
                                        else: 
                                        ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No pending help requests at the moment.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php if(!empty($activeMentorships)): ?>
                            <div class="mt-5">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="bi bi-stars me-2"></i>
                                    Active Mentorships
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle border-top">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Farmer</th>
                                                <th>Topic</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach($activeMentorships as $mentorship): ?>
                                            <tr>
                                                <td>
                                                    <strong>
                                                        <?php echo $mentorship['beginner_name']; ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary rounded-pill px-3">
                                                        <?php echo $mentorship['topic']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        Active
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <form action="../../controllers/FarmerController.php" method="POST">
                                                        <input type="hidden" name="action" value="completeMentorship">
                                                        <input type="hidden"
                                                            name="request_id"
                                                            value="<?php echo $mentorship['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-4">
                                                            <i class="bi bi-check-circle me-1"></i>
                                                            Complete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php else: ?>
                            <!-- واجهة المزارع المبتدئ (Beginner View) -->
                            <div class="text-center py-3">
                                <i class="bi bi-patch-question display-4 text-primary mb-3"></i>
                                <h6 class="fw-bold">Looking for guidance?</h6>
                                <p class="text-muted mb-4">Our mentorship algorithm matches you with experts based on your soil and crops.</p>
                                <button class="btn btn-primary btn-lg rounded-pill px-5 shadow" data-bs-toggle="modal" data-bs-target="#requestHelpModal">
                                    Find a Mentor
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php // if ($role != 'volunteer'): ?>
                <!-- <div class="mt-5">
                        <form action="../../controllers/FarmerController.php" method="POST">
                            <input type="hidden" name="action" value="becomeVolunteer">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                🤝 Become a Volunteer
                            </button>
                        </form>
                </div> -->
        <?php //  endif; ?>

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
        // بنخفي قسم الـ QR ونظهر قسم التأكيد النهائي
        document.getElementById('qrSection').style.display = 'none';
        document.getElementById('confirmSection').style.display = 'block';
    }
</script>


<!-- Modal طلب مساعدة للمبتدئين -->
<div class="modal fade" id="requestHelpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title">Request Mentorship</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../../controllers/FarmerController.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="requestMentorship">
                    <div class="mb-3">
                        <label class="form-label fw-bold">What do you need help with?</label>
                        <select class="form-select rounded-pill" name="topic" required>
                            <option value="" disabled selected>Select a topic</option>
                            <option value="Soil Preparation">Soil Preparation</option>
                            <option value="Pest Control">Pest Control</option>
                            <option value="Irrigation Systems">Irrigation Systems</option>
                            <option value="Crop Selection">Crop Selection</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Describe your issue (Optional)</label>
                        <textarea class="form-control rounded-3" name="message" rows="3" placeholder="Explain your situation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 justify-content-center">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">SEND REQUEST</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>
