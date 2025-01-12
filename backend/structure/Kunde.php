<?php

class Kunde {
    private $vorname;
    private $nachname;
    private $kundennummer;

    // Konstruktor zum Initialisieren der Attribute
    public function __construct($vorname, $nachname, $kundennummer) {
        $this->vorname = $vorname;
        $this->nachname = $nachname;
        $this->kundennummer = $kundennummer;
    }

    // Getter-Methoden
    public function getVorname() {
        return $this->vorname;
    }

    public function getNachname() {
        return $this->nachname;
    }

    public function getKundennummer() {
        return $this->kundennummer;
    }

    // Setter-Methoden
    public function setVorname($vorname) {
        $this->vorname = $vorname;
    }

    public function setNachname($nachname) {
        $this->nachname = $nachname;
    }

    public function setKundennummer($kundennummer) {
        $this->kundennummer = $kundennummer;
    }
}
?>