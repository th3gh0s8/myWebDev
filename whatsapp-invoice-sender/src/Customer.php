<?php

class Customer
{
    private $conn;
    private $table_name = "customers";

    public $id;
    public $user_id;
    public $name;
    public $phone_number;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (user_id, name, phone_number) VALUES (:user_id, :name, :phone_number)";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone_number", $this->phone_number);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function readAll($user_id)
    {
        $query = "SELECT id, name, phone_number FROM " . $this->table_name . " WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function readOne($id)
    {
        $query = "SELECT id, name, phone_number FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
