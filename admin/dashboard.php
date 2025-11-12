<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html");
    exit();
}

include '../php/connect.php';

// Handle report generation
if (isset($_GET['generate_report'])) {
    $report_type = $_GET['report_type'];
    $format = $_GET['format'];
    
    switch($report_type) {
        case 'users':
            generateUsersReport($format, $db);
            break;
        case 'products':
            generateProductsReport($format, $db);
            break;
        case 'orders':
            generateOrdersReport($format, $db);
            break;
        case 'revenue':
            generateRevenueReport($format, $db);
            break;
    }
    exit();
}

// Report generation functions
function generateUsersReport($format, $db) {
    $users = $db->query("SELECT id, name, email, accountType FROM users ORDER BY id")->fetch_all(MYSQLI_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Name', 'Email', 'Account Type']);
        
        foreach ($users as $user) {
            fputcsv($output, $user);
        }
        fclose($output);
    } else {
        header('Content-Type: text/html');
        echo generateHTMLReport('Users Report', $users, ['ID', 'Name', 'Email', 'Account Type']);
    }
}

function generateProductsReport($format, $db) {
    $products = $db->query("
        SELECT p.id, p.name, p.price, p.category, p.minOrder, s.name as store_name 
        FROM products p 
        JOIN stores s ON p.storeID = s.storeID 
        ORDER BY p.id
    ")->fetch_all(MYSQLI_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Product Name', 'Price', 'Category', 'Min Order', 'Store']);
        
        foreach ($products as $product) {
            fputcsv($output, $product);
        }
        fclose($output);
    } else {
        header('Content-Type: text/html');
        echo generateHTMLReport('Products Report', $products, ['ID', 'Product Name', 'Price', 'Category', 'Min Order', 'Store']);
    }
}

function generateOrdersReport($format, $db) {
    $orders = $db->query("
        SELECT o.order_id, o.customer_name, o.total_amount, o.status, o.order_date, 
               COUNT(oi.order_item_id) as item_count
        FROM orders o 
        LEFT JOIN order_items oi ON o.order_id = oi.order_id 
        GROUP BY o.order_id 
        ORDER BY o.order_date DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="orders_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order ID', 'Customer', 'Total Amount', 'Status', 'Order Date', 'Item Count']);
        
        foreach ($orders as $order) {
            fputcsv($output, $order);
        }
        fclose($output);
    } else {
        header('Content-Type: text/html');
        echo generateHTMLReport('Orders Report', $orders, ['Order ID', 'Customer', 'Total Amount', 'Status', 'Order Date', 'Item Count']);
    }
}

