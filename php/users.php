<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users</title>
    <link rel="stylesheet" href="../css/user.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Registered Users</h1>
            <p class="subtitle">All PriMeri users in the system, ordered alphabetically by name</p>
        </header>
        
        <?php
        include 'connect.php';
        $sql = "SELECT name, email, accountType FROM users ORDER BY name ASC";
        $result = $db->query($sql);

        if ($result->num_rows > 0) {
            echo '<div class="user-count">Total users: <strong>' . $result->num_rows . '</strong></div>';
            echo '<input type="text" class="search-box" placeholder="Search users..." id="searchInput">';
            echo '<table class="users-table">';
            echo '<thead><tr>';
            echo '<th>Number</th>';
            echo '<th>Name</th>';
            echo '<th>Email</th>';
            echo '<th>Account Type</th>';
            echo '</tr></thead>';
            echo '<tbody>';

            $i = 1;
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td class="id-cell">' . $i . '</td>';
                echo '<td class="name-cell">' . htmlspecialchars($row["name"]) . '</td>';
                echo '<td class="email-cell">' . htmlspecialchars($row["email"]) . '</td>';
                echo '<td class="type-cell">' . htmlspecialchars($row["accountType"]) . '</td>';
                echo '</tr>';
                $i++;
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="no-users">';
            echo '<h3>No users found</h3>';
            echo '<p>There are currently no registered users in the system.</p>';
            echo '</div>';
        }

        $db->closeConnection();
        ?>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.users-table tbody tr');
            
            rows.forEach(row => {
                const name = row.querySelector('.name-cell').textContent.toLowerCase();
                const email = row.querySelector('.email-cell').textContent.toLowerCase();
                const type = row.querySelector('.type-cell').textContent.toLowerCase();
                const id = row.querySelector('.id-cell').textContent.toLowerCase();
                
                if (name.includes(searchText) || email.includes(searchText) || id.includes(searchText) || type.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
