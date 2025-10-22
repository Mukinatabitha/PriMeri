<?php
require_once __DIR__ . '/php/connect.php';

$dbName = null;
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    // Get current database name
    $dbResult = $conn->query("SELECT DATABASE() AS dbname");
    if ($dbResult) {
        $row = $dbResult->fetch_assoc();
        $dbName = $row['dbname'] ?? null;
        $dbResult->free();
    }
    // Get list of tables
    $tables = [];
    $res = $conn->query("SHOW TABLES");
    if ($res) {
        while ($r = $res->fetch_row()) {
            $tables[] = $r[0];
        }
        $res->free();
    }
} else {
    $tables = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PriMeri - Index</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        h1 { margin-bottom: 8px; }
        .status { margin: 12px 0; padding: 12px; border-radius: 6px; background:#f5f5f5; }
        ul { margin: 8px 0 16px 20px; }
        .empty { color:#666; }
    </style>
</head>
<body>
    <h1>PriMeri</h1>

    <?php if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error): ?>
        <div class="status">
            <strong>Database connection:</strong> Successful<br>
            <strong>Server info:</strong> <?php echo htmlspecialchars($conn->server_info ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Host info:</strong> <?php echo htmlspecialchars($conn->host_info ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?><br>
            <strong>Database:</strong> <?php echo htmlspecialchars($dbName ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
        </div>

        <?php if (!empty($tables)): ?>
            <h2>Tables</h2>
            <ul>
                <?php foreach ($tables as $table): ?>
                    <li><?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">No tables found in the database.</p>
        <?php endif; ?>

    <?php else: ?>
        <div class="status">
            <strong>Database connection:</strong> Failed
        </div>
    <?php endif; ?>
</body>
</html>