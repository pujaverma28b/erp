<?php
class Database {
    private $db_host          = "localhost";
    private $db_username      = "root";
    private $db_password      = "";
    private $db_name          = "crud_operation";

    private $conn             = false;
   
    public function getConnection (){
        try{
            $this->conn = new PDO ("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name , $this->db_username, $this->db_password );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            echo "Connection Error" . $e->getMessage();
        }

        return $this->conn;
    }

    /* public function __construct(){
        try{
            $this->conn = new PDO ("mysql:host=" . $this->db_host . ";dbname=" . $this->db_name , $this->db_username, $this->db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] );
           
        }catch(PDOException $e){
            echo "Connection Error" . $e->getMessage();
        }
    }
    
    public function getConnection (){
        return $this->conn;
    } */
}

/* $db = new Database();
$dd = $db->getConnection();
if ($dd instanceof PDO) {
    echo "<br>✅ Connection object is valid and ready to use!";
} else {
    echo "<br>❌ Connection failed!";
}  */

//--another way

/*private $conn; // Private is much better!
    
 public function __construct() {
    $this->conn = new PDO("mysql:host=localhost;dbname=test", "user", "pass");
    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
 */
?>