<?php
header('Access-Control-Allow-Origin: *'); // Erlaubt Anfragen von allen Ursprüngen
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE'); // Erlaubt die Methoden POST, GET, OPTIONS und DELETE
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Erlaubt die Header Content-Type und Authorization
require_once 'router.php';
?>