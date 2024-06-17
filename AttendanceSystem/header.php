<?php
require("connection.php");
$employee_id = $_SESSION["USER_ID"];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        header {
            background-color: #ae5c5c;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        header a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        header a:hover {
            background-color: #961f1f; /* Darken the background color on hover */
        }
    </style>
</head>
<body>
    <header>
        <div><a href='index.php'>Home</a></div>
        <?php 
        if(empty($employee_id)){
        ?>
            <div><a href='login.php'>Login</a></div>
        <?php    
        } else {
        ?>
            <div><a href='logout.php'>Logout</a></div>
            <div><a href='registerAttendance.php'>Register Attendance Time</a></div>
        <?php
        }
        ?>
        <div><a href='summaryReport.php'>Summary Report</a></div>
        <div><a href='viewAttendance.php'>Attendance Record</a></div>
    </header>
</body>
</html>
