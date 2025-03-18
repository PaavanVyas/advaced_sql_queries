<?php
ob_start();
include './conn.php';
session_start();
require_once __DIR__ . '/vendor/autoload.php';
$query_string = $_SERVER['QUERY_STRING'];
if(!empty($_GET["selectedValue"])){
    $selectedValue = $_GET["selectedValue"];
    if (isset($selectedValue)) {    
        // echo "The selected value is: " . htmlspecialchars($selectedValue);
    } else {
        echo "No selected value available.";
    }
}
if (!empty($_GET)) { 

  } else {

  }

if(isset($_GET['url'])){
    $query_string = $_GET['url'];
}

parse_str($query_string, $query_params);

unset($query_params['page']);

$query_string = http_build_query($query_params);


$new_query_string = http_build_query($query_params);



$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if (isset($_GET['classification_search']) && !empty($_GET['classification_search'])) {
    $classification_search = $_GET['classification_search'];
}
if (isset($_GET['category_search']) && !empty($_GET['category_search'])) {
    $category_search = $_GET['category_search'];
}
if(isset($_GET['from_date']) && !empty($_GET['from_date'])){
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
}
if(isset($_GET['to_date']) && !empty($_GET['to_date'])){
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
}
$offset = ($page - 1) * $limit;


$total_sql = "SELECT COUNT(*) AS total FROM contacts 
              JOIN contacts_classification 
              ON contacts.contactid = contacts_classification.contactid";
if (!empty($classification_search)) {
    $classification_search = mysqli_real_escape_string($conn, $classification_search);

    $total_sql .= " WHERE contacts_classification.classification = '$classification_search'";
}

if (!empty($category_search)) {
    if (strpos($total_sql, "WHERE") !== false) {
        $total_sql .= " AND contacts.category = '$category_search'";
    } else {
        $total_sql .= " WHERE contacts.category ='$category_search'";
    }
}


if (!empty($from_date) && !empty($to_date)) {
    if (strpos($total_sql, "WHERE") !== false) {
        $total_sql .= " AND start_date BETWEEN '$from_date' AND '$to_date'";
    } else {
        $total_sql .= " WHERE start_date BETWEEN '$from_date' AND '$to_date'";
    }
}
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_rows = $total_row['total'];

$total_pages = ceil($total_rows / $limit);


$sql = "SELECT CONCAT(first_name, ' ', last_name) AS Full_Name, 
               email_address, 
               cell_phone,
               contacts.category AS category, 
               contacts_classification.classification AS classification, 
               start_date, 
               expiry_date, 
               SUM(donation_total) AS total_donations,
               type,
               invoice_type
        FROM contacts
        LEFT JOIN contacts_classification
        ON contacts.contactid = contacts_classification.contactid
        LEFT JOIN contacts_donation 
        ON contacts.contactid = contacts_donation.contactid
        LEFT JOIN invoice 
        ON contacts_donation.serviceid = invoice.serviceid 
        OR contacts_donation.patientid = invoice.patientid 
        ";

if (!empty($classification_search)) {
    $classification_search = mysqli_real_escape_string($conn, $classification_search);
    // If it's the first condition, use WHERE, otherwise use AND
    $sql .= " WHERE contacts_classification.classification = '$classification_search'";
}


if (!empty($category_search)) {

    if (strpos($sql, "WHERE") !== false) {
        $sql .= " AND contacts.category = '$category_search'";
    } else {
        $sql .= " WHERE contacts.category ='$category_search'";
    }
}

if (!empty($from_date) && !empty($to_date)) {
    if (strpos($sql, "WHERE") !== false) {
        $sql .= " AND start_date BETWEEN '$from_date' AND '$to_date'";
    } else {
        $sql .= " WHERE start_date BETWEEN '$from_date' AND '$to_date'";
    }
}
$sql .= " GROUP BY contacts.contactid, contacts_classification.classification, contacts.category, start_date, expiry_date, email_address, cell_phone";
$sql .= " LIMIT $limit OFFSET $offset";


$result = $conn-> query($sql);
$num_rows_result = $result -> num_rows;
echo $num_rows_result;

?>

<html>
<head>
    <title>Member Classification Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        label{
            font-weight: bold;
        }

    </style>
</head>
<body>
<div class="container w-100 mt-3 border">
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
    <form action="" method="get">
    
    <div class="container mt-2">
    <div class="row">
        <div class="col">
            <label class="form-label">Search by classification</label>
        </div>
        <div class="col">
            <input type="text" class="form-label w-100" name="classification_search" value="<?php echo isset($_GET['classification_search']) ? htmlspecialchars($_GET['classification_search']) : '' ?>" placeholder="Search By Classification" class="form-control">
        </div>
        <div class="col">
            <label class="form-label">Search by category</label>
        </div>
        <div class="col w-100">
            <input type="text" class="form-label w-100" name="category_search" value="<?php echo isset($_GET['category_search']) ? htmlspecialchars($_GET['category_search']) : '' ?>" placeholder="Search By Category" class="form-control">
        </div>
    </div>

    <div class="row align-items-center">
    <div class="col-3">
        <label class="form-label">Search by Membership Date</label>
    </div>

    <div class="col-auto">
        <input type="date" class="form-control-sm w-100" name="from_date" id="from_date"
            value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']) : '' ?>">
    </div>

    <div class="col-auto">
        <input type="date" class="form-control-sm w-100 " name="to_date" id="to_date"
            value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']) : '' ?>">
    </div>

    <?php if (isset($_GET['selectedValue'])) { ?>
        <input type="hidden" name="selectedValue" value="<?php echo htmlspecialchars($_GET['selectedValue']); ?>">
    <?php } ?>

    <div class="col text-end mb-2">
        <input type="submit" class="btn btn-primary ">
    </div>
    </form>
