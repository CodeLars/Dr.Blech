<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE'); // Erlaubt die Methoden POST, GET, OPTIONS und DELETE
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization

require_once 'controllers/CustomerController.php';
require_once 'controllers/OfferController.php';
require_once 'controllers/MaterialController.php';
require_once 'controllers/BlechController.php';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

$customerController = new CustomerController();
$offerController = new OfferController();
$materialController = new MaterialController();
$blechController = new BlechController();

if (strpos($requestUri, '/api/offers') !== false) {
    $offerController->handleRequest($requestMethod);
} elseif (strpos($requestUri, '/api/customers') !== false) {
    $customerController->handleRequest($requestMethod);
} elseif (strpos($requestUri, '/api/materials') !== false) {
    $materialController->handleRequest($requestMethod);
} elseif(strpos($requestUri, '/api/blech') !== false){
    $blechController->handleRequest($requestMethod);
}
else {
    http_response_code(404);
    echo json_encode(['message' => 'Route not found']);
}
?>