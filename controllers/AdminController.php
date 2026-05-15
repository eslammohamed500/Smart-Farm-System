<?php
require_once __DIR__ . '/DBController.php';
require_once __DIR__ . '/../dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;



class AdminController
{
    protected $db;

    public function getAuditLogs(){
        if (!$this->db->openConnection()) {
            return [];
        }

        $conn = $this->db->conn;

        $sql = "SELECT id, action, created_at FROM audit_logs ORDER BY created_at DESC";

        $result = $conn->query($sql);

        $logs = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }

        return $logs;
    }

    public function exportAuditLogsPDF(){
        if (!$this->db->openConnection()) {
            die("Database connection failed");
        }

        $conn = $this->db->conn;

        $sql = "SELECT id, action, created_at 
                FROM audit_logs 
                ORDER BY created_at DESC";

        $result = $conn->query($sql);

        $html = '
        <h1 style="text-align:center;">Audit Logs Report</h1>

        <table border="1" width="100%" cellpadding="10" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
        ';

        while ($row = $result->fetch_assoc()) {

            $html .= '
            <tr>
                <td>'.$row['id'].'</td>
                <td>'.$row['action'].'</td>
                <td>'.$row['created_at'].'</td>
            </tr>
            ';
        }

        $html .= '</table>';

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $dompdf->stream("audit_logs_report.pdf", [
            "Attachment" => true
        ]);
    }

    public function exportPDF(){
        if (!$this->db->openConnection()) {
            die("Database connection failed");
        }

        $conn = $this->db->conn;

        $sql = "SELECT * FROM audit_logs ORDER BY created_at DESC";

        $result = $conn->query($sql);

        $html = "
        <h1 style='text-align:center;'>Audit Logs Report</h1>

        <table border='1' width='100%' cellpadding='10' cellspacing='0'>

            <tr>
                <th>ID</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
        ";

        while ($row = $result->fetch_assoc()) {

            $html .= "
            <tr>
                <td>{$row['id']}</td>
                <td>{$row['action']}</td>
                <td>{$row['created_at']}</td>
            </tr>
            ";
        }

        $html .= "</table>";

        $dompdf = new Dompdf();

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $dompdf->stream('audit_logs_report.pdf');
    }

    public function getallusers(){
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT * FROM users";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return $result;}
        else{
        echo "Error in Database Connection";
        return false;}
    }

    public function updateUser($id, $name, $email, $role) {
        $this->db = new DBController;
        if($this->db->openConnection()){
            $query="UPDATE users SET name='$name', email='$email', role='$role' WHERE id=$id";
            $result=$this->db->update($query);
            if ($result===false) {
                echo"Please Enter Valid info";
                $this->db->closeConnection();
                return false;}
            else{
                $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('USER GOT UPDATED',NOW())");
                $this->db->closeConnection();
                return true;}}
        else{
        echo "Error in Database Connection";
        return false;}
    }

    public function deleteUser($id) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "DELETE FROM users WHERE id=$id";
            $result = $this->db->delete($query);
            $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('USER GOT DELETED',NOW())");
            $this->db->closeConnection();
            return $result;}
        else{
        echo "Error in Database Connection";
        return false;}
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

    public function schedule_irrigation($data){
        $date = $data['date'];
        $time = $data['time'];
        $message = $data['message'];
        $datetime = $date . " " . $time;

        $this->db = new DBController;
        if ($this->db->openConnection()) {
            // 1. save schedule
            $this->db->insert("
                INSERT INTO irrigation_schedule (schedule_time, message)
                VALUES ('$datetime', '$message')");
            // 2. send notification to ALL users
            $this->db->insert("
                INSERT INTO notifications (user_id, message, created_at)
                SELECT id, '$message', NOW()
                FROM users");
                $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('IRRIGATION IS SCHEDULED',NOW())");
            $this->db->closeConnection();}
        $_SESSION['success'] = "Irrigation scheduled successfully!";
    }


    public function getStats(){
        $this->db=new DBController;
        if($this->db->openConnection()){
            $users = $this->db->select("SELECT COUNT(*) as total FROM users")[0]['total'] ?? 0;
            $tools = $this->db->select("SELECT COUNT(*) as total FROM tools")[0]['total'] ?? 0;
            $logs = $this->db->select("SELECT COUNT(*) as total FROM audit_logs")[0]['total'] ?? 0;
            $this->db->closeConnection();
            return ["users" => $users, "tools" => $tools, "logs"  => $logs];}
        else{
        echo "Error in Database Connection";
        return false;}
    }

    public function addTool($name, $description, $quantity) {
        $this->db = new DBController;
        if($this->db->openConnection()){
            // Upload Image
            $imageName = $_FILES['image']['name'];
            $tmpName = $_FILES['image']['tmp_name'];
            $folder = "../../uploads/tools/";

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);}
            move_uploaded_file($tmpName, $folder . $imageName);

            $availability = ($quantity > 0) ? 'available' : 'unavailable';
            // INSERT
            $query = " INSERT INTO tools(name, description, quantity, availability, image) VALUES ('$name', '$description', $quantity, '$availability', '$imageName')";

            $result = $this->db->insert($query);
            if ($result === false) {
                echo "Please Enter Valid info";
                $this->db->closeConnection();
                return false;} 
            else {
                $this->db->insert("INSERT INTO audit_logs (id,action,created_at)VALUES ({$_SESSION['userId']},'TOOL ADDED',NOW())");
                $this->db->closeConnection();
                return true;}} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    public function deleteTool($id) {
        $this->db = new DBController;
        if($this->db->openConnection()){
            $query = "DELETE FROM tools WHERE id=$id";
            $result = $this->db->delete($query);
            if ($result === false) {
                echo "Please Enter Valid info";
                $this->db->closeConnection();
                return false;}
            else{
                $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('TOOL DELETED',NOW())");
                $this->db->closeConnection();
                return true;}}
        else{
            echo "Error in Database Connection";
            return false;}
    }

    public function checkProductQuality($productId, $status) {
        $this->db = new DBController;
        if($this->db->openConnection()){
            $query = "UPDATE products SET quality_status='$status' WHERE id=$productId";
            $result = $this->db->update($query);
            if ($result === false) {
                echo "Please Enter Valid info";
                $this->db->closeConnection();
                return false;}
            else{
                $this->db->closeConnection();
                return true;}}
        else{
            echo "Error in Database Connection";
            return false;}
    }

    public function addShift($title, $start_time, $end_time) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $start = strtotime($start_time);
            $end   = strtotime($end_time);
            if (!$start || !$end) {
                $this->db->closeConnection();
                return false;}
            if ($start >= $end) {
                $this->db->closeConnection();
                return false;}
            $query = "INSERT INTO shifts (title, start_time, end_time, status)VALUES ('$title', '$start_time', '$end_time', 'open')";
            $result = $this->db->insert($query);
            if ($result === false) {
                $this->db->closeConnection();
                return false;} 
            else {
                $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('SHIFT ADDED',NOW())");
                $this->db->closeConnection();
                return true;}} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    public function deleteShift($id) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "DELETE FROM shifts WHERE id = $id";
            $result = $this->db->delete($query);
            if ($result === false) {
                echo "Error deleting shift";
                $this->db->closeConnection();
                return false;} 
            else {
                $this->db->insert("INSERT INTO audit_logs (action,created_at)VALUES ('SHIFT DELETED',NOW())");
                $this->db->closeConnection();
                return true;}} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    public function getAllShifts() {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT * FROM shifts";
            $result = $this->db->select($query);
            if ($result === false) {
                echo "Error getting shifts";
                $this->db->closeConnection();
                return false;} 
            else {
                $this->db->closeConnection();
                return $result;}} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    public function getWeatherRequests() {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $result = $this->db->select("
                SELECT * FROM weather_requests
                WHERE status = 'pending'
                ORDER BY created_at DESC
            ");
            $this->db->closeConnection();
            return $result ?: [];}
        return [];
    }

    public function getRequestById($id) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $result = $this->db->select("
                SELECT * FROM weather_requests
                WHERE id = $id
            ");
            $this->db->closeConnection();
            return $result[0] ?? null;
        }
        return null;
    }

    public function approveWeather($requestId, $date) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $requestId = (int)$requestId;
            // approve request
            $this->db->update("
                UPDATE weather_requests
                SET status = 'approved'
                WHERE id = $requestId
            ");
            // cancel shifts
            $this->db->update("
                UPDATE shifts
                SET status = 'cancelled'
                WHERE DATE(start_time) = '$date'
            ");
            $this->db->closeConnection();
            return true;}
        return false;
    }

    public function rejectWeather($requestId) {
        $this->db = new DBController;

        if ($this->db->openConnection()) {

            $requestId = (int)$requestId;

            $this->db->update("
                UPDATE weather_requests 
                SET status = 'rejected'
                WHERE id = $requestId
            ");

            $this->db->closeConnection();
            return true;
        }

        return false;
    }

    public function recordVolunteerHours($volunteerId, $hours) {
        $this->db = new DBController;
        if($this->db->openConnection()){
            $query = "INSERT INTO volunteer_hours (volunteer_id, hours, date_recorded) VALUES ($volunteerId, $hours, NOW())";
            $result = $this->db->insert($query);
            if ($result === false) {
                echo "Please Enter Valid info";
                $this->db->closeConnection();
                return false;}
            else{
                $this->db->closeConnection();
                return true;}}
        else{
            echo "Error in Database Connection";
            return false;}
    }

}
