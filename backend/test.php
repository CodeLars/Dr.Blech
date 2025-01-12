<?php 
require 'db.php';
require 'structure/Blech.php';
require 'structure/Kunde.php';
$db = new Database();

$db->select("SELECT * FROM Blech");

$kunde = new Kunde('Neukunde', 'Neukunde', 'Neukunde');
    
// Bereite die SQL-Abfrage für den Kunden vor
$sqlKunde = "INSERT INTO Kunde (vorname, nachname, kundennummer) VALUES (
    '" . $kunde->getVorname() . "',
    '" . $kunde->getNachname() . "',
    '" . $kunde->getKundennummer() . "'
)";

?>