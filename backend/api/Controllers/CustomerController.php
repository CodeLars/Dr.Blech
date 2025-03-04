<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Erlaubt die Methoden POST, GET und OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization
header('Content-Type: application/json'); // Setzt den Content-Type Header auf JSON

require_once __DIR__ . '\..\db.php';
require_once __DIR__ . '\..\..\structure\Kunde.php';

class CustomerController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest($method) {
        switch ($method) {
            case 'OPTIONS':
                http_response_code(200);
                break;
            case 'GET':
                $this->getCustomers();
                break;
            case 'POST':
                $this->createCustomer();
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }

    private function getCustomers() {
        $sql = "SELECT vorname, nachname, kundennummer FROM Kunde WHERE kundennummer != 'Neukunde'";
        $result = $this->db->select($sql);
        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Fehler beim Laden der Kunden']);
        }
    }

    private function createCustomer() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $kunde = new Kunde($input['vorname'], $input['nachname'], $this->generateCustomerNumber());
            $sql = "INSERT INTO Kunde (vorname, nachname, kundennummer) VALUES (
                '" . $this->db->escape($kunde->getVorname()) . "',
                '" . $this->db->escape($kunde->getNachname()) . "',
                '" . $this->db->escape($kunde->getKundennummer()) . "'
            )";
            if ($this->db->execute($sql)) {
                echo json_encode(['message' => 'Customer created successfully', 'customerNumber' => $kunde->getKundennummer()]);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Fehler beim Erstellen des Kunden']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid data']);
        }
    }
    private function generateCustomerNumber() {
        do {
            // Generiere eine zufällige Kundennummer mit 8 Zeichen
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $customerNumber = '';
            for ($i = 0; $i < 8; $i++) {
                $customerNumber .= $characters[rand(0, strlen($characters) - 1)];
            }
    
            // Prüfe ob die Kundennummer bereits existiert
            $sql = "SELECT COUNT(*) as count FROM Kunde WHERE kundennummer = '" . 
                   $this->db->escape($customerNumber) . "'";
            $result = $this->db->select($sql);
            $exists = $result[0]['count'] > 0;
    
        } while ($exists); // Wiederhole, falls die Nummer bereits existiert
    
        return $customerNumber;
    }
}
?>