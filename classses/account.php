<?php
class Account
{
    private $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function findByLogin(string $login)
    {
        $column = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        try {
            $stmt = $this->conn->prepare(
                "SELECT user_id, name, email, password FROM users WHERE {$column} = :login LIMIT 1"
            );
            $stmt->execute(['login' => $login]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Account login lookup failed: ' . $e->getMessage());
            return false;
        }
    }

    public function findByEmail(string $email)
    {
        try {
            $stmt = $this->conn->prepare(
                'SELECT user_id FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Account email lookup failed: ' . $e->getMessage());
            return false;
        }
    }

    public function findByUsername(string $username)
    {
        try {
            $stmt = $this->conn->prepare(
                'SELECT user_id FROM users WHERE username = :username LIMIT 1'
            );
            $stmt->execute(['username' => $username]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Account username lookup failed: ' . $e->getMessage());
            return false;
        }
    }

    public function register(
        string $name,
        string $email,
        string $username,
        string $gender,
        string $address,
        string $password
    ): bool {
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO users (name, email, username, gender, address, password)
                 VALUES (:name, :email, :username, :gender, :address, :password)'
            );
            return $stmt->execute([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'gender' => $gender,
                'address' => $address,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        } catch (PDOException $e) {
            error_log('Account registration failed: ' . $e->getMessage());
            return false;
        }
    }
}
