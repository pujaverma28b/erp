<?php
class dynamicModel{
    private $conn;
    private $table_name;

    public function __construct($conn, $table_name){
        $this->conn = $conn;
        $this->table_name = $table_name;
    }

    private function tableExist(){
        try{
            $query = " SHOW COLUMNs FROM " . $this->table_name;
            $stmt  = $this->conn->prepare($query);

            $stmt->execute();
            return $stmt->rowCount() > 0 ;
        }catch(PDOException $e){
            return false; 
        }

    }

    public function insertData($data = [], $debug = false){
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");        
        }

        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $query = "INSERT INTO {$this->table_name} ($columns) VALUES ($placeholders)";
        $stmt  = $this->conn->prepare($query);

        foreach ($data as $key => $value){
            $stmt->bindValue(":$key", $value);
        }

        if($debug){
            return[
                "query" => $query,
                "data" => $data,
            ];
        }
       
        //return $stmt->execute();

        if($stmt->execute()){
            return $this->conn->lastInsertId();
        }


        return false;
    }

     //  Fetch data
    public function fetchData($conditions = [], $columns = "*", $orderBy = "", $limit = 0) {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        // Allow array for columns
        if (is_array($columns)) {
            $columns = implode(", ", $columns);
        }

        $query = "SELECT $columns FROM {$this->table_name}";

        // WHERE clause
        $where = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = :$key";
        }
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        // ORDER BY
        if (!empty($orderBy)) {
            $query .= " ORDER BY " . $orderBy;
        }

        // LIMIT
        if ($limit > 0) {
            $query .= " LIMIT " . $limit;
        }

        $stmt = $this->conn->prepare($query);

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>