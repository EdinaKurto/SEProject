<?php
require_once dirname(__FILE__) . "/../../config.php";

/**
 * The main class for interaction with database.
 *
 * All other DAO classes should inherit this class.
 */
class BaseDao
{

//    public function begin_transaction() {
//        $response = $this->connection->beginTransaction();
//    }
//
//    public function commit() {
//        $this->connection->commit();
//    }
//
//    public function rollback() {
//        $response = $this->connection->rollBack();
//    }
//    public function parse_order($order)
//    {
//        switch (substr($order, 0, 1)) {
//            case '-':
//                $order_direction = "ASC";
//                break;
//            case '+':
//                $order_direction = "DESC";
//                break;
//            default:
//                throw new Exception("Invalid order format. First character should be either + or -");
//                break;
//        };
//
//        // Filter SQL injection attacks on column name
//        $order_column = trim($this->connection->quote(substr($order, 1)), "'");
//
//        return [$order_column, $order_direction];
//    }

    protected $connection;
    private $table;
    private static $shared_connection = null;

    public function __construct($table)
    {
        $this->table = $table;

        if (self::$shared_connection === null) {
            try {
                self::$shared_connection = new PDO(
                    "mysql:host=" . Config::DB_HOST() . ";dbname=" . Config::DB_NAME() . ";charset=utf8;port=" . Config::DB_PORT(),
                    Config::DB_USER(),
                    Config::DB_PASSWORD(),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                error_log("PDO connection failed: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(["error" => "Database connection error."]);
                exit(); // prevent further execution
            }
        }

        $this->connection = self::$shared_connection;
    }

    protected function query($query, $params) {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function query_unique($query, $params) {
        $results = $this->query($query, $params);
        return reset($results);
    }

    protected function execute($query, $params) {
        $prepared_statement = $this->connection->prepare($query);
        if ($params) {
            foreach ($params as $key => $param) {
                $prepared_statement->bindValue($key, $param);
            }
        }
        $prepared_statement->execute();
        return $prepared_statement;
    }

    public function insert($table, $entity) {
        $query = "INSERT INTO {$table} (";
        // INSERT INTO patients (
        foreach ($entity as $column => $value) {
            $query .= $column . ", ";
        }
        // INSERT INTO patients (first_name, last_name,
        $query = substr($query, 0, -2);
        // INSERT INTO patients (first_name, last_name
        $query .= ") VALUES (";
        // INSERT INTO patients (first_name, last_name) VALUES (
        foreach ($entity as $column => $value) {
            $query .= ":" . $column . ", ";
        }
        // INSERT INTO patients (first_name, last_name) VALUES (:first_name, :last_name,
        $query = substr($query, 0, -2);
        // INSERT INTO patients (first_name, last_name) VALUES (:first_name, :last_name
        $query .= ")";
        // INSERT INTO patients (first_name, last_name) VALUES (:first_name, :last_name)

        $statement = $this->connection->prepare($query);
        $statement->execute($entity); // SQL injection prevention
        $entity['id'] = $this->connection->lastInsertId();
        return $entity;
   }
   public function update($table, $id, $entity, $id_column = "id")
    {
        $id = (int) $id;

        if (empty($entity)) {
            throw new InvalidArgumentException("Update data cannot be empty.");
        }

        $query = "UPDATE `$table` SET ";
        $fields = [];
        foreach ($entity as $name => $value) {
            $fields[] = "`$name` = :$name";
        }
        $query .= implode(", ", $fields);
        $query .= " WHERE `$id_column` = :id";

        $stmt = $this->connection->prepare($query);
        $entity['id'] = $id;
        $stmt->execute($entity);
        return $entity;
    }
    public function delete($table, $id, $id_column = "id"){

        $id = (int) $id;

        $query = "DELETE FROM `$table` WHERE `$id_column` = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0; // Returns true if a row was deleted, false otherwise
    }
}