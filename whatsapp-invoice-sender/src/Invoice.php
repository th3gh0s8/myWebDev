<?php

class Invoice
{
    private $conn;
    private $table_name = "invoices";

    public $id;
    public $user_id;
    public $customer_id;
    public $invoice_data;
    public $status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (user_id, customer_id, invoice_data, status) VALUES (:user_id, :customer_id, :invoice_data, :status)";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->invoice_data = htmlspecialchars(strip_tags($this->invoice_data));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":invoice_data", $this->invoice_data);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function readAll($user_id)
    {
        $query = "SELECT i.id, i.invoice_data, i.status, c.name AS customer_name FROM " . $this->table_name . " i LEFT JOIN customers c ON i.customer_id = c.id WHERE i.user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
