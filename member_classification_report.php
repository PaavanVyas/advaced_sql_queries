<?php
include './conn.php';
$query_string = $_SERVER['QUERY_STRING'];
$current_query = $_SERVER['QUERY_STRING'];
$actual_link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // use REQUEST_URI instead of PHP_SELF
// echo $actual_link;
$includedFileURL = 'http://' . $_SERVER['HTTP_HOST'] . '/display_classification_data.php';
echo $includedFileURL;

if (!empty($_GET)) { 

  } else {

  }

if(isset($_GET['url'])){
    $query_string = $_GET['url'];
}

parse_str($query_string, $query_params);


unset($query_params['page']);

$new_query_string = http_build_query($query_params);


$current_file = basename($_SERVER['PHP_SELF']);

$current_query = $_SERVER['QUERY_STRING'];

if (empty($current_query)) {
    $current_query = '';
    echo $current_query;
}
// echo $current_file;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 5;
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    echo $page;
    
    $classification_search = isset($_POST['classification_search']) ? $_POST['classification_search'] : '';
    $category_search = isset($_POST['category_search']) ? $_POST['category_search'] : '';
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
    $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
    
    $query_string = http_build_query([
        'classification_search' => $classification_search,
        'category_search' => $category_search,
        'limit' => $limit,
        'page' => $page
    ]);
    
    $action_url = $current_file . '?' . $query_string;
    
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
                   expiry_date
            FROM contacts
            LEFT JOIN contacts_classification
            ON contacts.contactid = contacts_classification.contactid";
    
    if (!empty($classification_search)) {
        $classification_search = mysqli_real_escape_string($conn, $classification_search);
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
    }
    ?>

<html>
<head>
    <title>Member Classification Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div>
        <form action="<?php echo isset($action_url) ? $action_url : $current_file; ?>" method="POST">
            <label>Search by classification</label>
            <input type="text" name="classification_search" value="<?php echo isset($_GET['classification_search']) ? htmlspecialchars($_GET['classification_search']) : '' ?>">
            
            <label>Search by category</label>
            <input type="text" name="category_search" value="<?php echo isset($_GET['category_search']) ? htmlspecialchars($_GET['category_search']) : '' ?>">
            
            <label>Search by Membership Date</label>
            <input type="date" placeholder="from_date" name="from_date" value="<?php echo isset($_GET['from_date']) ? htmlspecialchars($_GET['from_date']):''?>">
            <input type="date" placeholder="to_date" name="to_date" value="<?php echo isset($_GET['to_date']) ? htmlspecialchars($_GET['to_date']):''?>">
        <input type="submit">
        </form>
        <form method="POST" action="<?php echo isset($action_url) ? $action_url : $current_file; ?>">
    <label for="limit">Select Limit:</label><br/>
    <select name="limit" id="limit" class="form-select w-25" required>
        <?php
        // Get the current limit or default to 5
        $selected_limit = isset($_GET['limit']) ? $_GET['limit'] : 5;

        echo "<option value='' disabled>Select a limit</option>"; 
        for ($i = 5; $i <= 200; $i += 5) {
            echo "<option value='$i' " . ($selected_limit == $i ? 'selected' : '') . ">$i</option>";
        }
        ?>
    </select>


    <?php
    
    $query_params = $_GET;
    unset($query_params['limit']); 

    foreach ($query_params as $key => $value) {
        if (!empty($value)) {
            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
    }
    ?>

    <button type="submit" class="btn btn-primary mt-2">Submit</button>
</form>

    </div>
    <?php
    if (count($_GET)>1) { 
    ?>
    <table class="table table-bordered mt-2">
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
    <form id="pagination-form" method="POST" action="<?php echo isset($action_url) ? $action_url : $current_file; ?>">
        <!-- Hidden fields to carry the current search data -->
        <input type="hidden" name="classification_search" value="<?php echo isset($classification_search) ? htmlspecialchars($classification_search) : ''; ?>">
        <input type="hidden" name="category_search" value="<?php echo isset($category_search) ? htmlspecialchars($category_search) : ''; ?>">
        <input type="hidden" name="from_date" value="<?php echo isset($from_date) ? htmlspecialchars($from_date) : ''; ?>">
        <input type="hidden" name="to_date" value="<?php echo isset($to_date) ? htmlspecialchars($to_date) : ''; ?>">
        <input type="hidden" name="limit" value="<?php echo isset($limit) ? $limit : 5; ?>"> 
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1) { ?>
                    <li class="page-item">
                        <button type="submit" name="page" value="<?php echo $page - 1; ?>" class="page-link">
                            Previous
                        </button>
                    </li>
                <?php } ?>

                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <button type="submit" name="page" value="<?php echo $i; ?>" class="page-link">
                            <?php echo $i; 
                            $query_string = http_build_query([
                                'classification_search' => $classification_search,
                                'category_search' => $category_search,
                                'limit' => $limit,
                                'page' => $i
                            ]);
                            
                            $action_url = $current_file . '?' . $query_string;?>

                        </button>
                    </li>
                <?php } ?>

                <?php if ($page < $total_pages) { ?>
                    <li class="page-item">
                        <button type="submit" name="page" value="<?php echo $page + 1; ?>" class="page-link">
                            Next
                        </button>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </form>
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
