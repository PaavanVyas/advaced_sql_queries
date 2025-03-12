<?php
include './conn.php';
require_once __DIR__ . '/vendor/autoload.php'; 
ob_start();  


$sql_years = "SELECT DISTINCT DATE_FORMAT(start_date, '%Y') AS year_value FROM contacts_classification ORDER BY start_date;";
$result_years = $conn->query($sql_years);

if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
}

// else {
//     echo "No data submitted";
// }
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
        <div class="row">
        <form method="GET">
    <h5>Select Year & Month Range</h5>

    <?php if ($result_years->num_rows > 0) { ?>
        <label>FROM:</label>
        <input type="date" name="from_date" class="form-control w-25 ms-5" 
            value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : ''; ?>">

        <label>TO:</label>
        <input type="date" name="to_date" class="form-control w-25 ms-5" 
            value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : ''; ?>">

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


         <form action="" method="POST">
         <button type="submit" name="generate_pdf" class="btn btn-primary">Download PDF</button>
                <input type="text" hidden value="<?php echo $from_date?>" name="from_date">
                <input type="text" hidden value="<?php echo $to_date?>  " name="to_date">
            </form>
                
        </div>
        </div>
        <div class="container">
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
    
    <button type="submit" class="btn btn-primary mt-1">Submit</button>
</form>
</div>

        <div class="container mt-4">
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
    // $pdf = new TCPDF('L', 'mm', 'A4');
    $pdf = new TCPDF(); 
    $pdf->AddPage();
    $pdf->setTitle('Data_Report_'.$from_date.'_to_'.$to_date);
    $pdf->setSubject('Setting Subject');
    // $pdf->Write(0,'Hello This pdf will be from '.$from_date.' to '.$to_date);
   

    $pdf->SetFont('helvetica', '', 8);

    // $pdf->Cell(40, 10, 'Classification',1);
    // foreach($months as $month) {
        
    //     $pdf->Cell(15, 10, $month, 1, 0, 'C');

    // }
    // $pdf->Ln();

    // foreach ($data as $classification => $months_data) {
    //     $pdf->Cell(40, 15, empty($classification) ? "Unclassified Data" : $classification, 1, 0, 'C');
    //     foreach ($months as $month) {
    //         $count = isset($months_data[$month]) ? $months_data[$month] : 0;
    //         $pdf->Cell(15, 15, $count, 1, 0, 'C');
    //     }
    //     $pdf->Ln();
    // }
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

        $pdf->Writehtml($generatereport,true,true,true,true,'');
        $pdf->Output();

        // $pdf->Output('report_' . $from_date . ' to ' . $to_date . '.pdf', 'I');
    }
$conn->close(); 
?>