function generateRevenueReport($format, $db) {
    $revenue = $db->query("
        SELECT DATE_FORMAT(order_date, '%Y-%m') as month, 
               COUNT(*) as order_count,
               SUM(total_amount) as revenue,
               AVG(total_amount) as avg_order_value
        FROM orders 
        WHERE status = 'paid' 
        GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
        ORDER BY month DESC
    ")->fetch_all(MYSQLI_ASSOC);
    
    if ($format === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="revenue_report_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Month', 'Order Count', 'Revenue', 'Average Order Value']);
        
        foreach ($revenue as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    } else {
        header('Content-Type: text/html');
        echo generateHTMLReport('Revenue Report', $revenue, ['Month', 'Order Count', 'Revenue', 'Average Order Value']);
    }
}

function generateHTMLReport($title, $data, $headers) {
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <title>' . $title . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .report-header { text-align: center; margin-bottom: 30px; }
            .report-footer { margin-top: 30px; text-align: right; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="report-header">
            <h1>' . $title . '</h1>
            <p>Generated on: ' . date('F j, Y g:i A') . '</p>
        </div>
        <table>
            <thead>
                <tr>';
    
    foreach ($headers as $header) {
        $html .= '<th>' . $header . '</th>';
    }
    
    $html .= '</tr>
            </thead>
            <tbody>';
    
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $value) {
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody>
        </table>
        <div class="report-footer">
            <p>Report generated by PriMeri Admin System</p>
        </div>
    </body>
    </html>';
    
    return $html;
}

// Handle AJAX request for user details
if (isset($_GET['action']) && $_GET['action'] == 'get_user_details') {
    $user_id = intval($_GET['user_id']);
    
    // Get user basic info
    $user_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();
    
    // Get user orders if they're a buyer
    $orders = [];
    if ($user['accountType'] == 'buyer') {
        $order_stmt = $db->prepare("
            SELECT o.order_id, o.order_date, o.total_amount, o.status, 
                   COUNT(oi.order_item_id) as item_count
            FROM orders o 
            LEFT JOIN order_items oi ON o.order_id = oi.order_id 
            WHERE o.business_owner_id = ? 
            GROUP BY o.order_id 
            ORDER BY o.order_date DESC 
            LIMIT 10
        ");
        $order_stmt->bind_param("i", $user_id);
        $order_stmt->execute();
        $orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get user stores if they're a manufacturer
    $stores = [];
    if ($user['accountType'] == 'manufacturer') {
        $store_stmt = $db->prepare("
            SELECT s.storeID, s.name, c.name as category_name, 
                   COUNT(p.id) as product_count
            FROM stores s 
            LEFT JOIN products p ON s.storeID = p.storeID 
            LEFT JOIN categories c ON s.categoryID = c.category_id
            WHERE s.manufacturerID = ?
            GROUP BY s.storeID
        ");
        $store_stmt->bind_param("i", $user_id);
        $store_stmt->execute();
        $stores = $store_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Get user addresses
    $address_stmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ?");
    $address_stmt->bind_param("i", $user_id);
    $address_stmt->execute();
    $addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Calculate user statistics
    $total_orders = $db->query("SELECT COUNT(*) FROM orders WHERE business_owner_id = $user_id")->fetch_row()[0];
    $total_spent = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE business_owner_id = $user_id AND status = 'paid'")->fetch_row()[0];
    $last_login = "Not tracked";
    
    echo json_encode([
        'user' => $user,
        'orders' => $orders,
        'stores' => $stores,
        'addresses' => $addresses,
        'stats' => [
            'total_orders' => $total_orders,
            'total_spent' => $total_spent,
            'last_login' => $last_login
        ]
    ]);
    exit();
}

// Get statistics using MySQLi
$total_products = $db->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$total_stores = $db->query("SELECT COUNT(*) FROM stores")->fetch_row()[0];
$total_users = $db->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];

// Revenue calculations including pending orders
$total_revenue_result = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'paid'");
$total_revenue = $total_revenue_result ? $total_revenue_result->fetch_row()[0] : 0;

$pending_revenue_result = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'pending'");
$pending_revenue = $pending_revenue_result ? $pending_revenue_result->fetch_row()[0] : 0;

$pending_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetch_row()[0];
$paid_orders = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'paid'")->fetch_row()[0];

// Additional data for charts
$category_dist = $db->query("SELECT category, COUNT(*) as count FROM products GROUP BY category")->fetch_all(MYSQLI_ASSOC);
$order_status = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$user_types = $db->query("SELECT accountType, COUNT(*) as count FROM users GROUP BY accountType")->fetch_all(MYSQLI_ASSOC);
$monthly_revenue = $db->query("
    SELECT DATE_FORMAT(order_date, '%Y-%m') as month, 
           SUM(total_amount) as revenue 
    FROM orders 
    WHERE status = 'paid' 
    GROUP BY DATE_FORMAT(order_date, '%Y-%m') 
    ORDER BY month DESC 
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// Get all users for management
$all_users = $db->query("SELECT id, name, email, accountType FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        $db->query("DELETE FROM users WHERE id = $user_id");
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .nav-tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .nav-tab {
            padding: 12px 24px;
            background: rgba(255, 62, 62, 0.1);
            border: none;
            border-radius: 5px;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .nav-tab:hover {
            background: rgba(255, 62, 62, 0.2);
        }
        
        .nav-tab.active {
            background: var(--primary-color);
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
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
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .chart-card h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .chart-container {
            background: rgba(40, 40, 40, 0.8);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
            height: 300px;
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
        
        .action-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: var(--primary-dark);
        }
        
        .action-btn.delete {
            background: #dc3545;
        }
        
        .action-btn.delete:hover {
            background: #c82333;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
        }
        
        .modal-content {
            background: var(--bg-dark);
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            border: 2px solid var(--primary-color);
        }
        
        .close-btn {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: var(--primary-color);
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
        
        .user-details-section {
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(40, 40, 40, 0.6);
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
        
        .user-details-section h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #aaa;
            min-width: 150px;
        }
        
        .detail-value {
            color: var(--text-light);
        }
        
        .orders-table, .stores-table, .addresses-table {
            width: 100%;
            margin-top: 10px;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .loading {
            text-align: center;
            color: var(--primary-color);
            padding: 20px;
        }
        
        .report-generation {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }
        
        .report-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .report-option {
            background: rgba(40, 40, 40, 0.8);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .report-option select, .report-option button {
            width: 100%;
            padding: 8px;
            margin-top: 8px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background: rgba(0, 0, 0, 0.5);
            color: var(--text-light);
        }
        
        .report-option button {
            background: var(--primary-color);
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .report-option button:hover {
            background: var(--primary-dark);
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .quick-stat {
            background: rgba(255, 62, 62, 0.1);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 3px solid var(--primary-color);
        }
        
        .quick-stat .number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .quick-stat .label {
            font-size: 12px;
            color: #aaa;
            text-transform: uppercase;
        }
        
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .report-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .nav-tabs {
                flex-direction: column;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                min-width: auto;
                margin-bottom: 5px;
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
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchTab('overview')">Overview</button>
            <button class="nav-tab" onclick="switchTab('products')">Products</button>
            <button class="nav-tab" onclick="switchTab('manufacturers')">Manufacturers</button>
            <button class="nav-tab" onclick="switchTab('users')">User Management</button>
            <button class="nav-tab" onclick="switchTab('orders')">Orders & Revenue</button>
            <button class="nav-tab" onclick="switchTab('reports')">Reports</button>
        </div>

        <!-- Overview Tab -->
        <div id="overview" class="tab-content active">
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
                    <h3>Confirmed Revenue</h3>
                    <div class="stat-number">KSh <?php echo number_format($total_revenue, 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Revenue</h3>
                    <div class="stat-number">KSh <?php echo number_format($pending_revenue, 2); ?></div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3>Products by Category</h3>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Order Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>User Types Distribution</h3>
                    <div class="chart-container">
                        <canvas id="userTypeChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Monthly Revenue (Last 6 Months)</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content">
            <div class="reports-section">
                <h2 class="section-title">Products Analysis</h2>
                <div class="report-grid">
                    <div class="report-card">
                        <h4>Top 10 Products by Price</h4>
                        <?php
                        $result = $db->query("
                            SELECT p.name, p.price, s.name as store_name, p.category 
                            FROM products p 
                            JOIN stores s ON p.storeID = s.storeID 
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
        </div>

        <!-- Manufacturers Tab -->
        <div id="manufacturers" class="tab-content">
            <div class="reports-section">
                <h2 class="section-title">Manufacturers Analysis</h2>
                <div class="report-grid">
                    <div class="report-card">
                        <h4>Manufacturers by Category</h4>
                        <?php
                        $result = $db->query("
                            SELECT c.name as category_name, COUNT(*) as count 
                            FROM stores s 
                            JOIN categories c ON s.categoryID = c.category_id
                            GROUP BY c.name 
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
                                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                    <td><?php echo $category['count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="report-card">
                        <h4>Products per Manufacturer</h4>
                        <?php
                        $result = $db->query("
                            SELECT s.name, COUNT(p.id) as product_count 
                            FROM stores s 
                            LEFT JOIN products p ON s.storeID = p.storeID 
                            GROUP BY s.storeID, s.name 
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
                </div>
            </div>
        </div>

        <!-- User Management Tab -->
        <div id="users" class="tab-content">
            <div class="reports-section">
                <h2 class="section-title">User Management</h2>
                <div class="report-card">
                    <h4>All Registered Users</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Account Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower($user['accountType']); ?>">
                                        <?php echo ucfirst($user['accountType']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                                        View Details
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="action-btn delete">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orders & Revenue Tab -->
        <div id="orders" class="tab-content">
            <div class="reports-section">
                <h2 class="section-title">Orders & Revenue Analysis</h2>
                <div class="report-grid">
                    <div class="report-card">
                        <h4>Revenue Summary</h4>
                        <table>
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Order Count</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="status-paid">Paid</td>
                                    <td><?php echo number_format($paid_orders); ?></td>
                                    <td>KSh <?php echo number_format($total_revenue, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="status-pending">Pending</td>
                                    <td><?php echo number_format($pending_orders); ?></td>
                                    <td>KSh <?php echo number_format($pending_revenue, 2); ?></td>
                                </tr>
                                <tr style="background: rgba(255,62,62,0.1); font-weight: bold;">
                                    <td>Total Potential</td>
                                    <td><?php echo number_format($total_orders); ?></td>
                                    <td>KSh <?php echo number_format($total_revenue + $pending_revenue, 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div class="report-generation">
                <h2 class="section-title">Report Generation</h2>
                <p>Generate comprehensive reports in CSV or HTML format for analysis and record-keeping.</p>
                
                <div class="report-options">
                    <div class="report-option">
                        <h4>Users Report</h4>
                        <p>All registered users with account details</p>
                        <select id="users_format">
                            <option value="csv">CSV Format</option>
                            <option value="html">HTML Format</option>
                        </select>
                        <button onclick="generateReport('users')">Generate Users Report</button>
                    </div>
                    
                    <div class="report-option">
                        <h4>Products Report</h4>
                        <p>All products with pricing and store information</p>
                        <select id="products_format">
                            <option value="csv">CSV Format</option>
                            <option value="html">HTML Format</option>
                        </select>
                        <button onclick="generateReport('products')">Generate Products Report</button>
                    </div>
                    
                    <div class="report-option">
                        <h4>Orders Report</h4>
                        <p>Complete order history with customer details</p>
                        <select id="orders_format">
                            <option value="csv">CSV Format</option>
                            <option value="html">HTML Format</option>
                        </select>
                        <button onclick="generateReport('orders')">Generate Orders Report</button>
                    </div>
                    
                    <div class="report-option">
                        <h4>Revenue Report</h4>
                        <p>Monthly revenue analysis and trends</p>
                        <select id="revenue_format">
                            <option value="csv">CSV Format</option>
                            <option value="html">HTML Format</option>
                        </select>
                        <button onclick="generateReport('revenue')">Generate Revenue Report</button>
                    </div>
                </div>
                
                <div class="quick-stats">
                    <div class="quick-stat">
                        <div class="number"><?php echo number_format($total_users); ?></div>
                        <div class="label">Total Users</div>
                    </div>
                    <div class="quick-stat">
                        <div class="number"><?php echo number_format($total_products); ?></div>
                        <div class="label">Total Products</div>
                    </div>
                    <div class="quick-stat">
                        <div class="number"><?php echo number_format($total_orders); ?></div>
                        <div class="label">Total Orders</div>
                    </div>
                    <div class="quick-stat">
                        <div class="number">KSh <?php echo number_format($total_revenue); ?></div>
                        <div class="label">Total Revenue</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3>User Details</h3>
            <div id="userDetails" class="loading">
                Loading user information...
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.nav-tab').forEach(button => {
                button.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Modal functions
        function viewUserDetails(userId) {
            document.getElementById('userDetails').innerHTML = '<div class="loading">Loading user information...</div>';
            document.getElementById('userModal').style.display = 'block';
            
            // Fetch user details via AJAX
            fetch(`?action=get_user_details&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    displayUserDetails(data);
                })
                .catch(error => {
                    document.getElementById('userDetails').innerHTML = '<div class="no-data">Error loading user details. Please try again.</div>';
                    console.error('Error:', error);
                });
        }

        function displayUserDetails(data) {
            const user = data.user;
            const stats = data.stats;
            const orders = data.orders;
            const stores = data.stores;
            const addresses = data.addresses;
            
            let html = `
                <div class="user-details-section">
                    <h4>Basic Information</h4>
                    <div class="detail-row">
                        <div class="detail-label">User ID:</div>
                        <div class="detail-value">${user.id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Full Name:</div>
                        <div class="detail-value">${escapeHtml(user.name)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value">${escapeHtml(user.email)}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Account Type:</div>
                        <div class="detail-value">${user.accountType}</div>
                    </div>
                </div>
                
                <div class="user-details-section">
                    <h4>Account Statistics</h4>
                    <div class="detail-row">
                        <div class="detail-label">Total Orders:</div>
                        <div class="detail-value">${stats.total_orders}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Total Spent:</div>
                        <div class="detail-value">KSh ${Number(stats.total_spent).toLocaleString('en-KE', {minimumFractionDigits: 2})}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Last Activity:</div>
                        <div class="detail-value">${stats.last_login}</div>
                    </div>
                </div>
            `;
            
            // Display addresses if available
            if (addresses.length > 0) {
                html += `
                    <div class="user-details-section">
                        <h4>Addresses (${addresses.length})</h4>
                        <table class="addresses-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Street</th>
                                    <th>City</th>
                                    <th>Country</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                addresses.forEach(address => {
                    html += `
                        <tr>
                            <td>${address.address_type}</td>
                            <td>${escapeHtml(address.street_address)}</td>
                            <td>${escapeHtml(address.city)}</td>
                            <td>${escapeHtml(address.country)}</td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `
                    <div class="user-details-section">
                        <h4>Addresses</h4>
                        <div class="no-data">No addresses found for this user</div>
                    </div>
                `;
            }
            
            // Display orders for buyers
            if (user.accountType === 'buyer' && orders.length > 0) {
                html += `
                    <div class="user-details-section">
                        <h4>Recent Orders (${orders.length})</h4>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Items</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                orders.forEach(order => {
                    html += `
                        <tr>
                            <td>#${order.order_id}</td>
                            <td>${new Date(order.order_date).toLocaleDateString()}</td>
                            <td>KSh ${Number(order.total_amount).toLocaleString('en-KE', {minimumFractionDigits: 2})}</td>
                            <td>${order.item_count}</td>
                            <td class="status-${order.status}">${order.status.replace('_', ' ')}</td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else if (user.accountType === 'buyer') {
                html += `
                    <div class="user-details-section">
                        <h4>Recent Orders</h4>
                        <div class="no-data">No orders found for this user</div>
                    </div>
                `;
            }
            
            // Display stores for manufacturers
            if (user.accountType === 'manufacturer' && stores.length > 0) {
                html += `
                    <div class="user-details-section">
                        <h4>Manufacturer Stores (${stores.length})</h4>
                        <table class="stores-table">
                            <thead>
                                <tr>
                                    <th>Store Name</th>
                                    <th>Category</th>
                                    <th>Products</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                stores.forEach(store => {
                    html += `
                        <tr>
                            <td>${escapeHtml(store.name)}</td>
                            <td>${store.category_name}</td>
                            <td>${store.product_count}</td>
                        </tr>
                    `;
                });
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else if (user.accountType === 'manufacturer') {
                html += `
                    <div class="user-details-section">
                        <h4>Manufacturer Stores</h4>
                        <div class="no-data">No stores found for this manufacturer</div>
                    </div>
                `;
            }
            
            document.getElementById('userDetails').innerHTML = html;
        }

        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }

        // Utility function to escape HTML
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Report generation function
        function generateReport(reportType) {
            const format = document.getElementById(reportType + '_format').value;
            const url = `?generate_report=1&report_type=${reportType}&format=${format}`;
            
            if (format === 'csv') {
                // For CSV, trigger download
                window.location.href = url;
            } else {
                // For HTML, open in new tab
                window.open(url, '_blank');
            }
        }

        // Chart colors
        const chartColors = [
            '#ff3e3e', '#ff6b6b', '#ff9999', '#ffc7c7',
            '#ff8a3e', '#ffa86b', '#ffc699', '#ffe4c7',
            '#3e72ff', '#6b8eff', '#99aaff', '#c7d4ff'
        ];

        // Products by Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function($cat) { return "'" . $cat['category'] . "'"; }, $category_dist)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($cat) { return $cat['count']; }, $category_dist)); ?>],
                    backgroundColor: chartColors,
                    borderColor: '#1a1a1a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#f0f0f0',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });

        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const orderStatusChart = new Chart(orderStatusCtx, {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(function($status) { return "'" . ucfirst(str_replace('_', ' ', $status['status'])) . "'"; }, $order_status)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($status) { return $status['count']; }, $order_status)); ?>],
                    backgroundColor: ['#ff3e3e', '#28a745', '#17a2b8', '#ffc107', '#6c757d', '#007bff'],
                    borderColor: '#1a1a1a',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#f0f0f0',
                            font: { size: 11 }
                        }
                    }
                }
            }
        });

        // User Type Chart
        const userTypeCtx = document.getElementById('userTypeChart').getContext('2d');
        const userTypeChart = new Chart(userTypeCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($type) { return "'" . ucfirst($type['accountType']) . "'"; }, $user_types)); ?>],
                datasets: [{
                    label: 'Number of Users',
                    data: [<?php echo implode(',', array_map(function($type) { return $type['count']; }, $user_types)); ?>],
                    backgroundColor: '#ff3e3e',
                    borderColor: '#cc0000',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#f0f0f0'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#f0f0f0' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    x: {
                        ticks: { color: '#f0f0f0' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($rev) { return "'" . $rev['month'] . "'"; }, array_reverse($monthly_revenue))); ?>],
                datasets: [{
                    label: 'Monthly Revenue (KSh)',
                    data: [<?php echo implode(',', array_map(function($rev) { return $rev['revenue']; }, array_reverse($monthly_revenue))); ?>],
                    backgroundColor: 'rgba(255, 62, 62, 0.1)',
                    borderColor: '#ff3e3e',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: '#f0f0f0'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            color: '#f0f0f0',
                            callback: function(value) {
                                return 'KSh ' + value.toLocaleString();
                            }
                        },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    },
                    x: {
                        ticks: { color: '#f0f0f0' },
                        grid: { color: 'rgba(255,255,255,0.1)' }
                    }
                }
            }
        });
    </script>
</body>
</html>