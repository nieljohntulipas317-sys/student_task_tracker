<?php
/**
 * Database
 * Handles the PDO connection used by the application.
 */
class Database {
    private $config;
    public $conn;

    public function __construct() {
        $this->config = [
            "host" => "localhost",
            "db_name" => "student_task_tracker",
            "username" => "root",
            "password" => "",
        ];

        $localConfig = __DIR__ . "/db_config.local.php";
        if (is_file($localConfig)) {
            $overrides = require $localConfig;
            if (!is_array($overrides)) {
                throw new UnexpectedValueException("Database config must return an array.");
            }
            $this->config = array_merge($this->config, $overrides);
        }
    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->config["host"] . ";dbname=" . $this->config["db_name"] . ";charset=utf8mb4",
                $this->config["username"],
                $this->config["password"]
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            exit("Unable to connect to the task database.");
        }
        return $this->conn;
    }
}
