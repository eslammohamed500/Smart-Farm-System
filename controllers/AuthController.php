<?php


require_once __DIR__ . '/../Models/user.php';
require_once __DIR__ . '/DBController.php';
class AuthController
{
    protected $db;
    //1. Open connection.
    //2. Run query & logic.
    //3. Close connection


    public function login(User $user){

        if (session_status() === PHP_SESSION_NONE) {
            session_start();}

        $this->db = new DBController;

        if ($this->db->openConnection()) {

            $query = "select * from users where email='$user->email' and password ='$user->password'";
            $result = $this->db->select($query);

            if ($result === false) {  //strict comparison
                echo "Error in Query";
                $this->db->closeConnection();
                return false;} 
            else {
                if (count($result) == 0) {
                    $_SESSION["errMsg"] = "You have entered wrong email or password";
                    $this->db->closeConnection();
                    return false;}

                else {
                    $_SESSION["userId"] = $result[0]["id"];
                    $_SESSION["userName"] = $result[0]["name"];
                    if ($result[0]["role"] == "admin") {
                        $_SESSION["userRole"] = "Admin";} 

                    else {$_SESSION["userRole"] = "Client";}
                    $this->db->closeConnection();
                    return true;
                }
            }
        } 
        else {
            echo "Error in Database Connection";
            return false;
        }
    }

    public function logout(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();}

        session_unset();
        session_destroy();
        header("Location: ../views/Auth/login.php");
        exit;
    }

    public function register(User $user){
        if (session_status() === PHP_SESSION_NONE) {
        session_start();}

        $this->db = new DBController;
        if ($this->db->openConnection()) {
            $check = "SELECT * FROM users WHERE email='$user->email'";
            $result = $this->db->select($check);
            if ($result && count($result) > 0) {
                // echo "Email already exists";
                $this->db->closeConnection();
                return false;}

            $query = "INSERT INTO users (name, email, password, role) VALUES ('$user->name', '$user->email', '$user->password', 'Client')";            
            $result = $this->db->insert($query);
            if ($result === false) {
                echo "please enter valid data";
                $this->db->closeConnection();
                return false;} 
            else {
                $this->db->closeConnection();
                return true;}} 
        else {
            echo "Error in Database Connection";
            return false;}
    }
}


if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    $auth = new AuthController();
    $auth->logout();}