<?php


require_once __DIR__ . '\..\db.php';
class MaterialController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest($method) {
        switch ($method) {
            case 'GET':
                $this->getMaterials();
                break;
            case 'POST':
                $this->createMaterial();
                break;
            default:
                http_response_code(405);
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }

    private function getMaterials() {
        $sql = "SELECT * FROM material";
        $result = $this->db->select($sql);
        echo json_encode($result);
    }

    private function createMaterial() {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $sql = "INSERT INTO material (name, density, cost_per_kg) VALUES (
                '" . $this->db->escape($input['name']) . "',
                '" . $this->db->escape($input['density']) . "',
                '" . $this->db->escape($input['cost_per_kg']) . "'
            )";
            $this->db->execute($sql);
            echo json_encode(['message' => 'Material created successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid data']);
        }
    }
}
?>