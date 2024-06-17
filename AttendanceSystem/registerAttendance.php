<?php 
require "connection.php";

$attendedToday = false; 

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['timeField'])) {
        $timeField = $_POST['timeField'];
        $attendance_date = date('Y-m-d', strtotime('+1 days')); 
        $employee_id = $_SESSION["USER_ID"];

        try {
            $checkSql = "SELECT attendance_time, departure_time FROM attendance_records WHERE employee_id = ? AND attendance_date = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->execute([$employee_id, $attendance_date]);
            $existingRecord = $checkStmt->fetch();

            if ($existingRecord) {
                if (!empty($existingRecord['attendance_time']) && !empty($existingRecord['departure_time'])) {
                    $attendedToday = true;
                } elseif (!empty($existingRecord['attendance_time']) && empty($existingRecord['departure_time'])) {
                    $updateSql = "UPDATE attendance_records 
                                  SET departure_time = ? 
                                  WHERE employee_id = ? AND attendance_date = ?";
                    $updateStmt = $conn->prepare($updateSql);    
                    $updateStmt->execute([$timeField, $employee_id, $attendance_date]);

                    echo "Departure time registered successfully.";
                    $attendedToday = true; 
                }
            } else {
                $insertSql = "INSERT INTO attendance_records (employee_id, attendance_time, attendance_date) 
                              VALUES (?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->execute([$employee_id, $timeField, $attendance_date]);

                echo "Attendance time registered successfully.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Attendance And Departure Times - Mini Attendance System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
        }
        .container {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: 20px auto;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="time"], button[type="submit"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        button[type="submit"] {
            background-color: #ae5c5c;
            color: white;
            border: none;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #ab7c7c;
        }
        .disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
    <script>
        function validateTimeField() {
            var timeField = document.getElementById("timeField").value;
            var startTime = "07:30";
            var endTime = "16:30";
            
            if (timeField < startTime || timeField > endTime) {
                alert("Please enter a time between 07:30 and 16:30.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <?php require_once "header.php"; ?>

    <div class="container">
        <h2>Register Time</h2><br>

        <form id="timeForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return validateTimeField()">
            <label for="timeField">Time:</label>
            <input type="time" id="timeField" name="timeField" required>
            <?php if ($attendedToday): ?>
                <button type="submit" class="disabled" disabled>Already Attended and Departed Today</button>
            <?php else: ?>
                <button type="submit">Register Time</button>
            <?php endif; ?>
        </form>

        <?php if ($attendedToday): ?>
            <p style="color: #873F30;">You have already attended and departed today.</p>
        <?php endif; ?>
    </div>
</body>
</html>
