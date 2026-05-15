<?php

    // require_once '../models/farmer.php';
    require_once __DIR__ . '/DBController.php';

// session_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


class FarmerController {

    private $db;

    public function __construct() {
        require_once 'DBController.php';
        $this->db = new DBController(); 
    }

# duplicated in volunteer controller, should be in a common helper
    public function isJoined($userId, $shiftId) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT * FROM volunteer_shifts WHERE user_id = $userId AND shift_id = $shiftId";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return !empty($result);}
        return false;
    }

    public function getUserData($id){
        $id = (int)$id;
        $query = "SELECT * FROM users WHERE id = $id";
        $result = $this->db->select($query);
        return $result[0] ?? [];
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            switch ($action) {
                case 'rentPlot':
                    $this->add_plot($_POST);
                    break;
                case 'sharePlot':
                    $this->share_plot($_POST);
                    break;
                case 'returnPlot':
                    $this->return_plot($_POST);
                    break;
                case 'postProduce':
                    $this->post_produce($_POST);
                    break;
                case 'deleteProduce':
                    $this->delete_produce($_POST);
                    break;
                case 'exchangeSeeds':
                    $this->exchange_seeds($_POST);
                    break;
                case 'buySeeds':
                    $this->buy_seeds($_POST);
                    break;
                case 'borrowTool':
                    $this->borrow_tool($_POST);
                    break;
                case 'returnTool':
                    $this->return_tool($_POST);
                    break;
                case 'acceptMentorship':
                    $this->acceptMentorship($_POST['request_id']);
                    break;
                case 'completeMentorship':
                    $this->completeMentorship($_POST['request_id']);
                    break;
                case 'requestMentorship':
                    $this->request_mentorship($_POST);
                    break;
                // case 'becomeVolunteer':
                //     $this->becomeVolunteer();
                //     break;
                case 'buyProduct':
                    $this->buy_product($_POST);
                    break;
            }
        }
    }

    public function getMyLeasedPlots($farmerId) {

        if ($this->db->openConnection()) {

            $farmerId = (int)$farmerId;

            $query = "SELECT * FROM plot 
                    WHERE leased_by = $farmerId 
                    AND status = 'Rented'";

            return $this->db->select($query) ?: [];
        }

        return [];
    }

    public function getMySharedPlots($userId) {

        if ($this->db->openConnection()) {

            $userId = (int)$userId;

            $query = "
                SELECT 
                    c.plot_id AS id,
                    c.percentage,
                    p.soil_type,
                    p.leased_by,
                    c.owner_id,
                    c.partner_id,
                    u1.name AS owner_name,
                    u2.name AS partner_name
                FROM co_tenants c
                JOIN plot p ON c.plot_id = p.id
                JOIN users u1 ON c.owner_id = u1.id
                JOIN users u2 ON c.partner_id = u2.id
                WHERE c.owner_id = $userId OR c.partner_id = $userId
            ";

            return $this->db->select($query) ?: [];
        }

        return [];
    }


    public function share_plot($data) {

        if (!$this->db->openConnection()) return;
        if (!isset($_SESSION['shared_plots'])) {$_SESSION['shared_plots'] = [];}

        $plotId = (int)$data['plot_id'];
        $partnerNames = $data['partner_names'];
        $percentages = $data['percentages'];
        $totalPartnersPercentage = 0;
        $partnersList = [];
        foreach ($partnerNames as $i => $name) {
            $name = trim($name);
            $query = "SELECT id FROM users WHERE name = '$name' AND role = 'user'";
            $partner = $this->db->select($query);

            if(!$partner) {
                header("Location: ../views/farmer/share_plot.php?error=Partner+".urlencode($name)."+not+found");
                exit();
            }

            $share = (int)$percentages[$i];
            $totalPartnersPercentage += $share;

            $partnersList[] = [
                'id' => $partner[0]['id'],
                'name' => $name,
                'share' => $share];
        }

        if ($totalPartnersPercentage >= 100) {
            header("Location: ../views/farmer/share_plot.php?error=Total+shares+must+be+less+than+100%");
            exit();
        }

        foreach ($partnersList as $p) {
            $ownerId = (int)$_SESSION['userId'];
            $partnerId = (int)$p['id'];
            $share = (int)$p['share'];
            $query = "INSERT INTO co_tenants (plot_id, owner_id, partner_id, percentage)
                    VALUES ($plotId, $ownerId, $partnerId, $share)";
            $this->db->insert($query);
            $_SESSION['shared_plots'][] = [
                'id' => $plotId,
                'owner_name' => $_SESSION['userName'],
                'partner_name' => $p['name'],
                'percentage' => $share,
                'soil_type' => 'Shared',
                'coordinates' => '-'];
        }

        $this->db->insert("
            INSERT INTO audit_logs (action,created_at)
            VALUES ('PLOT GOT SHARED',NOW())");

        header("Location: ../views/farmer/share_plot.php?success=Plot+Shared+and+Saved+to+Database");
        exit();
    }

    public function add_plot($data) {
        $plotId = $data['plot_id'];
        $farmerId = (int)$_SESSION['userId'];
        $duration =addslashes($data['duration'] ?? '');

        if ($this->db->openConnection()) {
            $updateQuery = " UPDATE plot SET status = 'Rented', leased_by = $farmerId WHERE id = $plotId";
            $this->db->update($updateQuery);

            
            $insertQuery = "INSERT INTO rentals (farmer_id, plot_id, duration, start_date) VALUES ($farmerId, $plotId, '$duration', NOW())";
            $isSuccess = $this->db->insert($insertQuery);

            if ($isSuccess) {
                
                $plotData = $this->db->select("SELECT * FROM plot WHERE id = $plotId");
                $_SESSION['my_plots'][] = $plotData[0]; 
                $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('PLOT WAS ADDED',NOW())");

                header("Location: ../views/farmer/rent_plot.php?success=Plot+Leased+Successfully");
                exit();
            }
        }
    }

    public function return_plot($data) {
        if (!$this->db->openConnection()) return;

        $plotId = (int)$data['plot_id'];

        $this->db->update("
            UPDATE plot 
            SET status = 'Available', leased_by = NULL 
            WHERE id = $plotId
        ");

        if (isset($_SESSION['my_plots'])) {
            foreach ($_SESSION['my_plots'] as $key => $plot) {
                if ($plot['id'] == $plotId) {
                    unset($_SESSION['my_plots'][$key]);
                    $_SESSION['my_plots'] = array_values($_SESSION['my_plots']);
                    break;
                }
            }
        }

        $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('A PLOT GOT RETURNED',NOW())");
        header("Location: ../views/farmer/rent_plot.php?success=Plot $plotId returned!");
        exit();
    }

    public function post_produce($data) {
        if (!$this->db->openConnection()) return;
        $userId = $_SESSION['userId'] ?? null;
        if (!$userId) {
            header("Location: ../views/farmer/farmer.php?error=Unauthorized");
            exit();}

        $name = trim($data['produceName'] ?? '');
        $quantity = (int)($data['produceQuantity'] ?? 0);
        $price = (float)($data['price'] ?? 0);


        if ($name === '' || $quantity <= 0 || $price <= 0) {
            header("Location: ../views/farmer/farmer.php?error=Invalid+produce+data");
            exit();}
        $name = htmlspecialchars($name, ENT_QUOTES);
        $userId = (int)$userId;
        $tags = isset($data['allergyTags']) && is_array($data['allergyTags'])? implode(", ", array_map('htmlspecialchars', $data['allergyTags'])): "None";
        $query = "
            INSERT INTO marketplace 
            (farmer_id, produce_name, quantity ,price , allergy_tags, post_date)
            VALUES 
            ($userId, '$name', $quantity, $price, '$tags', NOW())";

        $this->db->insert($query);
        $_SESSION['my_produce'][] = [
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price,
            'tags' => $tags,
            'date' => date('Y-m-d')];
        $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('A PRODUCTION GOT POSTED',NOW())");
        header("Location: ../views/farmer/production.php?success=Produce+Posted");
        exit();
    }

    public function delete_produce($data) {
        if (!$this->db->openConnection()) return;

        $name = addslashes($data['produceName']);
        $userId = (int)$_SESSION['userId'];

        $this->db->delete("
            DELETE FROM marketplace 
            WHERE produce_name = '$name' 
            AND farmer_id = $userId
        ");

        if (isset($_SESSION['my_produce'])) {
            foreach ($_SESSION['my_produce'] as $key => $item) {
                if ($item['name'] == $name) {
                    unset($_SESSION['my_produce'][$key]);
                    $_SESSION['my_produce'] = array_values($_SESSION['my_produce']);
                    break;
                }
            }
        }

        $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('PRODUCTION GOT DELETED',NOW())");
        header("Location: ../views/farmer/production.php?success=Deleted");
        exit();
    }


    public function getMyProduce($userId) {
        $this->db->openConnection();
        // for each farmer production dashboard
        $query = "
            SELECT *
            FROM marketplace
            WHERE farmer_id = $userId     
            ORDER BY post_date DESC";
        return $this->db->select($query);
    }

    public function getMarketplaceItems($userId) {
        if (!$this->db->openConnection()) return [];
        $userId = (int)$userId;
        $query = "
            SELECT *
            FROM marketplace
            WHERE farmer_id != $userId
            ORDER BY post_date DESC";
        return $this->db->select($query);
    }

    public function buy_product($data) {

        $this->db->openConnection();
        $userId = $_SESSION['userId'];
        $productId = (int)$data['product_id'];

        $product = $this->db->select("SELECT * FROM marketplace WHERE id = $productId")[0];
        $price = $product['price'];

        // karma
        $user = $this->db->select("
            SELECT karma_points FROM users WHERE id = $userId")[0];
        // check balance
        if ($user['karma_points'] < $price) {
            header("Location: ../views/farmer/store.php?error=Not enough karma");
            exit();}
        // consume karma
        $this->db->update("
            UPDATE users
            SET karma_points = karma_points - $price
            WHERE id = $userId");
        // تقليل الكمية
        $newQty = $product['quantity'] - 1;
        if ($newQty <= 0) {
            $this->db->delete("DELETE FROM marketplace WHERE id = $productId");} 
        else {
            $this->db->update("
                UPDATE marketplace
                SET quantity = $newQty
                WHERE id = $productId");}
        header("Location: ../views/farmer/store.php?success=Purchased successfully!");
        exit();
    }

    public function getSmartInsights() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $plots = $_SESSION['my_plots'] ?? [];

        if (empty($plots)) {
            return [
                'spoilageRisk' => false,
                'message' => "Welcome! Rent your first plot to start receiving AI-powered harvest predictions.",
                'level' => 'info'
            ];
        }

        foreach ($plots as $plot) {
            
            if ($plot['soil_type'] === 'Loamy') {
                return [
                    'spoilageRisk' => false,
                    'message' => "Optimal conditions detected for Plot {$plot['id']}. High yield expected in 3 weeks!",
                    'level' => 'info'
                ];
            }
            
            
            if ($plot['id'] === 101) {
                return [
                    'spoilageRisk' => true,
                    'message' => "Urgent: High humidity detected near Plot {$plot['id']}. Risk of Tomato spoilage is 85%. Harvest now!",
                    'level' => 'warning'
                ];
            }
        }
        return [
            'spoilageRisk' => false,
            'message' => "All systems stable. Monitoring your crops for the best harvest time.",
            'level' => 'info'
        ];
    }

    public function exchange_seeds($data) {
        $farmerId = (int)$_SESSION['userId'];
        $seedName = addslashes($data['seedType']);
        $qtyToDeposit = (int)$data['quantity'];

        $currentStock = $this->db->select("
            SELECT quantity 
            FROM farmer_inventory 
            WHERE farmer_id = $farmerId 
            AND seed_name = '$seedName'
        ");

        if (!$currentStock || $currentStock[0]['quantity'] < $qtyToDeposit) {
            header("Location: ../views/farmer/farmer.php?error=Not enough $seedName bags!");
            exit();
        }

        $receivedQty = $qtyToDeposit * 2;

        $this->db->update("
            UPDATE farmer_inventory 
            SET quantity = quantity - $qtyToDeposit 
            WHERE farmer_id = $farmerId 
            AND seed_name = '$seedName'
        ");

        $this->db->insert("
            INSERT INTO farmer_inventory (farmer_id, seed_name, quantity)
            VALUES ($farmerId, '$seedName', $receivedQty)
            ON DUPLICATE KEY UPDATE quantity = quantity + $receivedQty
        ");

        $this->updateSeedSession($farmerId);
        $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('SEEDS GOT EXCHANGED',NOW())");

        header("Location: ../views/farmer/production.php?success=Exchanged successfully!");
        exit();
    }

    public function buy_seeds($data) {
        if (!$this->db->openConnection()) return;

        $farmerId = (int)$_SESSION['userId'];
        $seedName = addslashes($data['seedName']);
        $qty = (int)$data['buyQuantity'];
        $seedCost = (int)$data['seedCost'];

        $totalCost = $seedCost * $qty;

        $user = $this->db->select("
            SELECT community_points 
            FROM users 
            WHERE id = $farmerId
        ");

        if (!$user || $user[0]['community_points'] < $totalCost) {
            header("Location: ../views/farmer/farmer.php?error=Not enough points");
            exit();
        }

        $this->db->update("
            UPDATE users 
            SET community_points = community_points - $totalCost 
            WHERE id = $farmerId
        ");

        $this->db->insert("
            INSERT INTO farmer_inventory (farmer_id, seed_name, quantity)
            VALUES ($farmerId, '$seedName', $qty)
            ON DUPLICATE KEY UPDATE quantity = quantity + $qty
        ");

        $this->updateSeedSession($farmerId);
        $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('SEEDS GOT BOUGHT',NOW())");

        header("Location: ../views/farmer/production.php?success=Purchase done!");
        exit();
    }

    public function getAllBankSeeds() {
        if ($this->db->openConnection()) {
            return $this->db->select("SELECT * FROM seed_bank");
        }
        return [];
    }

    private function updateSeedSession($farmerId) {
        $farmerId = (int)$_SESSION['userId'];;

        if ($this->db->openConnection()) {
            $_SESSION['my_seeds'] = $this->db->select("
                SELECT * FROM farmer_inventory 
                WHERE farmer_id = $farmerId
            ");
        }
    }

    public function getAllTools() {
        $this->db = new DBController;
        if ($this->db->openConnection()){
            $query = "SELECT * FROM tools";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return $result;}
        else{
        echo "Error in Database Connection";
        return false;}
    }

    public function getAvailableTools() {
        return $this->db->select("
            SELECT * FROM tools 
            WHERE status = 'Available'
        ");
    }

    public function getUnavailableTools() {
        return $this->db->select("
            SELECT * FROM tools 
            WHERE status = 'Unavailable'
        ");
    }

    public function getMyBorrowedTools($farmerId) {
        $farmerId = (int)$farmerId;
        $query = "SELECT b.*, t.name 
                FROM borrowed_tools b 
                JOIN tools t ON b.tool_id = t.id 
                WHERE b.farmer_id = $farmerId AND b.status = 'Active'";
        return $this->db->select($query);
    }

    // لوجيك الاستعارة
    public function borrow_tool($data) {
        $farmerId = $_SESSION['userId'];
        $toolId = $data['toolId'];
        $dueDate = date('Y-m-d H:i:s', strtotime('+2 days'));

        if ($this->db->openConnection()) {
            
            $this->db->update("UPDATE tools SET status = 'Borrowed' WHERE id = $toolId");
            
            
            $query = "INSERT INTO borrowed_tools (farmer_id, tool_id, due_date) VALUES ($farmerId, $toolId,'$dueDate')";
            $this->db->insert($query);

            $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('TOOL GOT BORROWED',NOW())");
            header("Location: ../views/tools/tools.php?success=Tool borrowed! Please return it by '$dueDate'");
            exit();
        }
    }

    // داخل ملف FarmerController.php
    public function return_tool($data) {
        if (!$this->db->openConnection()) return;

        $toolId = (int)$data['toolId'];
        $farmerId = (int)$_SESSION['userId'];

        $this->db->update("
            UPDATE tools 
            SET status = 'Available'
            WHERE id = $toolId");

        $this->db->update("
            UPDATE borrowed_tools 
            SET status = 'Returned',
                usage_counter = usage_counter + 1
            WHERE farmer_id = $farmerId 
            AND tool_id = $toolId");

        $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('TOOL GOT RETURNED',NOW())");
        header("Location: ../views/tools/tools.php?success=Tool returned!");
        exit();
    }

    public function getPendingMentorships() {
        if (!$this->db->openConnection()) return [];
        
        $query = "SELECT m.*, u.name as beginner_name 
                FROM mentorship m 
                JOIN users u ON m.beginner_id = u.id 
                WHERE m.status = 'Pending'";
                
        return $this->db->select($query);
    }

    public function acceptMentorship($requestId) {
        $expertId = (int)$_SESSION['userId'];
        $requestId = (int)$requestId;
        if ($this->db->openConnection()) {
            $this->db->update("
                UPDATE mentorship 
                SET expert_id = $expertId,
                    status = 'Active' 
                WHERE id = $requestId");
            $this->db->insert("
                INSERT INTO audit_logs(action, created_at)
                VALUES ('MENTORSHIP ACCEPTED', NOW())");
            header("Location: ../views/farmer/farmer.php?success=You are now mentoring a fellow farmer!");
            exit();
        }
    }

    public function getActiveMentorships($expertId) {
        $this->db->openConnection();
        $query = "
            SELECT mentorship.*, users.name AS beginner_name FROM mentorship
            JOIN users
            ON mentorship.beginner_id = users.id
            WHERE mentorship.expert_id = $expertId
            AND mentorship.status = 'Active' ";
        return $this->db->select($query);
    }

        // زيادة الـ Karma بمقدار 100 نقطة كمكافأة
    public function completeMentorship($requestId) {
        $requestId = (int)$requestId;
        $userId = (int)$_SESSION['userId'];
        if ($this->db->openConnection()) {
            $this->db->update("
                UPDATE mentorship 
                SET status = 'Completed' 
                WHERE id = $requestId");

            $this->db->update("
                UPDATE users 
                SET karma_points = karma_points + 100 
                WHERE id = $userId");
            $this->db->insert("
                INSERT INTO audit_logs(action, created_at)
                VALUES ('MENTORSHIP COMPLETED', NOW())");
            header("Location: ../views/farmer/farmer.php?success=Mentorship completed! +100 Karma Points earned.");
            exit();
        }
    }

    public function request_mentorship($data) {

        $beginnerId = $_SESSION['userId'];
        $topic = $data['topic'];

        if ($this->db->openConnection()) {

                $query = "INSERT INTO mentorship 
                (beginner_id, topic, status)
                VALUES ($beginnerId, '$topic', 'Pending')";

                $this->db->insert($query);

                $this->db->insert("
                    INSERT INTO audit_logs (action, created_at)
                    VALUES ('MENTORSHIP REQUEST SENT', NOW())
                ");

                header("Location: ../views/farmer/farmer.php?success=" .
                    urlencode("Request for $topic sent! Waiting for an expert."));

                exit();
            }
    }

    public function getNotifications($userId) {
        $this->db = new DBController;

        if ($this->db->openConnection()) {

            $result = $this->db->select("
                SELECT * FROM notifications
                WHERE user_id = $userId
                ORDER BY created_at DESC
            ");

            return $result ?: [];
        }

        return [];
    }


    // public function becomeVolunteer() {
    //     if (!$this->db->openConnection()) return;
    //     $this->db->openConnection();
    //     $userId = (int)$_SESSION['userId'];
    //     $this->db->update("UPDATE users SET role = 'volunteer' WHERE id = $userId");
    //     // $_SESSION['userRole'] = 'volunteer';
    //     $this->db->insert("INSERT INTO audit_logs (action, created_at)VALUES ('USER BECAME VOLUNTEER', NOW())");
    //     header("Location: ../views/farmer/farmer.php?success=You are now a volunteer!");
    //     exit();
    // }

}

$controller = new FarmerController();
$controller->handleRequest();



?>
