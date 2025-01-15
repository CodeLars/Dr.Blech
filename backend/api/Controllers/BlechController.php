<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS'); // Erlaubt die Methoden POST, GET, PUT, DELETE und OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization
header('Content-Type: application/json'); // Setzt den Content-Type Header auf JSON

require_once __DIR__ . '\..\db.php';
require_once __DIR__ . '\..\..\structure\Kunde.php';

class BlechController {
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
                $this->getBlech();
                break;
            case 'POST':
                $this->createBlech();
                break;
            case 'PUT':
                $this->updateBlech();
                break;
            case 'DELETE':
                $this->deleteBlech();
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }

    private function getBlech() {
        $sql = "SELECT * FROM Blech";
        $result = $this->db->select($sql);
        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Fehler beim Laden der Bleche']);
        }
    }

    private function createBlech() {
        // Überprüfe, ob die Daten als JSON gesendet wurden
        $input = json_decode(file_get_contents('php://input'), true);


        // Schreibe die empfangenen Daten in eine Log-Datei zur Überprüfung
        $logFile = __DIR__ . '/error_log.txt';
        file_put_contents($logFile, "Empfangene Daten: " . print_r($input, true), FILE_APPEND);

        if ($input) {

            // Material-ID aus der material Tabelle holen
            $materialId = (int)$input['material']; // Stelle sicher, dass die Material-ID als Integer behandelt wird
            $materialSql = "SELECT id FROM material WHERE id = '$materialId'";
            $materialResult = $this->db->select($materialSql);

            if ($materialResult && count($materialResult) > 0) {
                $materialId = (int)$materialResult[0]['id'];

                $blech = new Blech(
                    $input['blechart'],
                    $materialId, // Verwende die Material-ID als Integer
                    $input['width'],
                    $input['length'],
                    $input['thickness'],
                    $input['stamping'] ?? "false", // Behandle leere Felder als false
                    $input['bending'] ?? "false", // Behandle leere Felder als false
                    $input['surfaceTreatment'] ?? "false", // Behandle leere Felder als false
                    $input['milling'] ?? "false", // Behandle leere Felder als false
                    $input['quantity'],
                    $input['cutting'] ?? "false"
                );

                $sql = "INSERT INTO Blech (Blechart, Material, Breite, Länge, Dicke, Zuschneiden, Stanzen, Biegen, Oberflächenbehandlung, Einfräsen, Menge) VALUES (
                    '" . $this->db->escape($blech->getBlechart()) . "',
                    " . $this->db->escape($blech->getMaterial()) . ",
                    '" . $this->db->escape($blech->getWidth()) . "',
                    '" . $this->db->escape($blech->getLength()) . "',
                    '" . $this->db->escape($blech->getThickness()) . "',
                    '" . $this->db->escape($blech->getCutting()) . "',
                    '" . $this->db->escape($blech->getStamping()) . "',
                    '" . $this->db->escape($blech->getBending()) . "',
                    '" . $this->db->escape($blech->getSurfaceTreatment()) . "',
                    '" . $this->db->escape($blech->getMilling()) . "',
                    '" . $this->db->escape($blech->getQuantity()) . "'
                )";
                $insertId = $this->db->execute($sql);
                if (is_numeric($insertId)) {
                    echo json_encode(['message' => 'Blech erfolgreich erstellt', 'id' => $insertId]);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Fehler beim Erstellen des Blechs']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Ungültiges Material']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Ungültige Daten']);
        }
    }


    private function updateBlech() {
        $input = json_decode(file_get_contents('php://input'), true);

        
        if ($input && isset($input['id'])) {
            // Material-ID aus der material Tabelle holen
            $materialName = $this->db->escape($input['material']);
            $materialSql = "SELECT id FROM material WHERE name = '$materialName'";
            $materialResult = $this->db->select($materialSql);

            if ($materialResult && count($materialResult) > 0) {
                $materialId = $materialResult[0]['id'];

                $blech = new Blech(
                    $input['blechart'],
                    $materialId, // Verwende die Material-ID
                    $input['width'],
                    $input['length'],
                    $input['thickness'],
                    $input['customAmount'],
                    $input['cutting'],
                    $input['stamping'],
                    $input['bending'],
                    $input['surfaceTreatment'],
                    $input['milling'],
                    $input['quantity']
                );

                $sql = "UPDATE Blech SET 
                    Blechart = '" . $this->db->escape($blech->getBlechart()) . "',
                    Material = '" . $this->db->escape($blech->getMaterial()) . "',
                    Breite = '" . $this->db->escape($blech->getWidth()) . "',
                    Länge = '" . $this->db->escape($blech->getLength()) . "',
                    Dicke = '" . $this->db->escape($blech->getThickness()) . "',
                    Zuschneiden = '" . $this->db->escape($blech->getCutting()) . "',
                    Stanzen = '" . $this->db->escape($blech->getStamping()) . "',
                    Biegen = '" . $this->db->escape($blech->getBending()) . "',
                    Oberflächenbehandlung = '" . $this->db->escape($blech->getSurfaceTreatment()) . "',
                    Einfräsen = '" . $this->db->escape($blech->getMilling()) . "',
                    Menge = '" . $this->db->escape($blech->getQuantity()) . "'
                    WHERE id = " . $this->db->escape($input['id']);

                if ($this->db->execute($sql)) {
                    echo json_encode(['message' => 'Blech erfolgreich aktualisiert']);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Fehler beim Aktualisieren des Blechs']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Ungültiges Material']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Ungültige Daten']);
        }
    }

    private function deleteBlech() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input && isset($input['id'])) {
            $sql = "DELETE FROM Blech WHERE id = " . $this->db->escape($input['id']);
            if ($this->db->execute($sql)) {
                echo json_encode(['message' => 'Blech erfolgreich gelöscht']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Fehler beim Löschen des Blechs']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Ungültige Daten']);
        }
    }
}
?>