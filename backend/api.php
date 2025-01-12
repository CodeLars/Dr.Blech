<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Erlaubt die Methoden POST, GET und OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization

require 'db.php';
require_once 'structure/Blech.php';
require_once 'structure/Kunde.php';
require_once 'structure/Angebot.php';

// Überprüfe die HTTP-Methode
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Behandle die Preflight-Anfrage
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getCustomers') {
    // Erstelle eine Datenbankverbindung
    $db = new Database();

    // Bereite die SQL-Abfrage vor
    $sql = "SELECT vorname, nachname, kundennummer FROM Kunde WHERE kundennummer != 'Neukunde'";

    // Führe die SQL-Abfrage aus
    $result = $db->select($sql);

    // Sende die Kundeninformationen zurück
    echo json_encode($result);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getOffers') {
    // Erstelle eine Datenbankverbindung
    $db = new Database();

    // Bereite die SQL-Abfrage vor
    $sql = "SELECT angebot.id, blech.Blechart, kunde.vorname, kunde.nachname, angebot.pauschalbetrag 
            FROM angebot 
            JOIN blech ON angebot.blech_id = blech.id 
            JOIN kunde ON angebot.kunden_id = kunde.id";

    // Führe die SQL-Abfrage aus
    $result = $db->select($sql);

    // Sende die Angebotsinformationen zurück
    echo json_encode($result);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Empfange die JSON-Daten
    $input = json_decode(file_get_contents('php://input'), true);

    // Überprüfe, ob die Daten empfangen wurden
    if ($input) {

        $pauschalbetrag = 0;
        if (empty($input['customAmount'])) {
            $materialDichten = [
                'Zink' => 7.14,
                'Weißblech' => 7.85,
                'Kupfer' => 8.96,
                'Gold' => 19.32,
                'Silber' => 10.49,
                'Edelstahl' => 7.85
            ];
    
            // Materialkosten pro kg in Euro (Beispieldaten)
            $materialKostenProKg = [
                'Zink' => 2.5,
                'Weißblech' => 1.5,
                'Kupfer' => 8.0,
                'Gold' => 50000.0,
                'Silber' => 700.0,
                'Edelstahl' => 3.0
            ];
    
            // Berechne das Volumen in cm³
            $volumen = $input['width'] * $input['length'] * $input['thickness']; // Breite * Länge * Dicke
    
            // Berechne das Gewicht in kg
            $gewicht = ($volumen * $materialDichten[$input['material']]) / 1000; // Volumen * Dichte / 1000
    
            // Berechne die Materialkosten
            $materialkosten = $gewicht * $materialKostenProKg[$input['material']];
             // Hier kannst du die Materialkosten berechnen oder festlegen
            $verarbeitungskosten = 0;
            if (!empty($input['stamping'])) {
                $verarbeitungskosten += 20;
            }
            if (!empty($input['bending'])) {
                $verarbeitungskosten += 15;
            }
            if (!empty($input['surfaceTreatment'])) {
                $verarbeitungskosten += 25;
            }
            if (!empty($input['milling'])) {
                $verarbeitungskosten += 17;
            }
            $pauschalbetrag = $materialkosten + $verarbeitungskosten;
        } else {
            $pauschalbetrag = $input['customAmount'];
        }
        // 30% auf den Pauschalbetrag aufschlagen
        $pauschalbetrag *= 1.3;

        // Pauschalbetrag mit der gelieferten Menge multiplizieren
        $pauschalbetrag *= $input['quantity'];
        

        // Erstelle ein Blech-Objekt
        $blech = new Blech(
            $input['newCustomer'],
            $input['existingCustomer'],
            $input['blechart'],
            $input['material'],
            $input['width'],
            $input['length'],
            $input['thickness'],
            $input['customAmount'],
            $input['stamping'],
            $input['bending'],
            $input['surfaceTreatment'],
            $input['milling'],
            $input['quantity']
        );

        // Erstelle eine Datenbankverbindung
        $db = new Database();

        // Bereite die SQL-Abfrage für das Blech vor
        $sqlBlech = "INSERT INTO blech (Blechart, Material, Breite, Länge, Dicke, Stanzen, Biegen, Oberflächenbehandlung, Einfräsen, Menge, zuschneiden) VALUES (
            '" . $blech->getBlechart() . "',
            '" . $blech->getMaterial() . "',
            '" . $blech->getWidth() . "',
            '" . $blech->getLength() . "',
            '" . $blech->getThickness() . "',
            '" . $blech->getStamping() . "',
            '" . $blech->getBending() . "',
            '" . $blech->getSurfaceTreatment() . "',
            '" . $blech->getMilling() . "',
            '" . $blech->getQuantity() . "',
            '1'
            
        )";
        $currentBlechID = $db->execute($sqlBlech);

        // Überprüfe, ob es sich um einen Neukunden handelt
        if ($input['newCustomer']) {
            // Erstelle ein Kunde-Objekt
            $kunde = new Kunde('Neukunde', 'Neukunde', 'Neukunde');

            // Bereite die SQL-Abfrage für den Kunden vor
            $sqlKunde = "INSERT INTO Kunde (vorname, nachname, kundennummer) VALUES (
                '" . $kunde->getVorname() . "',
                '" . $kunde->getNachname() . "',
                '" . $kunde->getKundennummer() . "'
            )";
            $currentKundenID = $db->execute($sqlKunde);
        } else {
            // Splitte den existingCustomer String, um die Kundennummer zu extrahieren

            // Führe eine SELECT-Abfrage aus, um die ID des Bestandskunden zu ermitteln
            $sqlSelectKunde = "SELECT id FROM Kunde WHERE kundennummer = '" . $input['existingCustomer'] . "'";
            $resultSelectKunde = $db->select($sqlSelectKunde);
            if (!empty($resultSelectKunde)) {
                $currentKundenID = $resultSelectKunde[0]['id'];
            } else {
                // Sende eine Fehlermeldung zurück, wenn der Kunde nicht gefunden wurde
                http_response_code(404);
                echo json_encode(['message' => 'Kunde nicht gefunden']);
                exit();
            }
        }

        // Erstelle ein Angebot-Objekt
        $angebot = new Angebot($currentBlechID, $currentKundenID, $pauschalbetrag); // Beispielwert für Pauschalbetrag

        // Bereite die SQL-Abfrage für das Angebot vor
        $sqlAngebot = "INSERT INTO angebot (blech_id, kunden_id, pauschalbetrag) VALUES (
            '" . $angebot->getBlechId() . "',
            '" . $angebot->getKundenId() . "',
            '" . $angebot->getPauschalbetrag() . "'
        )";
        $db->execute($sqlAngebot);

        // Sende eine Erfolgsmeldung zurück
        echo json_encode(['message' => 'Angebot erfolgreich erstellt', 'blech_id' => $currentBlechID, 'kunden_id' => $currentKundenID]);
    } else {
        // Sende eine Fehlermeldung zurück
        http_response_code(400);
        echo json_encode(['message' => 'Ungültige Daten']);
    }
} else {
    // Sende eine Fehlermeldung zurück
    http_response_code(405);
    echo json_encode(['message' => 'Methode nicht erlaubt']);
}
?>