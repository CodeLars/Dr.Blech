<?php


class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "dr_blech";
    private $conn;


    public function __construct() {

        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Verbindung fehlgeschlagen: " . $this->conn->connect_error);
        }
    }

    public function select($sql) {
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

 
    public function execute($sql) {
        if ($this->conn->query($sql) === TRUE) {

            if (strpos(strtoupper($sql), 'INSERT') === 0) {
                return $this->conn->insert_id;
            }
            return true;
        } else {
            return "Fehler: " . $this->conn->error;
        }
    }
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }

    public function __destruct() {
        $this->conn->close();
    }
}


?>