<?php

class Angebot {
    private $blech_id;
    private $kunden_id;
    private $pauschalbetrag;

    // Konstruktor zum Initialisieren der Attribute
    public function __construct($blech_id, $kunden_id, $pauschalbetrag) {
        $this->blech_id = $blech_id;
        $this->kunden_id = $kunden_id;
        $this->pauschalbetrag = $pauschalbetrag;
    }

    // Getter-Methoden
    public function getBlechId() {
        return $this->blech_id;
    }

    public function getKundenId() {
        return $this->kunden_id;
    }

    public function getPauschalbetrag() {
        return $this->pauschalbetrag;
    }

    // Setter-Methoden
    public function setBlechId($blech_id) {
        $this->blech_id = $blech_id;
    }

    public function setKundenId($kunden_id) {
        $this->kunden_id = $kunden_id;
    }

    public function setPauschalbetrag($pauschalbetrag) {
        $this->pauschalbetrag = $pauschalbetrag;
    }
}
?>