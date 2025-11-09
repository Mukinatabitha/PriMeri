<?php
// cart-sync.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['cart'])) {
        $_SESSION['cart'] = $input['cart'];
        echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No cart data provided']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>