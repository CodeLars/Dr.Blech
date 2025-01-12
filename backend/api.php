<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Erlaubt die Methoden POST, GET und OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization

require 'db.php';
require_once 'structure/Blech.php';
require_once 'structure/Kunde.php';
require_once 'structure/Angebot.php';


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getCustomers') {

    $db = new Database();
    $sql = "SELECT vorname, nachname, kundennummer FROM Kunde WHERE kundennummer != 'Neukunde'";
    $result = $db->select($sql);
    echo json_encode($result);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getOffers') {

    $db = new Database();


    $sql = "SELECT angebot.id, blech.Blechart, kunde.vorname, kunde.nachname, angebot.pauschalbetrag 
            FROM angebot 
            JOIN blech ON angebot.blech_id = blech.id 
            JOIN kunde ON angebot.kunden_id = kunde.id";

    $result = $db->select($sql);

    echo json_encode($result);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $input = json_decode(file_get_contents('php://input'), true);


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
    

            $materialKostenProKg = [
                'Zink' => 2.5,
                'Weißblech' => 1.5,
                'Kupfer' => 8.0,
                'Gold' => 50000.0,
                'Silber' => 700.0,
                'Edelstahl' => 3.0
            ];
    

            $volumen = $input['width'] * $input['length'] * $input['thickness']; 
    
    
            $gewicht = ($volumen * $materialDichten[$input['material']]) / 1000; 
    

            $materialkosten = $gewicht * $materialKostenProKg[$input['material']];

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

        $pauschalbetrag *= 1.3;

 
        $pauschalbetrag *= $input['quantity'];
        


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


        $db = new Database();

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

        if ($input['newCustomer']) {

            $kunde = new Kunde('Neukunde', 'Neukunde', 'Neukunde');


            $sqlKunde = "INSERT INTO Kunde (vorname, nachname, kundennummer) VALUES (
                '" . $kunde->getVorname() . "',
                '" . $kunde->getNachname() . "',
                '" . $kunde->getKundennummer() . "'
            )";
            $currentKundenID = $db->execute($sqlKunde);
        } else {

            $sqlSelectKunde = "SELECT id FROM Kunde WHERE kundennummer = '" . $input['existingCustomer'] . "'";
            $resultSelectKunde = $db->select($sqlSelectKunde);
            if (!empty($resultSelectKunde)) {
                $currentKundenID = $resultSelectKunde[0]['id'];
            } else {

                http_response_code(404);
                echo json_encode(['message' => 'Kunde nicht gefunden']);
                exit();
            }
        }


        $angebot = new Angebot($currentBlechID, $currentKundenID, $pauschalbetrag); 


        $sqlAngebot = "INSERT INTO angebot (blech_id, kunden_id, pauschalbetrag) VALUES (
            '" . $angebot->getBlechId() . "',
            '" . $angebot->getKundenId() . "',
            '" . $angebot->getPauschalbetrag() . "'
        )";
        $db->execute($sqlAngebot);


        echo json_encode(['message' => 'Angebot erfolgreich erstellt', 'blech_id' => $currentBlechID, 'kunden_id' => $currentKundenID]);
    } else {

        http_response_code(400);
        echo json_encode(['message' => 'Ungültige Daten']);
    }
} else {

    http_response_code(405);
    echo json_encode(['message' => 'Methode nicht erlaubt']);
}
?>