<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);
if ($data) {
    $_SESSION['cart'] = $data;
    echo "Cart restored";
} else {
    echo "No data received";
}
error_log("Restore cart attempt: " . print_r($data, true));