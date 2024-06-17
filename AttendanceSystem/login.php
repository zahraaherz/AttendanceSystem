<?php 
require "connection.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $email = $_POST["email"] ?? ''; 
    $passcode = $_POST["passcode"] ?? '';

    $email = trim($email);
    $passcode = trim($passcode);

    try {
        $sql = "SELECT * FROM employees WHERE email = :email AND passcode = :passcode LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':passcode', $passcode); 
        $stmt->execute();
        
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            // echo "User found: " . $employee["employee_id"];

            $_SESSION["USER_ID"] = $employee["employee_id"];
            header("Location: registerAttendance.php");
            die;
        } else {
            $error = "Invalid User Or Password";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Mini Attendance System</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 300vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
          
        }

        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 400px;
            max-width: 100%;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container input[type="text"],
        .container input[type="password"],
        .container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .container button {
            background-color: #ae5c5c;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .container button:hover {
            background-color: #45a049;
        }

        footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php require_once "header.php"; ?>

    <div class="container">
        <h2>Login</h2>
       
        <form method="post">
            <input type="text" name="email" placeholder="Email" required><br>
            <input type="password" name="passcode" placeholder="Password" required><br>
            <?php 
            if(!empty($error)){
                echo "<h5 style='color: #9D2C13 ;'>".$error."</h5><br>";
            }
            ?>
            <button type="submit">Login</button>
        </form>
    </div>

    <footer>
        <?php require_once "footer.php"; ?>
    </footer>
</body>
</html>
