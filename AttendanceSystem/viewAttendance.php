<?php
require "connection.php";

$search_query = ""; 
$search_email = ""; 

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['email'])) {
    $search_email = $_POST['email'];
    if (!empty($search_email)) {
        $search_query = " WHERE e.email LIKE ?";
    }
}

try {
    $sql = "SELECT e.name, e.email, ar.attendance_time, ar.departure_time, ar.attendance_date 
            FROM employees e
            INNER JOIN attendance_records ar ON e.employee_id = ar.employee_id 
            $search_query";

    $stmt = $conn->prepare($sql);

    if (!empty($search_email)) {
        $search_param = "%$search_email%";
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
    }

    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Record - Mini Attendance System</title>
    <style>
     
     * {
         padding: 0;
         margin: 0;
         box-sizing: border-box;
     }
     body {
         font-family: Arial, sans-serif;
         background-color: #f2f2f2;
         margin: 0;
     }
     header {
         background-color: #ae5c5c;
         color: white;
         padding: 10px 20px;
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
         margin-bottom: 20px; 
     }
     header a {
         color: white;
         text-decoration: none;
         padding: 10px 15px;
         border-radius: 5px;
         transition: background-color 0.3s ease;
     }
     header a:hover {
         background-color: #961f1f; 
     }
     h2 {
         margin-bottom: 10px;
     }
     .container {
         background-color: white;
         border-radius: 5px;
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
         padding: 20px;
         margin-bottom: 20px;
     }
     form {
         margin-bottom: 20px;
     }
     label {
         font-weight: bold;
         margin-right: 10px;
     }
     input[type="text"] {
         padding: 8px;
         margin-right: 10px;
         border: 1px solid #ccc;
         border-radius: 4px;
         box-sizing: border-box;
         font-size: 14px;
         width: 200px;
     }
     input[type="submit"] {
         padding: 8px 16px;
         background-color:  #ae5c5c;
         color: white;
         border: none;
         border-radius: 4px;
         cursor: pointer;
     }
     table {
         width: 100%;
         border-collapse: collapse;
         background-color: white;
         border-radius: 5px;
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
     }
     table th, table td {
         padding: 10px;
         text-align: left;
         border: 1px solid #ddd;
     }
     table th {
         background-color: #f2f2f2;
     }
     h2 {
         margin-bottom: 10px;
         text-align: center; 
     }
 </style>

</head>
<body>
    <?php require_once "header.php"; ?>

    <div class="container">
        <h2>User Information</h2>

        <form method="post" action="">
            <label for="email">Search by Email:</label>
            <input type="text" id="email" name="email" placeholder="Enter email" value="<?php echo htmlspecialchars($search_email); ?>">
            <input type="submit" value="Search">
        </form>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Attendance Time</th>
                    <th>Departure Time</th>
                    <th>Attendance Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($results) > 0) {
                    foreach ($results as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['attendance_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['departure_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['attendance_date']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; color: #A93920 ;' >No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php require_once "footer.php"; ?>
</body>
</html>
