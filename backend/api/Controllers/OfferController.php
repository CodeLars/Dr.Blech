<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Erlaubt die Methoden POST, GET und OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization
header('Content-Type: application/json'); // Setzt den Content-Type Header auf JSON

require_once __DIR__ . '\..\db.php';
require_once __DIR__ . '\..\..\structure\Blech.php';
require_once __DIR__ . '\..\..\structure\Angebot.php';
require_once __DIR__ . '\..\..\fpdf\fpdf.php';

class OfferController
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function handleRequest($method)
    {
        switch ($method) {
            case 'OPTIONS':
                http_response_code(200);
                break;
            case 'GET':
                $this->getOffers();
                break;
            case 'POST':
                $this->createOffer();
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }

    private function getOffers()
    {
        $sql = "SELECT angebot.id, blech.Blechart, kunde.vorname, kunde.nachname, angebot.pauschalbetrag 
                FROM angebot 
                JOIN blech ON angebot.blech_id = blech.id 
                JOIN kunde ON angebot.kunden_id = kunde.id";
        $result = $this->db->select($sql);
        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Fehler beim Laden der Angebote']);
        }
    }

    private function createOffer()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $logFile = __DIR__ . '/error_log.txt';
        file_put_contents($logFile, "Empfangene Daten: " . print_r($input, true), FILE_APPEND);

        if ($input) {
            if ($input['customAmount']) {
                $blechId = (int)$input['blechId'];
                $customerNumber = (int)($input['customerNumber']);
                $customAmount = $input['customAmount'];

                // Kunden-ID aus der Datenbank anhand der Kundennummer holen
                $customerSql = "SELECT id FROM kunde WHERE kundennummer = $customerNumber";
                $customerResult = $this->db->select($customerSql);

                if ($customerResult && count($customerResult) > 0) {
                    $customerId = (int)$customerResult[0]['id'];

                    // Berechnung und Erstellung des Angebots
                    // Hier kannst du die Berechnungen für das Angebot hinzufügen
                    // ...

                    // Beispiel für die Erstellung eines Angebots in der Datenbank
                    $sql = "INSERT INTO angebot (blech_id, kunden_id, pauschalbetrag) VALUES (
                        " . $this->db->escape($blechId) . ",
                        " . $this->db->escape($customerId) . ",
                        " . ($customAmount !== null ? $this->db->escape($customAmount) : 'NULL') . "
                    )";

                    $result = $this->db->execute($sql);
                    if (is_numeric($result)) {
                        echo json_encode(['message' => 'Angebot erfolgreich erstellt', 'id' => $result]);
                    } else {
                        http_response_code(500);
                        echo json_encode(['message' => 'Fehler beim Erstellen des Angebots', 'error' => $result]);
                    }
                } else {
                    http_response_code(400);
                    echo json_encode(['message' => 'Ungültige Kundennummer']);
                }
            } else {
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Ungültige Daten']);
        }
    }

    private function calculateOffer($blechId) {
        
    }
}
