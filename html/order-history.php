<?php
// order-history.php (basic version)
session_start();
include '../php/connect.php';

// For now, show all orders (you can add user authentication later)
$orderQuery = $db->query("
    SELECT o.*, s.name as store_name 
    FROM orders o 
    LEFT JOIN stores s ON o.manufacturer_id = s.id 
    ORDER BY o.order_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - PriMeri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h1>Order History</h1>
        <div class="row">
            <?php while($order = $orderQuery->fetch_assoc()): ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order #<?= $order['order_id'] ?></h5>
                        <p class="card-text">
                            <strong>Store:</strong> <?= htmlspecialchars($order['store_name']) ?><br>
                            <strong>Date:</strong> <?= $order['order_date'] ?><br>
                            <strong>Total:</strong> KSh <?= number_format($order['total_amount'], 2) ?><br>
                            <strong>Status:</strong> 
                            <span class="badge 
                                <?= $order['status'] == 'pending' ? 'bg-warning' : '' ?>
                                <?= $order['status'] == 'confirmed' ? 'bg-primary' : '' ?>
                                <?= $order['status'] == 'in_production' ? 'bg-info' : '' ?>
                                <?= $order['status'] == 'shipped' ? 'bg-success' : '' ?>
                            "><?= ucfirst($order['status']) ?></span>
                        </p>
                        <a href="order-details.php?id=<?= $order['order_id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>