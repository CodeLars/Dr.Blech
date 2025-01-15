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
            if (isset($input['customAmount'])) {
                $blechId = (int)$input['blechId'];
                $customerNumber = (int)($input['customerNumber']);
                $customAmount = $input['customAmount'];

                $sql = "SELECT Menge 
                FROM blech where id = $blechId";

                $result = $this->db->select($sql);
                $menge = $result[0]['Menge'];
                // Kunden-ID aus der Datenbank anhand der Kundennummer holen
                $customerSql = "SELECT id FROM kunde WHERE kundennummer = $customerNumber";
                $customerResult = $this->db->select($customerSql);

                if ($customerResult && count($customerResult) > 0) {
                    $customerId = (int)$customerResult[0]['id'];
                    $gewinnzuschlag = round($this->db->escape($customAmount) * 0.30, 2);
                    $einzelpreis = round($customAmount,2)+round($this->db->escape($customAmount) * 0.30, 2);
                    $pauschalbetrag = round($einzelpreis * $menge, 2);
                    // Berechnung und Erstellung des Angebots
                    // Hier kannst du die Berechnungen für das Angebot hinzufügen
                    // ...

                    // Beispiel für die Erstellung eines Angebots in der Datenbank
                    $sql = "INSERT INTO angebot (blech_id, kunden_id, einzelpreis, gewinnzuschlag, pauschalbetrag) VALUES (
                        " . $this->db->escape($blechId) . ",
                        " . $this->db->escape($customerId) . ",
                        " . $this->db->escape($einzelpreis) . ",
                        ". $this->db->escape($gewinnzuschlag).",
                        ". $this->db->escape($pauschalbetrag)."

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
                $result = $this->calculateOffer($input['blechId'], $input['customerNumber']);
                if (is_numeric($result)) {
                    echo json_encode(['message' => 'custom Angebot erfolgreich erstellt', 'id' => $result]);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Fehler beim Erstellen des Angebots', 'error' => $result]);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Ungültige Daten']);
        }
    }

    private function calculateOffer($blechId, $customerNumber, $customAmount = null) {
        // Get customer ID from customerNumber
        $customerSql = "SELECT id FROM kunde WHERE kundennummer = '" . $this->db->escape($customerNumber)."'";
        $customerResult = $this->db->select($customerSql);
        
        if (!$customerResult || count($customerResult) === 0) {
            return false; // Customer not found
        }
        
        $customerId = (int)$customerResult[0]['id'];
        
        // Get Blech and Material data
        $sql = "SELECT b.*, m.name as materialName, m.dichte, m.kosten_per_kg 
                FROM blech b
                JOIN material m ON b.Material = m.id
                WHERE b.id = " . $this->db->escape($blechId);
        
        $blechResult = $this->db->select($sql);
    
        if ($blechResult && count($blechResult) > 0) {
            $blech = $blechResult[0];
            
            if ($customAmount !== null) {
                $gesamtpreis = $customAmount;
            } else {
                // Calculate dimensions and volume (cm³)
                $flaeche = $blech['Breite'] * $blech['Länge']; // cm²
                $volumen = ($flaeche * $blech['Dicke']) / 10; // cm³ (Dicke in mm -> cm)
                
                // Calculate weight (kg) and material costs
                $gewicht = ($volumen * $blech['dichte']) / 1000; // Dichte in g/cm³ -> kg
                $materialkosten = round($gewicht * $blech['kosten_per_kg'], 2);
                
                // Processing costs
                $verarbeitungskosten = 0;
                $verarbeitungskosten += 10.00;
                if ($blech['Stanzen'] == '1') $verarbeitungskosten += 20.00;
                if ($blech['Biegen'] == '1') $verarbeitungskosten += 15.00;
                if ($blech['Oberflächenbehandlung'] == '1') $verarbeitungskosten += 25.00;
                if ($blech['Einfräsen'] == '1') $verarbeitungskosten += 17.00;
                
                // Calculate subtotal
                $zwischensumme = round($materialkosten + $verarbeitungskosten, 2);
                $gewinnzuschlag = round($zwischensumme * 0.30, 2);
                $einzelpreis = round($zwischensumme + $gewinnzuschlag, 2);
                $gesamtpreis = round($einzelpreis * $blech['Menge'], 2);
            }
            
            // Create offer in database
            $sql = "INSERT INTO angebot (
                blech_id, 
                kunden_id, 
                materialkosten,
                verarbeitungskosten,
                zwischensumme,
                gewinnzuschlag,
                einzelpreis,
                pauschalbetrag
            ) VALUES (
                " . $this->db->escape($blechId) . ",
                " . $this->db->escape($customerId) . ",
                " . $this->db->escape($materialkosten) . ",
                " . $this->db->escape($verarbeitungskosten) . ",
                " . $this->db->escape($zwischensumme) . ",
                " . $this->db->escape($gewinnzuschlag) . ",
                " . $this->db->escape($einzelpreis) . ",
                " . $this->db->escape($gesamtpreis) . "
            )";
            
            $result = $this->db->execute($sql);
            if (is_numeric($result)) {
                return $result;
            }
        }
        return false;
    }
}
