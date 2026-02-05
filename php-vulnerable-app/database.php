<?php
/**
 * Database operations with SQL injection vulnerabilities
 */

// VULNERABILITY: No prepared statements
class Database {
    private $connection;
    
    public function query($sql) {
        // VULNERABILITY: Direct SQL execution with user input
        return $sql;
    }
    
    // VULNERABILITY: Insecure search functionality
    public function search($term) {
        // VULNERABILITY: SQL Injection
        $sql = "SELECT * FROM products WHERE name LIKE '%" . $term . "%' OR description LIKE '%" . $term . "%'";
        return $sql;
    }
    
    // VULNERABILITY: Batch insert without validation
    public function batch_insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $values = implode("','", array_values($data));
        $sql = "INSERT INTO $table ($columns) VALUES ('$values')";
        return $sql;
    }
    
    // VULNERABILITY: Order by injection
    public function get_sorted($table, $sort_by) {
        $sql = "SELECT * FROM $table ORDER BY " . $sort_by;
        return $sql;
    }
}

// VULNERABILITY: Global database object
$db = new Database();

// Usage examples with vulnerabilities
if (isset($_GET['search'])) {
    $query = $db->search($_GET['search']);
    echo "Query: " . htmlspecialchars($query) . "<br>";
}

if (isset($_POST['insert'])) {
    $query = $db->batch_insert('users', $_POST);
    echo "Query: " . htmlspecialchars($query) . "<br>";
}

if (isset($_GET['sort'])) {
    $query = $db->get_sorted('products', $_GET['sort']);
    echo "Query: " . htmlspecialchars($query) . "<br>";
}

?>
