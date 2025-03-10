<?php
include './conn.php';

$sql_years = "SELECT DISTINCT DATE_FORMAT(start_date, '%Y') AS year_value FROM contacts_classification ORDER BY start_date;";
$result_years = $conn->query($sql_years);

if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
} else {
    echo "No data submitted";
}
?>

<html>
<head>
    <title>Classification Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* .table-responsive{
            max-width:95%;
        } */
    </style>
</head>
<body>
    <div class="container mt-4">
        <form method="POST">
            <h5>Select Year & Month Range</h5>
            <div class="row">
                <?php if ($result_years->num_rows > 0) { ?>
                    <input type="date" name="from_date">
                    <input type="date" name="to_date">
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                <?php } else { echo "<p class='container alert alert-warning'>No years found.</p>"; } ?>
            </div>
        </form>
    </div>
    
    <?php
if (!empty($from_date) && !empty($to_date)) {
    
    if(strtotime($from_date) > strtotime($to_date)) {
    echo "<p class='container alert alert-danger'>Please enter dates correctly.</p>";
    exit();
    } 

    $sql = "SELECT classification, COUNT(classification) AS count, DATE_FORMAT(start_date, '%Y-%m') AS month 
            FROM contacts_classification 
            WHERE start_date BETWEEN '$from_date' AND '$to_date' 
            GROUP BY classification, month";

    $result = $conn->query($sql);

    if ($result->num_rows==0) {
        die("<p class='container alert alert-danger'>No data found in the given timestamp " . $conn->error . "</p>");
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['classification']][$row['month']] = $row['count'];
    }

    $months = [];
    foreach ($data as $classification => $months_data) {
        foreach ($months_data as $month => $count) {
            $months[$month] = $month;
        }
    }

    sort($months);
    ?>

    <div class="table-responsive">
        <table class="table table-bordered border-dark m-4 table-hover">
            <thead class="table-active">
                <tr>
                    <th>Classification</th>
                    <?php 
                    foreach ($months as $month) {
                        echo "<th>" . htmlspecialchars($month) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody class="table-group-divider bg-light text-dark">
                <?php 
                foreach ($data as $classification => $months_data) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars(empty($classification) ? "Unclassified Data or Missing classification." : $classification); ?></td>
                        <?php
                        foreach ($months as $month) {
                            echo "<td class='text-center'>" . (isset($months_data[$month]) ? $months_data[$month] : 0) . "</td>";
                        }
                        ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

<?php
}
else {
    echo "<p class= 'container alert alert-danger'>Please select both year and month ranges to display data.</p>";
}
?>

</body>
</html>

<?php $conn->close(); ?>