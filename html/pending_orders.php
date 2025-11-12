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
$message = "";

// Handle marking order as completed
if (isset($_GET['complete_order'])) {
    $order_id = intval($_GET['complete_order']);

    $update_stmt = $db->prepare("
        UPDATE orders o
        INNER JOIN stores s ON o.manufacturer_id = s.manufacturerID
        SET o.status = 'completed'
        WHERE o.order_id = ? AND s.manufacturerID = ?
    ");
    $update_stmt->bind_param("ii", $order_id, $user_id);
    if ($update_stmt->execute()) {
        $message = "Order #$order_id marked as completed.";
    } else {
        $message = "Failed to update order.";
    }
    $update_stmt->close();
}

// Fetch pending orders for this manufacturer
$order_query = $db->prepare("
    SELECT o.order_id, o.order_date, o.deadline, o.status, o.total_amount, 
           o.shipping_address, o.customer_name, o.customer_email, o.customer_phone,
           s.name AS store_name, p.name AS product_name
    FROM orders o
    INNER JOIN stores s ON o.manufacturer_id = s.manufacturerID
    INNER JOIN products p ON o.order_id = p.id
    WHERE s.manufacturerID = ? AND o.status = 'pending'
    ORDER BY o.order_date ASC
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
  <title>PriMeri - Pending Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/pending_orders.css">
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
         <a href="completed_orders.php" class="btn bg-primary-custom text-white px-3 py-2 rounded-3 btn-hover-primary">Completed Orders</a>
        <a href="../php/logout.php" class="btn bg-primary-custom text-white px-3 py-2 rounded-3 btn-hover-primary">Log Out</a>
      </div>
    </nav>
  </header>

  <!-- Main -->
  <main class="container-xl py-5">
    <h1 class="fw-bolder text-dark text-center mb-4">Pending Orders</h1>
    <p class="text-center text-muted mb-5">Here are your customers’ pending orders awaiting completion.</p>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
      <div class="text-center py-5">
        <h3 class="text-muted">No Pending Orders</h3>
        <p class="text-muted">All your orders are completed or no orders have been placed yet.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive shadow-lg rounded-4">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-primary-custom text-white">
            <tr>
              <th scope="col">Order ID</th>
              <th scope="col">Store</th>
              <th scope="col">Item</th>
              <th scope="col">Quantity</th>
              <th scope="col">Customer Description</th>
              <th scope="col">Customer Name</th>
              <th scope="col">Customer Email</th>
              <th scope="col">Customer Phone</th>
              <th scope="col">Shipping Address</th>
              <th scope="col">Order Date</th>
              <th scope="col">Deadline</th>
              <th scope="col">Total Amount</th>
              <th scope="col" class="text-center">Action</th>
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
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['order_date']))); ?></td>
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['deadline']))); ?></td>
                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                <td class="text-center">
                  <a href="?complete_order=<?php echo $order['order_id']; ?>" 
                     class="btn btn-sm bg-secondary-custom text-white rounded-3"
                     onclick="return confirm('Mark order #<?php echo $order['order_id']; ?> as completed?');">
                    Mark as Completed
                  </a>
                </td>
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
