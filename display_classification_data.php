<?php
    include './conn.php';

    // Fetch distinct years from the database
    $sql_years = "SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS YEAR FROM contacts_classification ORDER BY start_date;";
    $result_years = $conn->query($sql_years);

    // Get user input (selected years)
    $from_year = isset($_POST['from_year']) ? $_POST['from_year'] : "";
    $to_year = isset($_POST['to_year']) ? $_POST['to_year'] : "";
    echo $from_year;
    echo $to_year;

    if (!empty($from_year) && !empty($to_year)) {
        $sql = "SELECT classification, ";


        for ($year = $from_year; $year <= $to_year; $year++) {
            $sql .= "IFNULL(SUM(CASE WHEN DATE_FORMAT(start_date, '%Y-%m') = '$year' THEN 1 ELSE 0 END), 0) AS `count_$year`, ";
        }

        $sql = rtrim($sql, ", "); // Remove the trailing comma
        $sql .= " FROM contacts_classification GROUP BY classification;";

        $result = $conn->query($sql);
    }
?>

<html>
<head>
    <title>Classification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <form method="POST">
            <h5>Select the Year Range</h5>
            <div class="row">
                <?php if ($result_years->num_rows > 0) { ?>
                    <div class="col-md-3">
                        <label for="year1" class="form-label">From</label>
                        <select id="year1" name="from_year" class="form-select w-50">
                            <option value="">Select Year</option>
                            <?php while ($row = $result_years->fetch_assoc()) { 
                                if($row['YEAR'] != "0"){?>
                                <option value="<?php echo $row['YEAR']; ?>" <?php echo ($row['YEAR'] == $from_year) ? 'selected' : ''; ?>>
                                    <?php echo $row['YEAR']; ?>
                                </option>
                            <?php }} ?>
                        </select>
                    </div>

                    <?php $result_years->data_seek(0);?>

                    <div class="col-md-3">
                        <label for="year2" class="form-label">To</label>
                        <select id="year2" name="to_year" class="form-select w-50">
                            <option value="">Select Year</option>
                            <?php while ($row = $result_years->fetch_assoc()) {
                                if($row['YEAR'] != "0"){?>
                                <option value="<?php echo $row['YEAR']; ?>" <?php echo ($row['YEAR'] == $to_year) ? 'selected' : ''; ?>>
                                    <?php echo $row['YEAR']; ?>
                                </option>
                            <?php
                        } } ?>
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
                if($to_year < $from_year){
                    echo "<p class='alert alert-danger'>Please enter a valid date range.</p>";
                }
                else { ?>
                <table class="table table-bordered border-dark m-4 table-hover top-50 start-50">
                    <tr class="table-active">
                        <th>Classification</th>
                        <?php
                        echo $from_year;
                        echo $to_year;
                         for ($year = $from_year; $year <= $to_year; $year++) {

                            if ((int)date("m", strtotime($year)) < 12) { ?>
                            <th><?php echo $year; ?></th>
                        <?php
                    } } ?>
                    </tr>
                    <tbody class="table-group-divider bg-light text-dark top-50 start-50">
                    <?php
                    if (!$result) {
                        throw new Exception("Database Error [{$conn->errno}] {$conn->error}");
                    }
                    
                    while ($row = $result->fetch_assoc()) { ?>
                        <tr class="table-group-divider">
                            <?php
                                if($row['classification']==""){
                                    $row['classification']="Unclassified or missing classification";
                                }
                            ?>
                            <td><?php echo $row['classification']; ?></td>
                            <?php for ($year = $from_year; $year <= $to_year; $year++) { ?>
                                <td><?php echo $row["count_$year"]?></td>
                            <?php } ?>
                        </tr>
                    <?php } 
                    ?>
                </table>
            <?php
            }
         } else { echo "<p class='alert alert-danger'>Please select both year ranges to display data.</p>"; } ?>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