</div>

    </div>

        <form method="get" action="">
    
    <div class="container mt-2 mb-2">
    <div class="row align-items-center">
    <div class="col-3">
        <label for="limit" class="form-label">Select Limit:</label>
    </div>
    <div class="col-3">
        <select name="limit" id="limit" class="form-select" required>
            <?php
            $selected_limit = isset($_GET['limit']) ? $_GET['limit'] : 5;
            for ($i = 5; $i <= 200; $i += 5) {
                echo "<option value='$i' " . ($selected_limit == $i ? 'selected' : '') . ">$i</option>";
            }
            ?>
        </select>
    </div>

    <?php
    $query_params = $_GET;
    unset($query_params['page'], $query_params['limit']);
    foreach ($query_params as $key => $value) {
        if (!empty($value)) {
            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
    }
    if (isset($_GET['selectedValue'])) {
        echo "<input type='hidden' name='selectedValue' value='" . htmlspecialchars($_GET['selectedValue']) . "'>";
    }
    ?>
    
    <!-- Right-align Submit button -->
    <div class="col">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</div>

</div>
</form>

    <?php
    if(!isset($classification_search) && !isset($category_search) && !isset($from_date) && !isset($to_date)){
        die("<p class='container alert alert-danger mt-2'>Please enter atleast one criteria to display data" . $conn->error . "</p>");
    }
    if ($result->num_rows==0) {
        die("<p class='container alert alert-danger mt-2'>No data found in the given Condition/s " . $conn->error . "</p>");
    }
    ?>

    </div>
    <?php
    if($result->num_rows>0){
        ?>
        <div class="row">
        <div class="col d-flex justify-content-end mt-2">
             <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
                <input type="text" hidden value="<?php if(!empty($from_date)){ echo $from_date;}?>" name="from_date">
                <input type="text" hidden value="<?php if(!empty($to_date)){echo $to_date;}?>  " name="to_date">
                <button type="submit" name="generate_pdf" class="btn btn-primary ms-3">Download PDF</button>
            </form>
    
            <form action="" method="POST">
                <input type="text" hidden value="<?php if(!empty($from_date)){echo $from_date;}?>" name="from_date">
                <input type="text" hidden value="<?php if(!empty($to_date)){echo $to_date;}?>" name="to_date">
                <button type="submit" name="generate_csv" class="btn btn-primary ms-3">Download CSV</button>
            </form> 
        </div> 
    </div>       
    <?php
    }
    if (count($_GET)>1) { 
    ?>
    <table class="container table table-bordered mt-2">
        <tr>
            <td>Full Name</td>
            <td>Email Address</td>
            <td>Cell Phone</td>
            <td>Classification</td>
            <td>Start Date</td>
            <td>Expiry Date</td>
            <td>category</td>
            <td>Total Donation</td>
            <td>Type</td>
        </tr>

        <?php
        while($row = $result->fetch_assoc()){
            ?>
            
            <tr>
                <td><?php echo decryptItShared($row["Full_Name"]);?></td>
                <td><?php echo decryptItShared($row["email_address"]);?></td>
                <td><?php echo decryptItShared($row["cell_phone"]);?></td>
                <td><?php echo $row["classification"];?></td>
                <td><?php echo $row["start_date"];?></td>
                <td><?php echo $row["expiry_date"];?></td>
                <td><?php echo $row["category"];?></td>
                <td><?php echo $row["total_donations"];?></td>
                <td><?php echo $row["type"];?></td>
            </tr>
        <?php
        }
        ?>
    </table>
</div>
<div class="container mt-4">
<nav>
    <ul class="pagination" style="max-width: 100%; overflow-x: scroll; ">
        <?php if ($page > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $query_string; ?>">
                    Previous
                </a>
            </li>
        <?php } ?>

        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>" style="display: inline;">
                <a class="page-link" href="?&page=<?php echo $i; ?>&<?php echo $query_string; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php } ?>

        <?php if ($page < $total_pages) { ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $query_string; ?>">
                    Next
                </a>
            </li>
        <?php } ?>
    </ul>
</nav>

</div>
<?php
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
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS Full_Name, 
               email_address, 
               cell_phone,
               contacts.category AS category, 
               contacts_classification.classification AS classification, 
               start_date, 
               expiry_date
        FROM contacts
        LEFT JOIN contacts_classification
        ON contacts.contactid = contacts_classification.contactid";

if (!empty($classification_search)) {
    $classification_search = mysqli_real_escape_string($conn, $classification_search);
    // If it's the first condition, use WHERE, otherwise use AND
    $sql .= " WHERE contacts_classification.classification = '$classification_search'";
}


if (!empty($category_search)) {

    if (strpos($sql, "WHERE") !== false) {
        $sql .= " AND contacts.category = '$category_search'";
    } else {
        $sql .= " WHERE contacts.category ='$category_search'";
    }
}

if (!empty($from_date) && !empty($to_date)) {
    if (strpos($sql, "WHERE") !== false) {
        $sql .= " AND start_date BETWEEN '$from_date' AND '$to_date'";
    } else {
        $sql .= " WHERE start_date BETWEEN '$from_date' AND '$to_date'";
    }
}



$result = $conn-> query($sql);

$generatereport = '<table border="1" cellpadding="5">
        <tr>
            <th>Full Name</th>
            <th>Email Address</th>
            <th>Cell Phone</th>
            <th>Classification</th>
            <th>Start Date</th>
            <th>Expiry Date</th>
            <th>Category</th>
        </tr>';

while ($row = $result->fetch_assoc()) {
    $generatereport .= '<tr>
                <td>' . decryptItShared($row["Full_Name"]) . '</td>
                <td>' . decryptItShared($row["email_address"]) . '</td>
                <td>' . decryptItShared($row["cell_phone"]) . '</td>
                <td>' . $row["classification"] . '</td>
                <td>' . $row["start_date"] . '</td>
                <td>' . $row["expiry_date"] . '</td>
                <td>' . $row["category"] . '</td>
            </tr>';
            echo $row['category'];
}

$generatereport .= '</table>';

$pdf->writeHTML($generatereport, true, false, true, false, '');

$directory = __DIR__ . "/csv_files/";
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}
$filename = 'Data_Report_'.date("Y-m-d_H-i-s").'.pdf';
$file_path = $directory . $filename;

$pdf->Output($file_path, 'F');

// Store filename in session for download
$_SESSION["pdf_generated"] = "True";
$_SESSION["pdf_filename"] = $filename;

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

    $sql = "SELECT CONCAT(first_name, ' ', last_name) AS Full_Name, 
               email_address, 
               cell_phone,
               contacts.category AS category, 
               contacts_classification.classification AS classification, 
               start_date, 
               expiry_date
        FROM contacts
        LEFT JOIN contacts_classification
        ON contacts.contactid = contacts_classification.contactid";

if (!empty($classification_search)) {
    $classification_search = mysqli_real_escape_string($conn, $classification_search);
    // If it's the first condition, use WHERE, otherwise use AND
    $sql .= " WHERE contacts_classification.classification = '$classification_search'";
}


if (!empty($category_search)) {

    if (strpos($sql, "WHERE") !== false) {
        $sql .= " AND contacts.category = '$category_search'";
    } else {
        $sql .= " WHERE contacts.category ='$category_search'";
    }
}

if (!empty($from_date) && !empty($to_date)) {
    if (strpos($sql, "WHERE") !== false) {
        $sql .= " AND start_date BETWEEN '$from_date' AND '$to_date'";
    } else {
        $sql .= " WHERE start_date BETWEEN '$from_date' AND '$to_date'";
    }
}


$result = $conn-> query($sql);
while($row=$result->fetch_assoc()){
    $encrpted_name = $row["Full_Name"];
    $decrypted_name = decryptItShared($encrpted_name);
    $row["Full_Name"] = $decrypted_name;

    $encrypted_email = $row["email_address"];
    $decrypted_email = decryptItShared($encrypted_email);
    $row["email_address"] = $decrypted_email;

    $encrypted_cellphone = $row["cell_phone"];
    $decrypted_cellphone = decryptItShared($encrypted_cellphone);
    $row["cell_phone"] = $decrypted_cellphone;
    fputcsv($output, $row);
}
print_r($result);

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


function decryptItShared($q) {
    if ($q != '') {
        $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
        $q = explode(' ', rtrim($q));
        $decrypted = [];
        foreach ($q as $str) {
            try {
                if (substr(rtrim($str), -1) == '=' || strlen(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($str), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0")) < strlen($str)) {
                    $decrypted[] = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($str), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
                } else {
                    $decrypted[] = $str;
                }
            } catch (Exception $err) {
                $decrypted[] = $str;
            }
        }
        return implode(' ', $decrypted);
    }
}
?>

<script>
    document.getElementById("startDate").onfocus = function () {
    this.type = "date";
};
document.getElementById("startDate").onblur = function () {
    if (!this.value) this.type = "text";
    this.placeholder = "Select start date";
};

document.getElementById("endDate").onfocus = function () {
    this.type = "date";
};
document.getElementById("endDate").onblur = function () {
    if (!this.value) this.type = "text";
    this.placeholder = "Select end date";
};

</script>
<?php
ob_end_flush(); // Send output to browser
?>

