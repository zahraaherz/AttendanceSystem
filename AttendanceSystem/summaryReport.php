<?php
require "connection.php";
include "header.php";
$search_date = date('Y-m-d'); 
$search_type = 'daily';
$daily_summary = [];
$monthly_summary = [];
$yearly_summary = [];
$attendance_data = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['search_date'])) {
        $search_date = $_POST['search_date'];
    }
    if (isset($_POST['search_type'])) {
        $search_type = $_POST['search_type'];
    }
}

try {

    // Fetch daily summary
    $sql_daily = "SELECT 
                    COUNT(*) AS total_records,
                    SUM(CASE WHEN attendance_time IS NOT NULL THEN 1 ELSE 0 END) AS total_arrivals,
                    SUM(CASE WHEN departure_time IS NOT NULL THEN 1 ELSE 0 END) AS total_departures
                  FROM attendance_records
                  WHERE attendance_date = :date";
    $stmt_daily = $conn->prepare($sql_daily);
    $stmt_daily->bindParam(':date', $search_date);
    $stmt_daily->execute();
    $daily_summary = $stmt_daily->fetch(PDO::FETCH_ASSOC);

    // Fetch monthly summary
    $month = date('m', strtotime($search_date));
    $year = date('Y', strtotime($search_date));
    $sql_monthly = "SELECT 
                      COUNT(*) AS total_records,
                      SUM(CASE WHEN attendance_time IS NOT NULL THEN 1 ELSE 0 END) AS total_arrivals,
                      SUM(CASE WHEN departure_time IS NOT NULL THEN 1 ELSE 0 END) AS total_departures
                    FROM attendance_records
                    WHERE MONTH(attendance_date) = :month AND YEAR(attendance_date) = :year";
    $stmt_monthly = $conn->prepare($sql_monthly);
    $stmt_monthly->bindParam(':month', $month);
    $stmt_monthly->bindParam(':year', $year);
    $stmt_monthly->execute();
    $monthly_summary = $stmt_monthly->fetch(PDO::FETCH_ASSOC);

    // Fetch yearly summary
    $sql_yearly = "SELECT 
                     COUNT(*) AS total_records,
                     SUM(CASE WHEN attendance_time IS NOT NULL THEN 1 ELSE 0 END) AS total_arrivals,
                     SUM(CASE WHEN departure_time IS NOT NULL THEN 1 ELSE 0 END) AS total_departures
                   FROM attendance_records
                   WHERE YEAR(attendance_date) = :year";
    $stmt_yearly = $conn->prepare($sql_yearly);
    $stmt_yearly->bindParam(':year', $year);
    $stmt_yearly->execute();
    $yearly_summary = $stmt_yearly->fetch(PDO::FETCH_ASSOC);

    // Fetch attendance data for chart based on selected timeframe
    switch ($search_type) {
        case 'daily':
            $start_date = $search_date;
            $end_date = $search_date;
            break;
        case 'monthly':
            $start_date = date('Y-m-01', strtotime($search_date));
            $end_date = date('Y-m-t', strtotime($search_date));
            break;
        case 'yearly':
            $start_date = date('Y-01-01', strtotime($search_date));
            $end_date = date('Y-12-31', strtotime($search_date));
            break;
        default:
            $start_date = $search_date;
            $end_date = $search_date;
            break;
    }

    // Prepare and execute query to get attendance data for the selected date range
    $sql_attendance = "SELECT attendance_time, departure_time FROM attendance_records WHERE attendance_date BETWEEN :start_date AND :end_date";
    $stmt_attendance = $conn->prepare($sql_attendance);
    $stmt_attendance->bindParam(':start_date', $start_date);
    $stmt_attendance->bindParam(':end_date', $end_date);
    $stmt_attendance->execute();
    $attendance_records = $stmt_attendance->fetchAll(PDO::FETCH_ASSOC);

    $on_time_count_arrival = 0;
    $early_count_arrival = 0;
    $late_count_arrival = 0;
    $on_time_count_departure = 0;
    $early_count_departure = 0;
    $overtime_count_departure = 0;

    $arrival_threshold_early = strtotime(date('Y-m-d') . ' 07:30:00');
    $arrival_threshold_on_time = strtotime(date('Y-m-d') . ' 08:00:00');
    $departure_threshold_on_time = strtotime(date('Y-m-d') . ' 16:00:00');
    $departure_threshold_overtime = strtotime(date('Y-m-d') . ' 16:30:00');

    foreach ($attendance_records as $record) {
        if (!empty($record['attendance_time'])) {
            $arrival_time = strtotime($record['attendance_time']);
            if ($arrival_time <= $arrival_threshold_early) {
                $early_count_arrival++;
            } elseif ($arrival_time <= $arrival_threshold_on_time) {
                $on_time_count_arrival++;
            } else {
                $late_count_arrival++;
            }
        }

        if (!empty($record['departure_time'])) {
            $departure_time = strtotime($record['departure_time']);
            if ($departure_time < $departure_threshold_on_time) {
                $early_count_departure++;
            } elseif ($departure_time <= $departure_threshold_overtime) {
                $on_time_count_departure++;
            } else {
                $overtime_count_departure++;
            }
        }
    }

    $attendance_data = [
        'early_arrival' => $early_count_arrival,
        'on_time_arrival' => $on_time_count_arrival,
        'late_arrival' => $late_count_arrival,
        'early_departure' => $early_count_departure,
        'on_time_departure' => $on_time_count_departure,
        'overtime_departure' => $overtime_count_departure
    ];

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Summary Report with Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        } 
        h2{
           

        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-right: 10px;
        }
        input[type="date"] {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        input[type="submit"] {
            padding: 8px 16px;
            background-color: #ae5c5c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .summary-container {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .summary-container h3 {
            margin-bottom: 10px;
            text-align: center;
        }
        .summary-content {
            display: flex;
            justify-content: space-around;
            margin-bottom: 10px;
        }
        .summary-content p {
            flex: 1;
            text-align: center;
        }
        .chart-container {
            text-align: center;
        }
         #search_type {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            width: 150px; 
            -webkit-appearance: none; /* Remove default arrow */
            -moz-appearance: none;
            appearance: none;
            background-color: #ffffff; /* Dropdown background color */
            background-image: url('data:image/svg+xml;utf8,<svg fill="#000000" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>'); /* Custom arrow icon */
            background-repeat: no-repeat;
            background-position: right 8px center;
            padding-right: 24px; /* Adjust space for arrow icon */
            cursor: pointer;
        }
        #search_type:focus {
            outline: none;
            border-color: #666; /* Focus state border color */
        }
        /* Additional styles for options if needed */
        #search_type option {
            background-color: #fff;
            color: #333;
        }
        .selected {
            background-color: #f9f9f9; /* Background color when option selected */
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Attendance Summary Report with Chart</h2> 
    <br>
    <!-- Search form -->
    <form method="post" action="">
        <label for="search_date">Select Date:</label>
        <input type="date" id="search_date" name="search_date" value="<?php echo htmlspecialchars($search_date); ?>">
        <label for="search_type">Select Timeframe:</label>
        <select id="search_type" name="search_type">
            <option value="daily" <?php if ($search_type == 'daily') echo 'selected'; ?>>Daily</option>
            <option value="monthly" <?php if ($search_type == 'monthly') echo 'selected'; ?>>Monthly</option>
            <option value="yearly" <?php if ($search_type == 'yearly') echo 'selected'; ?>>Yearly</option>
        </select>
        <input type="submit" value="Search">
    </form>

    <br>
    <!-- Daily Summary -->
    <div class="summary-container">
        <h3>Daily Summary for <?php echo htmlspecialchars($search_date); ?></h3>
        <div class="summary-content">
            <p>Total Records: <?php echo $daily_summary['total_records']; ?></p>
            <p>Total Arrivals: <?php echo $daily_summary['total_arrivals']; ?></p>
            <p>Total Departures: <?php echo $daily_summary['total_departures']; ?></p>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="summary-container">
        <h3>Monthly Summary for <?php echo date('F Y', strtotime($search_date)); ?></h3>
        <div class="summary-content">
            <p>Total Records: <?php echo $monthly_summary['total_records']; ?></p>
            <p>Total Arrivals: <?php echo $monthly_summary['total_arrivals']; ?></p>
            <p>Total Departures: <?php echo $monthly_summary['total_departures']; ?></p>
        </div>
    </div>

    <!-- Yearly Summary -->
    <div class="summary-container">
        <h3>Yearly Summary for <?php echo date('Y', strtotime($search_date)); ?></h3>
        <div class="summary-content">
            <p>Total Records: <?php echo $yearly_summary['total_records']; ?></p>
            <p>Total Arrivals: <?php echo $yearly_summary['total_arrivals']; ?></p>
            <p>Total Departures: <?php echo $yearly_summary['total_departures']; ?></p>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container">
        <canvas id="attendanceChart" width="800" height="400"></canvas>
    </div>

</div>
<script>
    // Attendance data for the chart
    var attendanceData = {
        labels: ['Early Arrival', 'On Time Arrival', 'Late Arrival', 'Early Departure', 'On Time Departure', 'Overtime Departure'],
        datasets: [{
            label: 'Attendance Analysis',
            backgroundColor: ['#e74c3c', '#2ecc71', '#3498db', '#f39c12', '#9b59b6', '#1abc9c'],
            borderColor: '#ddd',
            borderWidth: 1,
            data: [
                <?php echo $attendance_data['early_arrival']; ?>,
                <?php echo $attendance_data['on_time_arrival']; ?>,
                <?php echo $attendance_data['late_arrival']; ?>,
                <?php echo $attendance_data['early_departure']; ?>,
                <?php echo $attendance_data['on_time_departure']; ?>,
                <?php echo $attendance_data['overtime_departure']; ?>
            ]
        }]
    };

    // Chart options
    var options = {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }]
        },
        legend: {
            display: true,
            position: 'top'
        },
        title: {
            display: true,
            text: 'Attendance Analysis for <?php echo htmlspecialchars($search_date); ?>'
        }
    };

    // Get chart canvas
    var ctx = document.getElementById('attendanceChart').getContext('2d');

    // Create bar chart
    var attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: attendanceData,
        options: options
    });
</script>


</body>
</html>