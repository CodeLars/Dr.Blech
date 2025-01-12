<?php
// filepath: /c:/xampp/htdocs/Dr.Blech/backend/db.php

class Database {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "dr_blech";
    private $conn;

    // Konstruktor zum Herstellen der Verbindung
    public function __construct() {

        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Überprüfe die Verbindung
        if ($this->conn->connect_error) {
            die("Verbindung fehlgeschlagen: " . $this->conn->connect_error);
        }
    }

    // Methode zum Ausführen von SELECT-Abfragen
    public function select($sql) {
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // Methode zum Ausführen von INSERT-, UPDATE- und DELETE-Abfragen
    public function execute($sql) {
        if ($this->conn->query($sql) === TRUE) {
            // Wenn die Abfrage ein INSERT war, gib die letzte eingefügte ID zurück
            if (strpos(strtoupper($sql), 'INSERT') === 0) {
                return $this->conn->insert_id;
            }
            return true;
        } else {
            return "Fehler: " . $this->conn->error;
        }
    }

    // Destruktor zum Schließen der Verbindung
    public function __destruct() {
        $this->conn->close();
    }
}

// Beispielverwendung
// $db = new Database();
// $result = $db->select("SELECT * FROM users");
// print_r($result);
?>