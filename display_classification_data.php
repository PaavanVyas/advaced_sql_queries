<?php
include './conn.php';

$sql_years = "SELECT DISTINCT DATE_FORMAT(start_date, '%Y') AS year_value FROM contacts_classification ORDER BY start_date;";
$result_years = $conn->query($sql_years);

$from_year = isset($_POST['from_year']) ? $_POST['from_year'] : "";
$to_year = isset($_POST['to_year']) ? $_POST['to_year'] : "";
$from_month = isset($_POST['from_month']) ? $_POST['from_month'] : "";
$to_month = isset($_POST['to_month']) ? $_POST['to_month'] : "";

if (!empty($from_year) && !empty($to_year)) {
    $sql = "SELECT classification, ";

    $conditions = [];
    
    for ($year = $from_year; $year <= $to_year; $year++) {
        $startMonth = ($year == $from_year) ? $from_month : 1;
        $endMonth = ($year == $to_year) ? $to_month : 12;

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $formattedMonth = str_pad($month, 2, "0", STR_PAD_LEFT); // Ensure two-digit format
            $column = "SUM(CASE WHEN YEAR(start_date) = $year AND MONTH(start_date) = $month THEN 1 ELSE 0 END) AS '{$year}_{$formattedMonth}'";
        $sql .= "$column, ";
        }
    }

    $sql = rtrim($sql, ", ");
    $sql .= " FROM contacts_classification GROUP BY classification;";
    $result = $conn->query($sql);
}
?>

<html>
<head>
    <title>Classification Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <form method="POST">
            <h5>Select Year & Month Range</h5>
            <div class="row">
                <?php if ($result_years->num_rows > 0) { ?>
                    <div class="col-md-3">
                        <label for="year1" class="form-label">From Year</label>
                        <select id="year1" name="from_year" class="form-select">
                            <option value="">Select Year</option>
                            <?php while ($row = $result_years->fetch_assoc()) { ?>
                                <option value="<?php echo $row['year_value']; ?>" <?php echo ($row['year_value'] == $from_year) ? 'selected' : ''; ?>>
                                    <?php echo $row['year_value']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="month1" class="form-label">From Month</label>
                        <select id="month1" name="from_month" class="form-select">
                            <?php for ($m = 1; $m <= 12; $m++) { 
                                $mFormatted = sprintf('%02d', $m);
                            ?>
                                <option value="<?php echo $mFormatted; ?>" <?php echo ($mFormatted == $from_month) ? 'selected' : ''; ?>>
                                    <?php echo date("F", mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <?php $result_years->data_seek(0); ?>

                    <div class="col-md-3">
                        <label for="year2" class="form-label">To Year</label>
                        <select id="year2" name="to_year" class="form-select">
                            <option value="">Select Year</option>
                            <?php while ($row = $result_years->fetch_assoc()) { ?>
                                <option value="<?php echo $row['year_value']; ?>" <?php echo ($row['year_value'] == $to_year) ? 'selected' : ''; ?>>
                                    <?php echo $row['year_value']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="month2" class="form-label">To Month</label>
                        <select id="month2" name="to_month" class="form-select">
                            <?php for ($m = 1; $m <= 12; $m++) { 
                                $mFormatted = sprintf('%02d', $m);
                            ?>
                                <option value="<?php echo $mFormatted; ?>" <?php echo ($mFormatted == $to_month) ? 'selected' : ''; ?>>
                                    <?php echo date("F", mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </div>
                <?php } else { echo "<p class='alert alert-warning'>No years found.</p>"; } ?>
            </div>
        </form>

        <div>
            <?php if (!empty($from_year) && !empty($to_year) && $result_years->num_rows > 0) { 
                if ($to_year < $from_year || ($to_year == $from_year && $to_month < $from_month)) {
                    echo "<p> class='alert alert-danger'>Invalid date range. Please select again.</p>";
                } 
                else { ?>
                <table class="table table-bordered border-dark m-4 table-hover">
                <thead class="table-active">
    <tr>
        <th rowspan="2">Classification</th>
        <?php for ($year = $from_year; $year <= $to_year; $year++) { ?>
            <?php 
                // Determine the correct number of months for each year
                $startMonth = ($year == $from_year) ? $from_month : 1;
                $endMonth = ($year == $to_year) ? $to_month : 12;
                $monthCount = $endMonth - $startMonth + 1;
            ?>
            <th colspan="<?php echo $monthCount; ?>" class="text-center"><?php echo $year; ?></th>
        <?php } ?>
    </tr>
    <tr>
        <?php for ($year = $from_year; $year <= $to_year; $year++) { ?>
            <?php 
                $startMonth = ($year == $from_year) ? $from_month : 1;
                $endMonth = ($year == $to_year) ? $to_month : 12;
            ?>
            <?php for ($month = $startMonth; $month <= $endMonth; $month++) { ?>
                <th><?php echo date("M", mktime(0, 0, 0, $month, 1)); ?></th>
            <?php } ?>
        <?php } ?>
    </tr>
</thead>

                    <tbody class="table-group-divider bg-light text-dark">
                    <?php
                    if (!$result) {
                        die("<p class='alert alert-danger'>Database Error: " . $conn->error . "</p>");
                    }

                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo ($row['classification'] == "") ? "Unclassified or missing classification" : $row['classification']; ?></td>
                            
                            <?php for ($year = $from_year; $year <= $to_year; $year++) { ?>
                                <?php 
                                    $startMonth = ($year == $from_year) ? $from_month : 1;
                                    $endMonth = ($year == $to_year) ? $to_month : 12;
                                ?>
                                <?php for ($month = $startMonth; $month <= $endMonth; $month++) { 
                                    $monthFormatted = sprintf('%02d', $month); ?>
                                    <td><?php echo isset($row["{$year}_{$monthFormatted}"]) ? $row["{$year}_{$monthFormatted}"] : 0; ?></td>
                                <?php } ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    
                    </tbody>
                </table>
            <?php } 
         } else { echo "<p class='alert alert-danger'>Please select both year and month ranges to display data.</p>"; } ?>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
