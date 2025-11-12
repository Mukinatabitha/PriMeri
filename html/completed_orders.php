<?php
session_start();
include '../php/connect.php';

// Ensure the user is logged in and is a manufacturer
if (!isset($_SESSION['user_id']) || ($_SESSION['account_type'] ?? '') !== 'manufacturer') {
    header("Location: ../html/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = [];

// Fetch completed orders for this manufacturer
$order_query = $db->prepare("
    SELECT o.order_id, o.order_date, o.deadline, o.status, o.total_amount, 
           o.shipping_address, o.customer_name, o.customer_email, o.customer_phone,
           s.name AS store_name, p.name AS product_name,
           o.order_date
    FROM orders o
    INNER JOIN stores s ON o.manufacturer_id = s.manufacturerID
    INNER JOIN products p ON o.order_id = p.id
    WHERE s.manufacturerID = ? AND o.status = 'completed'
    ORDER BY o.order_date DESC
");
$order_query->bind_param("i", $user_id);
$order_query->execute();
$result = $order_query->get_result();
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$order_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PriMeri - Completed Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/completed_orders.css">
</head>
<body class="bg-light-bg text-dark">

  <!-- Header -->
  <header class="bg-white shadow-sm sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-xl px-4 d-flex justify-content-between align-items-center">
        <a href="manage_store.php" class="navbar-brand fs-4 fw-bold text-primary-custom d-flex align-items-center">
          <img src="../images/logo.jpg" alt="PriMeri Logo" width="48" height="48" class="me-2">
          PriMeri
        </a>
        <a href="../php/logout.php" class="btn bg-primary-custom text-white px-3 py-2 rounded-3 btn-hover-primary">Log Out</a>
      </div>
    </nav>
  </header>

  <!-- Main -->
  <main class="container-xl py-5">
    <h1 class="fw-bolder text-dark text-center mb-4">Completed Orders</h1>
    <p class="text-center text-muted mb-5">All fulfilled customer orders are listed below for your records.</p>

    <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <h3 class="text-muted">No Completed Orders</h3>
        <p class="text-muted">You haven’t completed any orders yet.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive shadow-lg rounded-4">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-secondary-custom text-white">
            <tr>
              <th>Order ID</th>
              <th>Store</th>
              <th>Item</th>
              <th>Quantity</th>
              <th>Customer Description</th>
              <th>Customer Name</th>
              <th>Customer Email</th>
              <th>Customer Phone</th>
              <th>Shipping Address</th>
              <th>Date Completed</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?php echo htmlspecialchars($order['order_id']); ?></td>
                <td><?php echo htmlspecialchars($order['store_name']); ?></td>
                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_description'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_phone']); ?></td>
                <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['completion_date'] ?? $order['order_date']))); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

  <footer class="bg-dark-bg-custom text-white mt-5">
    <div class="container-xl px-4 py-4 text-center">
      <p class="mb-0">&copy; 2025 PriMeri.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
