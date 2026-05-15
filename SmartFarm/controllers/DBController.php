<?php

class DBController{
    public $host = "localhost";
    public $user = "root";
    public $pass = "";
    public $db_name = "green";
    public $conn;

    public function openConnection(){
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);
        if ($this->conn->connect_error) {
            echo "error in connection to database";
            return false;} 
        else {return true;}
    }

    public function closeConnection(){
        if ($this->conn) {
            $this->conn->close();} 
        else {echo "Connection is not opened";}
    }

    public function select($query){
        if (!$this->conn) {
        $this->openConnection();}
        $result = $this->conn->query($query);
        if (!$result) {
            echo "" . mysqli_error($this->conn);
            return false;} 
        else {
            return $result->fetch_all(MYSQLI_ASSOC);}
    }    //return array >>> index name is field in db >>>> 2d array

    public function insert($query){
        $result = $this->conn->query($query);
        if (!$result) {
            echo "" . mysqli_error($this->conn);
            return false;} 
        else {return true;}
    }

    public function update($query){
        $result = $this->conn->query($query);
        if (!$result) {
            echo "" . mysqli_error($this->conn);
            return false;} 
        else {return true;}
    }

    public function delete($query){
        $result=$this->conn->query($query);
        if(!$result){
            echo"".mysqli_error($this->conn);
            return false;}
        else{return true;}
    }

}
