<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();}

    // require_once '../../models/plot.php';
    require_once __DIR__ . '/DBController.php';


    class RentController {
        private $db;


    public function getAvailablePlots() {
        $this->db = new DBController();

        if ($this->db->openConnection()) {
            $query = "SELECT * FROM plot WHERE status = 'Available'";
            $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('TOOL DELETED',NOW())");
            return $this->db->select($query);}
        else{
        echo "Error in database connection";
        return false;}
    }

        // public function getAvailablePlots() {
        //     // بنرجع Array وهمية عشان الـ View يعرف يشتغل[cite: 4, 6]
        //     return [
        //         ['id' => 101, 'area' => 50, 'soil_type' => 'Loamy'],
        //         ['id' => 202, 'area' => 75, 'soil_type' => 'Premium'],
        //         ['id' => 303, 'area' => 40, 'soil_type' => 'Clay']
        //     ];
        // }



    }
?>
