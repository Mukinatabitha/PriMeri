<?php
// order-details.php
include '../php/connect.php'; // your database connection

// Get order_id from URL
if (!isset($_GET['id'])) {
    echo "No order ID provided.";
    exit;
}

$order_id = intval($_GET['id']);

// Fetch order details
$order_sql = "SELECT * FROM orders WHERE order_id = $order_id";
$order_result = $db->query($order_sql);

if ($order_result->num_rows == 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$items_sql = "SELECT * FROM order_items WHERE order_id = $order_id";
$items_result = $db->query($items_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Details #<?= $order_id ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Order Details #<?= $order_id ?></h2>

    <h4>Customer Information</h4>
    <ul>
        <li>Name: <?= htmlspecialchars($order['customer_name']) ?></li>
        <li>Email: <?= htmlspecialchars($order['customer_email']) ?></li>
        <li>Phone: <?= htmlspecialchars($order['customer_phone']) ?></li>
        <li>Shipping Address: <?= htmlspecialchars($order['shipping_address']) ?></li>
    </ul>

    <h4>Order Information</h4>
    <ul>
        <li>Order Date: <?= $order['order_date'] ?></li>
        <li>Deadline: <?= $order['deadline'] ?></li>
        <li>Status: <?= htmlspecialchars($order['status']) ?></li>
        <li>Total Amount: Ksh<?= number_format($order['total_amount'], 2) ?></li>
    </ul>

    <h4>Items</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Item Name</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 1;
            while ($item = $items_result->fetch_assoc()) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                echo "<tr>
                    <td>{$count}</td>
                    <td>".htmlspecialchars($item['item_name'])."</td>
                    <td>".htmlspecialchars($item['description'])."</td>
                    <td>{$item['quantity']}</td>
                    <td>$".number_format($item['unit_price'], 2)."</td>
                    <td>$".number_format($subtotal, 2)."</td>
                </tr>";
                $count++;
            }
            ?>
        </tbody>
    </table>

    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
</body>
</html>
