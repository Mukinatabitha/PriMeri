<?php
// Set headers FIRST before any output
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Check if it's POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Use POST.'
    ]);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
parse_str($input, $postData);

// Log received data for debugging
error_log("Received POST data: " . print_r($postData, true));

// Check if required fields are present
$required = ['firstName', 'lastName', 'email', 'mpesaPhone', 'address', 'city', 'country'];
$missing = [];

foreach ($required as $field) {
    if (empty($postData[$field])) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing)
    ]);
    exit;
}

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'primeri';
    $username = 'root';
    $password = '';
    $port = 3308;

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Generate order ID
    $order_id = rand(10000, 99999);
    
    // Calculate total amount from cart
    $cart_data = json_decode($postData['cart'] ?? '[]', true);
    $total_amount = 0;
    
    if (!empty($cart_data)) {
        foreach ($cart_data as $item) {
            $price = floatval($item['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $total_amount += $price * $quantity;
        }
    }
    
    // Set default values for business_owner_id and manufacturer_id
    $business_owner_id = 1;
    $manufacturer_id = 1;
    
    // Prepare shipping address
    $customer_name = $postData['firstName'] . ' ' . $postData['lastName'];
    $shipping_address = $postData['address'] . ', ' . $postData['city'] . ', ' . $postData['country'];
    if (!empty($postData['postalCode'])) {
        $shipping_address .= ' - ' . $postData['postalCode'];
    }
    
    // Save order to orders table
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (order_id, business_owner_id, manufacturer_id, order_date, deadline, status, total_amount, shipping_address, customer_name, customer_email, customer_phone) 
        VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'paid', ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $order_id,
        $business_owner_id,
        $manufacturer_id,
        $total_amount,
        $shipping_address,
        $customer_name,
        $postData['email'],
        $postData['mpesaPhone']
    ]);
    
    $order_db_id = $pdo->lastInsertId();
    error_log("Order inserted with ID: " . $order_db_id);
    
    // Save order items to order_items table
    if (!empty($cart_data)) {
        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, item_name, description, quantity, unit_price) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($cart_data as $item) {
            $item_stmt->execute([
                $order_db_id,
                $item['id'] ?? 0,
                $item['name'] ?? 'Unknown Product',
                $item['description'] ?? 'No description available',
                $item['quantity'] ?? 1,
                $item['price'] ?? 0
            ]);
        }
        error_log("Order items inserted: " . count($cart_data) . " items");
    }
    
    // Generate payment reference and save payment record
    $payment_reference = 'MPESA' . time() . rand(10000, 99999);
    
    // Save payment to payments table
    $payment_stmt = $pdo->prepare("
        INSERT INTO payments 
        (order_id, amount, payment_date, payment_method, status, payment_reference) 
        VALUES (?, ?, NOW(), 'M-Pesa', 'completed', ?)
    ");
    
    $payment_stmt->execute([
        $order_id,
        $total_amount,
        $payment_reference
    ]);
    
    $payment_id = $pdo->lastInsertId();
    error_log("Payment inserted with ID: " . $payment_id);
    
    // Try to include and send email (but don't let email failure break the order)
    $email_sent = false;
    $email_error = '';
    
    try {
        if (file_exists('sendMail.php')) {
            include 'sendMail.php';
            if (class_exists('Mail')) {
                $mail = new Mail(SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_ENCRYPTION);
                $mail->paymentConfirmationEmail(
                    $postData['email'],
                    $customer_name,
                    $order_id,
                    $total_amount
                );
                $email_sent = true;
                error_log("Email sent successfully to: " . $postData['email']);
            }
        }
    } catch (Exception $emailException) {
        $email_error = $emailException->getMessage();
        error_log("Email error: " . $email_error);
    }
    
    // Return success response
    $response = [
        'success' => true,
        'order_id' => $order_id,
        'payment_id' => $payment_id,
        'payment_reference' => $payment_reference,
        'total_amount' => $total_amount,
        'email_sent' => $email_sent,
        'message' => 'Order and payment processed successfully!' . ($email_sent ? '' : ' (Email notification failed)')
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_info' => $e->errorInfo
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>