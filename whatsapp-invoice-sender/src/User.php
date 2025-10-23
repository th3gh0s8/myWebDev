<?php

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (username, password) VALUES (:username, :password)";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = htmlspecialchars(strip_tags($this->password));

        $stmt->bindParam(":username", $this->username);

        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $password_hash);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function userExists()
    {
        $query = "SELECT id, password FROM " . $this->table_name . " WHERE username = ?";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));

        $stmt->bindParam(1, $this->username);

        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $password_hash = $row['password'];

            if (password_verify($this->password, $password_hash)) {
                return true;
            }
        }

        return false;
    }
}
