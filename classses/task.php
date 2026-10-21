<?php

class Task {
    private $conn;
    private $table = "tasks";
    private $userId;

    // Properties matching the tasks table
    public $task_id;
    public $task_title;
    public $subject;
    public $description;
    public $deadline;
    public $priority;
    public $status;

    public function __construct($db, $userId) {
        $this->conn = $db;
        $this->userId = (int) $userId;
    }

    /**
     * Insert a new task.
     */
    public function addTask() {
        $query = "INSERT INTO " . $this->table . "
                  (user_id, task_title, subject, description, deadline, priority, status)
                  VALUES (:user_id, :task_title, :subject, :description, :deadline, :priority, :status)";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
            $stmt->bindParam(":task_title", $this->task_title);
            $stmt->bindParam(":subject", $this->subject);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":deadline", $this->deadline);
            $stmt->bindParam(":priority", $this->priority);
            $stmt->bindParam(":status", $this->status);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("addTask failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all tasks, soonest deadline first.
     */
    public function getTasks() {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id ORDER BY task_id ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getTasks failed: " . $e->getMessage());
            return [];
        }
    }

    public function getSummary() {
        $query = "SELECT
                      COUNT(*) AS total,
                      COALESCE(SUM(CASE WHEN status = 'Pending' AND deadline >= CURDATE() THEN 1 ELSE 0 END), 0) AS pending,
                      COALESCE(SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END), 0) AS completed,
                      COALESCE(SUM(CASE WHEN status = 'Overdue' OR (status = 'Pending' AND deadline < CURDATE()) THEN 1 ELSE 0 END), 0) AS overdue
                  FROM " . $this->table . "
                  WHERE user_id = :user_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getSummary failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch a single task by id (used to pre-fill the edit form).
     */
    public function getTaskById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE task_id = :task_id AND user_id = :user_id LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $id);
            $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getTaskById failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing task's details.
     */
    public function updateTask($id) {
        $query = "UPDATE " . $this->table . "
                  SET task_title = :task_title,
                      subject = :subject,
                      description = :description,
                      deadline = :deadline,
                      priority = :priority,
                      status = :status
                  WHERE task_id = :task_id AND user_id = :user_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_title", $this->task_title);
            $stmt->bindParam(":subject", $this->subject);
            $stmt->bindParam(":description", $this->description);
            $stmt->bindParam(":deadline", $this->deadline);
            $stmt->bindParam(":priority", $this->priority);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":task_id", $id);
            $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("updateTask failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a task by id.
     */
    public function deleteTask($id) {
        $query = "DELETE FROM " . $this->table . " WHERE task_id = :task_id AND user_id = :user_id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":task_id", $id);
            $stmt->bindValue(":user_id", $this->userId, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() === 1;
        } catch (PDOException $e) {
            error_log("deleteTask failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search tasks by keyword across title, subject, description,
     * priority, and status.
     */
    public function searchTasks($keyword) {
        $query = "SELECT * FROM " . $this->table . "
                  WHERE user_id = ?
                    AND (task_title LIKE ?
                     OR subject LIKE ?
                     OR description LIKE ?
                     OR priority LIKE ?
                     OR status LIKE ?)
                  ORDER BY deadline ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $search = "%" . $keyword . "%";
            $stmt->execute(array_merge([$this->userId], array_fill(0, 5, $search)));
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("searchTasks failed: " . $e->getMessage());
            return [];
        }
    }
}
