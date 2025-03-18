<?php
include './conn.php';
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

$new_query_string = http_build_query($query_params);



$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if (isset($_GET['classification_search']) && !empty($_GET['classification_search'])) {
    $classification_search = $_GET['classification_search'];
}
if (isset($_GET['category_search']) && !empty($_GET['category_search'])) {
    $category_search = $_GET['category_search'];
}
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$offset = ($page - 1) * $limit;

$total_sql = "SELECT COUNT(*) AS total FROM contacts 
              JOIN contacts_classification 
              ON contacts.contactid = contacts_classification.contactid";
if (!empty($classification_search)) {
    $classification_search = mysqli_real_escape_string($conn, $classification_search);

    $total_sql .= " WHERE contacts_classification.classification = '$classification_search'";
}

if (!empty($category_search)) {
    // If we already have a WHERE condition, add AND, else use WHERE
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
$sql .= " LIMIT $limit OFFSET $offset";


$result = $conn-> query($sql);
?>

<html>
<head>
    <title>Member Classification Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container w-100">
    <form action="" method="get">
    
    <div class="d-flex align-items-between w-100">
        <div class="d-flex align-items-between">
            <div>
                <label class="form-label">Search by classification</label>
            </div>
            <div class="w-75">
                <input type="text" class="form-label" name="classification_search" value="<?php echo isset($_GET['classification_search']) ? htmlspecialchars($_GET['classification_search']) : '' ?>" placeholder = "Search By Classification">
            </div>
        </div>
        <div class="d-flex align-items-between">
            <div>
                <label>Search by category</label>
            </div>
            <div class="w-75">
                <input type="text" class="form-label" name="category_search" value="<?php echo isset($_GET['category_search']) ? htmlspecialchars($_GET['category_search']) : '' ?>" placeholder="Search By Category">
            </div>
        </div>
    </div> 
        <div class="d-flex align-items-center w-100">
            <div>
                <label class="form-label">Search by Membership Date</label>
            </div>

            <div class="d-flex align-items-center ms-2">
                <div>
                <input type="date" class="form-label ms-2" placeholder="from_date" name="from_date" value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']):''?>">
                </div>
                <div>
                <input type="date" class="form-label ms-2" placeholder="to_date" name="to_date" value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']):''?>">
                </div>
            </div>
        </div>
        <?php
    if (isset($_GET['selectedValue'])) {
        echo "<input type='hidden' name='selectedValue' value='" . htmlspecialchars($_GET['selectedValue']) . "'>";
    }
    ?>
            <input type="submit" class="btn btn-primary">
      
        </form>
        <form method="get" action="">
    <label for="limit">Select Limit:</label><br/>
    <select name="limit" id="limit" class="form-select w-25" required>
        <?php
        // Get the current limit or default to 5
        $selected_limit = isset($_GET['limit']) ? $_GET['limit'] : 5;

        // Loop through options (5, 10, 15, etc.)
        for ($i = 5; $i <= 200; $i += 5) {
            echo "<option value='$i' " . ($selected_limit == $i ? 'selected' : '') . ">$i</option>";
        }
        ?>
    </select>

    <?php
    // Get the query parameters excluding the 'page' and 'limit'
    $query_params = $_GET;
    unset($query_params['page']);  // Remove the 'page' parameter to prevent pagination conflicts
    unset($query_params['limit']); // Remove the 'limit' parameter if it exists

    // Rebuild the query string excluding the current limit
    $new_query_string = http_build_query($query_params);
    
    // Add other hidden inputs for the remaining parameters (if any)
    foreach ($query_params as $key => $value) {
        if (!empty($value)) {
            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
    }

    // Add the selectedValue hidden input if it exists in the URL
    if (isset($_GET['selectedValue'])) {
        echo "<input type='hidden' name='selectedValue' value='" . htmlspecialchars($_GET['selectedValue']) . "'>";
    }
    ?>

    <button type="submit" class="btn btn-primary mt-2">Submit</button>
</form>


    </div>
    <?php
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
            </tr>
        <?php
        }
        ?>
    </table>
</div>

<?php
?>
<div class="container mt-4">
<nav>
    <ul class="pagination justify-content-center" style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
        <?php if ($page > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&<?php echo $new_query_string; ?>">
                    Previous
                </a>
            </li>
        <?php } ?>

        <!-- Page Number Links -->
        <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>" style="display: inline-block;">
                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo $new_query_string; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php } ?>

        <?php if ($page < $total_pages) { ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&<?php echo $new_query_string; ?>">
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
