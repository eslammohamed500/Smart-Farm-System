<?php


require_once __DIR__ . '/DBController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


class VolunteerController {

    protected $db;

    public function getAvailableShifts() {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT * FROM shifts WHERE status = 'open'";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return $result;} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    // public function updateVolunteerHours($userId, $hoursToAdd) {
    //     $this->db = new DBController;
    //     if ($this->db->openConnection()) {

    //         $check = "SELECT * FROM volunteer_shifts WHERE user_id = $userId";
    //         $exists = $this->db->select($check);
    //         if (empty($exists)) {
    //             echo "No shift found";
    //             $this->db->closeConnection();
    //             return false;}

    //         $query = "UPDATE volunteer_shifts SET hours_logged = hours_logged + $hoursToAdd WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1";
    //         $result = $this->db->conn->query($query);
    //         if ($result === false) {
    //             echo "Error updating hours";
    //             $this->db->closeConnection();
    //             return false;}
    //         else {
    //             $this->db->closeConnection();
    //             return true;}} 
    //     else {
    //         echo "Error in Database Connection";
    //         return false;}
    // }

    public function joinShift($userId, $shiftId, $role) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {

            // 1. already joined
            $checkQuery = "SELECT * FROM volunteer_shifts WHERE user_id = $userId AND shift_id = $shiftId";
            $exists = $this->db->select($checkQuery);
            if ($exists && count($exists) > 0){
                $this->db->closeConnection();
                return "already";}

            // 2. current hours
            $hoursQuery = "SELECT SUM(hours_logged) as total FROM volunteer_shifts WHERE user_id = $userId";
            $res = $this->db->select($hoursQuery);
            $currentHours = ($res && $res[0]['total']) ? $res[0]['total'] : 0;
            $mandatoryHours = 8;

            // 3. max check
            if ($currentHours + 3 > $mandatoryHours) {
                $this->db->closeConnection();
                return "max_reached";}

            // 4. insert
            $query = "INSERT INTO volunteer_shifts (user_id, shift_id, hours_logged) VALUES ($userId, $shiftId, 3)";
            $result = $this->db->insert($query);
            $this->db->closeConnection();
            return $result ? "joined" : "error";}

        return "error";
    }

    public function isJoined($userId, $shiftId) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT * FROM volunteer_shifts WHERE user_id = $userId AND shift_id = $shiftId";
            $result = $this->db->select($query);
            $this->db->closeConnection();
            return !empty($result);}
        return false;
    }

    public function cancelJoin($userId, $shiftId) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "DELETE FROM volunteer_shifts WHERE user_id = $userId AND shift_id = $shiftId";
            $result = $this->db->delete($query);
            if ($result === false) {
                $this->db->closeConnection();
                return false;} 
            else {
                $this->db->closeConnection();
                return true;}} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    public function serviceHourTracker($userId, $mandatoryHours = 8.0) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $query = "SELECT SUM(hours_logged) as total_logged FROM volunteer_shifts WHERE user_id = $userId";
            $result = $this->db->select($query);
            $hours = ($result && !empty($result[0]['total_logged']))? $result[0]['total_logged']: 0;
            $this->db->closeConnection();
            return [
                "current_hours" => $hours,
                "mandatory_hours" => $mandatoryHours,
                "met_requirement" => ($hours >= $mandatoryHours)];} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

    public function reportWeather($userId, $weather, $date) {

        $this->db = new DBController;
        if ($this->db->openConnection()) {

            $this->db->insert("
                INSERT INTO weather_requests (user_id, weather_condition, request_date)
                VALUES ($userId, '$weather', '$date')
            ");
            $this->db->closeConnection();
            return true;}

        return false;
    }

    public function advanceDay($userId, $currentDate) {
        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $this->db->update("UPDATE volunteer_shifts SET hours_logged = 0");
            $this->db->update("UPDATE shifts SET status='closed'");
            $tasks = [
                'Watering Plants', 'Harvesting Tomatoes', 'Compost Turning',
                'Weeding', 'Planting Seeds', 'Fertilizing'];
            shuffle($tasks);
            for ($i = 0; $i < 3; $i++) {
                $task = $tasks[$i];
                $startTime = $currentDate . " 08:00:00";
                $endTime = $currentDate . " 12:00:00";
                $query = "INSERT INTO shifts (title, start_time, end_time)VALUES ('$task', '$startTime', '$endTime')";
                $result = $this->db->insert($query);
                if ($result === false) {
                    echo "Error adding shift";
                    $this->db->closeConnection();
                    return false;}}
            $this->db->closeConnection();
            return true;} 
        else {
            echo "Error in Database Connection";
            return false;}
    }

}