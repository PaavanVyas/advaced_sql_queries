<?php
include './conn.php';
require_once __DIR__ . '/vendor/autoload.php'; 
ob_start();  
session_start();

$sql_years = "SELECT DISTINCT DATE_FORMAT(start_date, '%Y') AS year_value FROM contacts_classification ORDER BY start_date;";
$result_years = $conn->query($sql_years);
// $current_query = $_SERVER['QUERY_STRING'];
// echo $current_query;
$actual_link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // use REQUEST_URI instead of PHP_SELF
// echo $actual_link;
$includedFileURL = 'http://' . $_SERVER['HTTP_HOST'] . '/display_classification_data.php';
// echo $includedFileURL;
if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
}

?>

<html>
<head>
    <title>Classification Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .reduced-height {
            padding: 5px !important;  
            font-size: 14px;          
            line-height: 1.2;          
            display: flex;
            align-items: center;
            color:black;
        }

.reduced-height button {
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

a {
    text-decoration: none !important; /* Ensures underline is removed */
}

/* .reduced-height button img {
    width: 16px;   
    height: 16px;
} */
</style>
</head>
<body>
<?php
        if (isset($_SESSION["csv_generated"]) && $_SESSION["csv_generated"] == "True") { 
            $file_name = $_SESSION["filename"];
        ?>
        <div class="container mt-2 d-flex justify-content-between alert alert-warning reduced-height">
            <div class="m-0">
                <a href='./csv_files/<?php echo $file_name;?>' class="text-dark link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-0-hover"><?php echo $file_name?> :Completed. Click to Download</a>
            </div>
            <div class="m-0">
                <form action="" method="POST">
                        <button class="btn bg-transperent" name="cancel-export-csv" type="submit">
                            <img src="./images/cancel-button.png" alt="cancel image">
                        </button>       
                </form>

            </div>
        </div>
        <?php
    }
     if (isset($_SESSION["pdf_generated"]) && $_SESSION["pdf_generated"] == "True") { 
            $file_name = $_SESSION["pdf_filename"];
        ?>
        <div class="container mt-2 d-flex justify-content-between alert alert-warning reduced-height">
            <div class="m-0">
            <a href="./csv_files/<?php echo $file_name;?>" 
   download="<?php echo $file_name;?>" 
   class="text-dark link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-0-hover">
   <?php echo $file_name?> : Completed. Click to Download
</a>

            </div>
            <div class="m-0">
                <form action="" method="POST">
                        <button class="btn bg-transperent" name="cancel-export-pdf" type="submit">
                            <img src="./images/cancel-button.png" alt="cancel image">
                        </button>       
                </form>

            </div>
        </div>
        <?php
    }?>
    <div class="container mt-4">
        <div class="row">
        <form method="GET">
    <h5>Select Year & Month Range</h5>

    <?php if ($result_years->num_rows > 0) { ?> 
        <div class="d-flex align-items-center mb-2">
            <label class="me-3" style="width: 60px;">FROM:</label>
            <input type="date" name="from_date" class="form-control w-25" value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : ''; ?>">
        </div>
        <div class="d-flex align-items-center mb-2">
            <label class="me-3" style="width: 60px;">TO:</label>
            <input type="date" name="to_date" class="form-control w-25" value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : ''; ?>">
        </div>

        <div class="d-flex justify-content-between">
            <div>
                <button type="submit" class="btn btn-primary mt-2">Generate Report</button>
            </div>

    <?php } else { ?>
        <p class='container alert alert-warning m-5'>No years found.</p>
    <?php } ?>
</form> 
    <?php
    if (!empty($from_date) && !empty($to_date)) {
    
    if(strtotime($from_date) > strtotime($to_date)) {
    ?></div>
    </div><p class='container alert alert-danger'>Please enter dates correctly.</p><?php
    exit();
    } 

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $sql = "SELECT classification, COUNT(classification) AS count, DATE_FORMAT(start_date, '%Y-%m') AS month 
            FROM contacts_classification 
            WHERE start_date BETWEEN '$from_date' AND '$to_date' 
            GROUP BY classification, month
            ORDER BY month";

    $result = $conn->query($sql);

    if ($result->num_rows==0) {
        die("</div></div><p class='container alert alert-danger'>No data found in the given timestamp " . $conn->error . "</p>");
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
        $rows_per_page = 5; 
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $rows_per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;


        $total_rows = count($data);
        $total_pages = ceil($total_rows / $rows_per_page);

        $offset = ($page - 1) * $limit;
        $sliced_data = array_slice($data, $offset, $limit, true);


    ?>
    <?php
    if($result->num_rows>0){
    ?>
    <div class="d-flex align-items-start">
         <form action="" method="POST">
            <input type="text" hidden value="<?php echo $from_date?>" name="from_date">
            <input type="text" hidden value="<?php echo $to_date?>  " name="to_date">
            <button type="submit" name="generate_pdf" class="btn btn-primary ms-3">Download PDF</button>
        </form>

        <form action="" method="POST">
            <input type="text" hidden value="<?php echo $from_date?>" name="from_date">
            <input type="text" hidden value="<?php echo $to_date?>  " name="to_date">
            <button type="submit" name="generate_csv" class="btn btn-primary ms-3">Download CSV</button>
        </form> 
    </div>        
        </div>
        </div>
        <div>
        <form method="GET">
        <label for="limit">Select Limit:</label><br/>
        <select name="limit" id="limit" class="form-select w-25" required>
    <?php 
    $selected_limit = isset($_GET['limit']) ? $_GET['limit'] : '';
    for ($i = 1; $i <= 10; $i++) {
        echo "<option value='$i' " . ($selected_limit == $i ? 'selected' : '') . " >$i</option>";
    }
    ?>
</select>

    
    <input type="hidden" name="from_date" value="<?php echo htmlspecialchars($from_date, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="to_date" value="<?php echo htmlspecialchars($to_date, ENT_QUOTES, 'UTF-8'); ?>">
    
    <button type="submit" class="btn btn-primary mt-2">Submit</button>
</form>
</div>

        <div class=" mt-4">
            <h6 class="form-label">Showing data from <?php echo $from_date;?> to <?php echo $to_date;?></h6>
        </div>
        <?php
    }
    ?>
</div>
<div class="container table-responsive">
    <table class="table table-bordered border-dark m-4 table-hover">
        <thead class="table-active">
            <tr>
                <th>Classification</th>
                <?php foreach ($months as $month) {
                    echo "<th>" . htmlspecialchars($month) . "</th>";
                } ?>
            </tr>
        </thead>
        <tbody class="table-group-divider bg-light text-dark">
            <?php foreach ($sliced_data as $classification => $months_data) { ?>
                <tr>
                    <td><?php echo htmlspecialchars(empty($classification) ? "Unclassified Data or Missing classification." : $classification); ?></td>
                    <?php foreach ($months as $month) { ?>
                        <td class="text-center"><?php echo isset($months_data[$month]) ? $months_data[$month] : 0; ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="container mt-4">
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?page=<?php echo $page - 1; ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&limit=<?php echo urlencode($limit); ?>">
                        Previous
                    </a>
                </li>
            <?php } ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" 
                       href="?page=<?php echo $i; ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&limit=<?php echo urlencode($limit); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>

            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?page=<?php echo $page + 1; ?>&from_date=<?php echo urlencode($from_date); ?>&to_date=<?php echo urlencode($to_date); ?>&limit=<?php echo urlencode($limit); ?>">
                        Next
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>



<?php
}
else {
    echo "</div></div><p class= 'container alert alert-danger'>Please select both year and month ranges to display data.</p>";
}
?>

</body>
</html>

<?php 

if (isset($_POST['generate_pdf'])) {
    ob_end_clean();  

    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    date_default_timezone_set('Asia/Kolkata');
    $filename = "classification_report_".date("Y-m-d_H-i-s").".pdf";
    

    $pdf = new TCPDF(); 
    $pdf->AddPage();
    $pdf->setTitle('Data_Report_' . $from_date . '_to_' . $to_date);
    $pdf->setSubject('Setting Subject');
    $pdf->SetFont('helvetica', '', 8);

    $generatereport = '<table class="table table-bordered border-dark m-4 table-hover" style="border: 1px solid black; border-collapse: collapse; margin: 10px;">';
    $generatereport .= '<thead class="table-active">';
    $generatereport .= '<tr>';
    $generatereport .= '<th style="border: 1px solid black; padding: 8px; text-align: center;">Classification</th>';

    foreach ($months as $month) {
        $generatereport .= '<th style="border: 1px solid black; padding: 8px; text-align: center;">' . htmlspecialchars($month) . '</th>';
    }

    $generatereport .= '</tr>';
    $generatereport .= '</thead>';
    $generatereport .= '<tbody class="table-group-divider bg-light text-dark">';

    foreach ($data as $classification => $months_data) {
        $generatereport .= '<tr>';
        $generatereport .= '<td style="border: 1px solid black; padding: 8px; text-align: center;">' . htmlspecialchars(empty($classification) ? "Unclassified Data or Missing classification." : $classification) . '</td>';

        foreach ($months as $month) {
            $count = isset($months_data[$month]) ? $months_data[$month] : 0;
            $generatereport .= '<td class="text-center" style="border: 1px solid black; padding: 8px; text-align: center;">' . $count . '</td>';
        }

        $generatereport .= '</tr>';
    }

    $generatereport .= '</tbody>';
    $generatereport .= '</table>';

    $pdf->Writehtml($generatereport, true, true, true, true, '');

    $directory = __DIR__ . "/csv_files/";
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true); 
    }
    $file_path = $directory . $filename;

    $pdf->Output($file_path, 'F');


    $_SESSION["pdf_generated"] = "True";
    $_SESSION["pdf_filename"] = $filename;

    // **Redirect user to display download link**
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
    exit();
}


if (isset($_POST['generate_csv'])) {
    echo "form submitted";
    $from_date = $_POST['from_date'];
    $to_date = $_POST['to_date'];
    date_default_timezone_set('Asia/Kolkata');
    $filename = "classification_report_".date("Y-m-d_H-i-s").".csv";
    
    $file_path = "./csv_files/" . $filename;
    $output = fopen($file_path, "w");

    $sql = "SELECT DISTINCT DATE_FORMAT(start_date, '%Y-%m') AS month 
            FROM contacts_classification 
            WHERE start_date BETWEEN '$from_date' AND '$to_date'
            ORDER BY month";

    $result = $conn->query($sql);
    $months = [];

    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month'];
    }

    fputcsv($output, array_merge(["Classification"], $months));

    // Fetching the classification data
    $sql = "SELECT classification, COUNT(classification) AS count, DATE_FORMAT(start_date, '%Y-%m') AS month 
            FROM contacts_classification 
            WHERE start_date BETWEEN '$from_date' AND '$to_date' 
            GROUP BY classification, month
            ORDER BY month";

    $result = $conn->query($sql);
    $sliced_data = [];

    while ($row = $result->fetch_assoc()) {
        $classification = $row['classification'] ?: 'Unclassified Data';
        $month = $row['month'];
        $sliced_data[$classification][$month] = $row['count'];
    }

    foreach ($sliced_data as $classification => $months_data) {
        $row = [$classification];
        foreach ($months as $month) {
            $row[] = isset($months_data[$month]) ? $months_data[$month] : 0;
        }
        fputcsv($output, $row);
    }

    $_SESSION["csv_generated"] = "True";
    $_SESSION["filename"] = $filename;
    echo "Session created";

    fclose($output);
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

    exit();
}
if(isset($_POST["cancel-export-csv"])){

        unset($_SESSION["csv_generated"]);

    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
}

if(isset($_POST["cancel-export-pdf"])){
    unset($_SESSION["pdf_generated"]);
    header("Location: " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);
}

    
$conn->close();   
?>