<?php
class dynamicModel {
    private $conn;
    private $table_name;

    public function __construct($conn, $table_name) {
        $this->conn = $conn;
        $this->table_name = $table_name;
    }

    private function tableExist() {
        try {
            $query = "SHOW COLUMNS FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $e) {
            return false; 
        }
    }

    public function insertData($data = [], $debug = false) {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");        
        }

        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $query = "INSERT INTO {$this->table_name} ($columns) VALUES ($placeholders)";
        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if($debug) {
            return [
                "query" => $query,
                "data" => $data,
            ];
        }
       
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Get all records
    public function getAll($limit = null, $offset = 0, $orderBy = "id DESC") {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        $query = "SELECT * FROM {$this->table_name}";
        
        // Add ORDER BY clause
        if (!empty($orderBy)) {
            $query .= " ORDER BY " . $orderBy;
        }

        // Add LIMIT clause
        if ($limit !== null) {
            $query .= " LIMIT :offset, :limit";
        }

        $stmt = $this->conn->prepare($query);

        if ($limit !== null) {
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single record by ID
    public function getById($id) {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        $query = "SELECT * FROM {$this->table_name} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get records by specific column value
    public function getByColumn($column, $value, $limit = null, $offset = 0) {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        $query = "SELECT * FROM {$this->table_name} WHERE {$column} = :value";
        
        if ($limit !== null) {
            $query .= " LIMIT :offset, :limit";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':value', $value);

        if ($limit !== null) {
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search records with multiple conditions
    public function search($conditions = [], $limit = null, $offset = 0, $orderBy = "id DESC") {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        if (empty($conditions)) {
            return $this->getAll($limit, $offset, $orderBy);
        }

        $whereClause = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} LIKE :{$column}";
            $params[":{$column}"] = "%{$value}%";
        }

        $query = "SELECT * FROM {$this->table_name} WHERE " . implode(" AND ", $whereClause);
        
        if (!empty($orderBy)) {
            $query .= " ORDER BY " . $orderBy;
        }

        if ($limit !== null) {
            $query .= " LIMIT :offset, :limit";
            $params[':offset'] = (int)$offset;
            $params[':limit'] = (int)$limit;
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get records with custom WHERE clause
    public function getWhere($whereClause = "", $params = [], $limit = null, $offset = 0, $orderBy = "id DESC") {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        $query = "SELECT * FROM {$this->table_name}";
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . $whereClause;
        }

        if (!empty($orderBy)) {
            $query .= " ORDER BY " . $orderBy;
        }

        if ($limit !== null) {
            $query .= " LIMIT :offset, :limit";
            $params[':offset'] = (int)$offset;
            $params[':limit'] = (int)$limit;
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Count total records
    public function countAll() {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        $query = "SELECT COUNT(*) as total FROM {$this->table_name}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    // Count records with conditions
    public function countWhere($whereClause = "", $params = []) {
        if (!$this->tableExist()) {
            throw new Exception("Table '{$this->table_name}' does not exist");
        }

        $query = "SELECT COUNT(*) as total FROM {$this->table_name}";
        
        if (!empty($whereClause)) {
            $query .= " WHERE " . $whereClause;
        }

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    // Get paginated results
    public function getPaginated($page = 1, $perPage = 10, $orderBy = "id DESC") {
        $offset = ($page - 1) * $perPage;
        $data = $this->getAll($perPage, $offset, $orderBy);
        $total = $this->countAll();
        $totalPages = ceil($total / $perPage);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
}
?>
Usage Examples:
php
<?php
// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Create dynamic model instance
$userModel = new dynamicModel($db, 'users');

// 1. Get all users
$allUsers = $userModel->getAll();

// 2. Get users with pagination
$page1Users = $userModel->getAll(10, 0); // First 10 users
$page2Users = $userModel->getAll(10, 10); // Next 10 users

// 3. Get user by ID
$user = $userModel->getById(1);

// 4. Get users by specific column
$activeUsers = $userModel->getByColumn('status', 'active');

// 5. Search users
$searchResults = $userModel->search([
    'name' => 'john',
    'email' => 'gmail'
]);

// 6. Custom WHERE clause
$customResults = $userModel->getWhere(
    "age > :age AND status = :status",
    [':age' => 18, ':status' => 'active']
);

// 7. Count records
$totalUsers = $userModel->countAll();
$activeCount = $userModel->countWhere("status = :status", [':status' => 'active']);

// 8. Paginated results
$paginated = $userModel->getPaginated(1, 10);
echo "Page 1 data: " . count($paginated['data']) . " records";
echo "Total pages: " . $paginated['pagination']['total_pages'];
?>
Key Features Added:
getAll() - Get all records with optional limit and ordering

getById() - Get single record by ID

getByColumn() - Get records by specific column value

search() - Search across multiple columns

getWhere() - Custom WHERE clause with parameters

countAll() - Count total records

countWhere() - Count records with conditions

getPaginated() - Get paginated results with metadata

These methods provide a comprehensive set of data retrieval options while maintaining the dynamic nature of your class.

