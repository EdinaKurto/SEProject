<?php
require_once dirname(__FILE__) . "/../../config.php";
class BaseDao
{
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
                exit(); 
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
        foreach ($entity as $column => $value) {
            $query .= $column . ", ";
        }
        $query = substr($query, 0, -2);
        $query .= ") VALUES (";
        foreach ($entity as $column => $value) {
            $query .= ":" . $column . ", ";
        }
        $query = substr($query, 0, -2);
        $query .= ")";

        $statement = $this->connection->prepare($query);
        $statement->execute($entity);
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

        return $stmt->rowCount() > 0; 
    }
}