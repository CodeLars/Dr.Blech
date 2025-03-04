<?php 
class Blech {
    private $blechart;
    private $material;
    private $width;
    private $length;
    private $thickness;
    private $stamping;
    private $bending;
    private $surfaceTreatment;
    private $milling;
    private $quantity;
    private $cutting;

    // Konstruktor zum Initialisieren der Attribute
    public function __construct($blechart, $material, $width, $length, $thickness, $stamping, $bending, $surfaceTreatment, $milling, $quantity, $cutting) {

        $this->blechart = $blechart;
        $this->material = $material;
        $this->width = $width;
        $this->length = $length;
        $this->thickness = $thickness;
        $this->stamping = $stamping;
        $this->bending = $bending;
        $this->surfaceTreatment = $surfaceTreatment;
        $this->milling = $milling;
        $this->quantity = $quantity;
        $this->$cutting = $cutting;
    }

    // Getter-Methoden

    public function getCutting() {
        return $this->cutting;
    }

    public function getBlechart() {
        return $this->blechart;
    }

    public function getMaterial() {
        return $this->material;
    }

    public function getWidth() {
        return $this->width;
    }

    public function getLength() {
        return $this->length;
    }

    public function getThickness() {
        return $this->thickness;
    }


    public function getStamping() {
        return $this->stamping;
    }

    public function getBending() {
        return $this->bending;
    }

    public function getSurfaceTreatment() {
        return $this->surfaceTreatment;
    }

    public function getMilling() {
        return $this->milling;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    // Setter-Methoden

    public function setBlechart($blechart) {
        $this->blechart = $blechart;
    }

    public function setMaterial($material) {
        $this->material = $material;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function setLength($length) {
        $this->length = $length;
    }

    public function setThickness($thickness) {
        $this->thickness = $thickness;
    }


    public function setStamping($stamping) {
        $this->stamping = $stamping;
    }

    public function setBending($bending) {
        $this->bending = $bending;
    }

    public function setSurfaceTreatment($surfaceTreatment) {
        $this->surfaceTreatment = $surfaceTreatment;
    }

    public function setMilling($milling) {
        $this->milling = $milling;
    }

    public function setQuantity($quantity) {
        $this->quantity = $quantity;
    }

    public function setCutting($cutting) {
        $this->cutting = $cutting;
    }
}
?>