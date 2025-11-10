<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

include '../php/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Reports</title>
    <style>
        :root {
            --primary-color: #ff3e3e;
            --primary-dark: #cc0000;
            --bg-dark: #1a1a1a;
            --bg-darker: #0d0d0d;
            --text-light: #f0f0f0;
            --border-color: #444;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--bg-darker), var(--bg-dark));
            color: var(--text-light);
            min-height: 100vh;
        }
        
        .dashboard-header {
            background: rgba(0, 0, 0, 0.9);
            padding: 20px;
            border-bottom: 3px solid var(--primary-color);
            box-shadow: 0 2px 10px rgba(255, 62, 62, 0.2);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-title {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 0 10px rgba(255, 62, 62, 0.5);
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(0, 0, 0, 0.7);
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .stat-card h3 {
            color: #aaa;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-number {
            color: var(--primary-color);
            font-size: 32px;
            font-weight: 700;
        }
        
        .reports-section {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 22px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .report-card {
            background: rgba(40, 40, 40, 0.8);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
        }
        
        .report-card h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: rgba(255, 62, 62, 0.1);
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background: rgba(255, 62, 62, 0.05);
        }
        
        .status-pending { color: var(--warning); }
        .status-paid { color: var(--success); }
        .status-delivered { color: var(--info); }
        
        .chart-container {
            background: rgba(40, 40, 40, 0.8);
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
            border: 1px solid var(--border-color);
        }
        
        .export-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        
        .export-btn:hover {
            background: var(--primary-dark);
            box-shadow: 0 0 10px rgba(255, 62, 62, 0.5);
        }
        
        @media (max-width: 768px) {
            .report-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php
        // Get statistics using MySQLi
        $total_products = $db->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
        $total_stores = $db->query("SELECT COUNT(*) FROM stores")->fetch_row()[0];
        $total_users = $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
        $total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
        $total_revenue_result = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'paid'");
        $total_revenue = $total_revenue_result ? $total_revenue_result->fetch_row()[0] : 0;
        $pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="stat-number"><?php echo number_format($total_products); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Manufacturers</h3>
                <div class="stat-number"><?php echo number_format($total_stores); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-number"><?php echo number_format($total_users); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="stat-number">KSh <?php echo number_format($total_revenue, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="stat-number"><?php echo number_format($pending_orders); ?></div>
            </div>
        </div>

        <!-- Products Reports -->
        <div class="reports-section">
            <h2 class="section-title">Products Analysis</h2>
            <div class="report-grid">
                <!-- Top Products by Price -->
                <div class="report-card">
                    <h4>Top 10 Products by Price</h4>
                    <?php
                    $result = $db->query("
                        SELECT p.name, p.price, s.name as store_name, p.category 
                        FROM products p 
                        JOIN stores s ON p.store_id = s.id 
                        ORDER BY p.price DESC 
                        LIMIT 10
                    ");
                    $top_products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Store</th>
                                <th>Category</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['store_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td>KSh <?php echo number_format($product['price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Products by Category -->
                <div class="report-card">
                    <h4>Products Distribution by Category</h4>
                    <?php
                    $result = $db->query("
                        SELECT category, COUNT(*) as count 
                        FROM products 
                        GROUP BY category 
                        ORDER BY count DESC
                    ");
                    $category_dist = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($category_dist as $category): 
                                $percentage = ($category['count'] / $total_products) * 100;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['category']); ?></td>
                                <td><?php echo $category['count']; ?></td>
                                <td><?php echo number_format($percentage, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Products Price Range Analysis -->
                <div class="report-card">
                    <h4>Price Range Analysis</h4>
                    <?php
                    $result = $db->query("
                        SELECT 
                            COUNT(CASE WHEN price < 100 THEN 1 END) as under_100,
                            COUNT(CASE WHEN price BETWEEN 100 AND 500 THEN 1 END) as 100_500,
                            COUNT(CASE WHEN price BETWEEN 501 AND 1000 THEN 1 END) as 501_1000,
                            COUNT(CASE WHEN price > 1000 THEN 1 END) as over_1000
                        FROM products
                    ");
                    $price_ranges = $result ? $result->fetch_assoc() : ['under_100' => 0, '100_500' => 0, '501_1000' => 0, 'over_1000' => 0];
                    ?>
                    <div class="chart-container">
                        <canvas id="priceRangeChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Minimum Order Requirements -->
                <div class="report-card">
                    <h4>Minimum Order Requirements</h4>
                    <?php
                    $result = $db->query("
                        SELECT name, minOrder, category 
                        FROM products 
                        ORDER BY minOrder DESC 
                        LIMIT 10
                    ");
                    $min_orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Min Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($min_orders as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td><?php echo number_format($product['minOrder']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Manufacturers Reports -->
        <div class="reports-section">
            <h2 class="section-title">Manufacturers Analysis</h2>
            <div class="report-grid">
                <!-- Stores by Category -->
                <div class="report-card">
                    <h4>Manufacturers by Category</h4>
                    <?php
                    $result = $db->query("
                        SELECT category, COUNT(*) as count 
                        FROM stores 
                        GROUP BY category 
                        ORDER BY count DESC
                    ");
                    $store_categories = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($store_categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['category']); ?></td>
                                <td><?php echo $category['count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Top Rated Manufacturers -->
                <div class="report-card">
                    <h4>Top Rated Manufacturers</h4>
                    <?php
                    $result = $db->query("
                        SELECT name, category, rating, reviews 
                        FROM stores 
                        WHERE rating IS NOT NULL 
                        ORDER BY rating DESC, reviews DESC 
                        LIMIT 10
                    ");
                    $top_stores = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Manufacturer</th>
                                <th>Category</th>
                                <th>Rating</th>
                                <th>Reviews</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_stores as $store): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($store['name']); ?></td>
                                <td><?php echo htmlspecialchars($store['category']); ?></td>
                                <td><?php echo number_format($store['rating'], 1); ?> ★</td>
                                <td><?php echo number_format($store['reviews']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Products per Manufacturer -->
                <div class="report-card">
                    <h4>Products per Manufacturer</h4>
                    <?php
                    $result = $db->query("
                        SELECT s.name, COUNT(p.id) as product_count 
                        FROM stores s 
                        LEFT JOIN products p ON s.id = p.store_id 
                        GROUP BY s.id, s.name 
                        ORDER BY product_count DESC 
                        LIMIT 10
                    ");
                    $products_per_store = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Manufacturer</th>
                                <th>Products</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products_per_store as $store): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($store['name']); ?></td>
                                <td><?php echo $store['product_count']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Lead Time Analysis -->
                <div class="report-card">
                    <h4>Manufacturer Lead Times</h4>
                    <?php
                    $result = $db->query("
                        SELECT name, leadTime, minOrder 
                        FROM stores 
                        WHERE leadTime IS NOT NULL 
                        ORDER BY minOrder 
                        LIMIT 10
                    ");
                    $lead_times = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Manufacturer</th>
                                <th>Lead Time</th>
                                <th>Min Order</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lead_times as $store): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($store['name']); ?></td>
                                <td><?php echo htmlspecialchars($store['leadTime']); ?></td>
                                <td><?php echo number_format($store['minOrder']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users/Buyers Reports -->
        <div class="reports-section">
            <h2 class="section-title">Users & Buyers Analysis</h2>
            <div class="report-grid">
                <!-- User Distribution by Account Type -->
                <div class="report-card">
                    <h4>User Distribution by Account Type</h4>
                    <?php
                    $result = $db->query("
                        SELECT accountType, COUNT(*) as count 
                        FROM users 
                        GROUP BY accountType
                    ");
                    $user_types = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Account Type</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($user_types as $type): 
                                $percentage = ($type['count'] / $total_users) * 100;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type['accountType']); ?></td>
                                <td><?php echo $type['count']; ?></td>
                                <td><?php echo number_format($percentage, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent User Registrations -->
                <div class="report-card">
                    <h4>Recent User Registrations</h4>
                    <?php
                    $result = $db->query("
                        SELECT name, email, accountType 
                        FROM users 
                        ORDER BY id DESC 
                        LIMIT 10
                    ");
                    $recent_users = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['accountType']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orders Reports -->
        <div class="reports-section">
            <h2 class="section-title">Orders & Revenue Analysis</h2>
            <div class="report-grid">
                <!-- Order Status Distribution -->
                <div class="report-card">
                    <h4>Order Status Distribution</h4>
                    <?php
                    $result = $db->query("
                        SELECT status, COUNT(*) as count 
                        FROM orders 
                        GROUP BY status 
                        ORDER BY count DESC
                    ");
                    $order_status = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($order_status as $status): 
                                $percentage = ($status['count'] / $total_orders) * 100;
                            ?>
                            <tr>
                                <td class="status-<?php echo $status['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $status['status'])); ?>
                                </td>
                                <td><?php echo $status['count']; ?></td>
                                <td><?php echo number_format($percentage, 1); ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Orders -->
                <div class="report-card">
                    <h4>Recent Orders</h4>
                    <?php
                    $result = $db->query("
                        SELECT order_id, customer_name, total_amount, status, order_date 
                        FROM orders 
                        ORDER BY order_date DESC 
                        LIMIT 10
                    ");
                    $recent_orders = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td>KSh <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td class="status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Revenue by Status -->
                <div class="report-card">
                    <h4>Revenue by Order Status</h4>
                    <?php
                    $result = $db->query("
                        SELECT status, SUM(total_amount) as revenue 
                        FROM orders 
                        GROUP BY status 
                        ORDER BY revenue DESC
                    ");
                    $revenue_by_status = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($revenue_by_status as $revenue): ?>
                            <tr>
                                <td class="status-<?php echo $revenue['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $revenue['status'])); ?>
                                </td>
                                <td>KSh <?php echo number_format($revenue['revenue'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Price Range Chart
        const priceRangeCtx = document.getElementById('priceRangeChart').getContext('2d');
        const priceRangeChart = new Chart(priceRangeCtx, {
            type: 'pie',
            data: {
                labels: ['Under KSh 100', 'KSh 100-500', 'KSh 501-1000', 'Over KSh 1000'],
                datasets: [{
                    data: [
                        <?php echo $price_ranges['under_100']; ?>,
                        <?php echo $price_ranges['100_500']; ?>,
                        <?php echo $price_ranges['501_1000']; ?>,
                        <?php echo $price_ranges['over_1000']; ?>
                    ],
                    backgroundColor: [
                        '#ff3e3e',
                        '#ff6b6b',
                        '#ff9999',
                        '#ffc7c7'
                    ],
                    borderColor: '#1a1a1a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#f0f0f0',
                            font: {
                                size: 12
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Products by Price Range',
                        color: '#f0f0f0',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>