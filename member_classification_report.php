<?php
include './conn.php';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_sql = "SELECT COUNT(*) AS total FROM contacts 
              JOIN contacts_classification 
              ON contacts.contactid = contacts_classification.contactid";
$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_rows = $total_row['total'];


$sql = "SELECT CONCAT(first_name, ' ', last_name) AS Full_Name, 
               email_address, 
               cell_phone, 
               contacts_classification.classification AS classification, 
               start_date, 
               expiry_date
        FROM contacts 
        JOIN contacts_classification 
        ON contacts.contactid = contacts_classification.contactid
        LIMIT $limit OFFSET $offset"; 

$result = $conn->query($sql);
?>

<html>
<head>
    <title>Member Classification Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <form method="GET">
        <label for="limit">Select Limit:</label><br/>
        <select name="limit" id="limit" class="form-select w-25" required>
            <?php 
            $selected_limit = isset($_GET['limit']) ? $_GET['limit'] : '';
            for ($i = 5; $i <= 200; $i += 5) {
                echo "<option value='$i' " . ($selected_limit == $i ? 'selected' : '') . " >$i</option>";
            }
            ?>
        </select>
        <button type="submit" class="btn btn-primary mt-2">Submit</button>
    </form>

    <table class="table table-bordered mt-2">
        <tr>
            <td>Full Name</td>
            <td>Email Address</td>
            <td>Cell Phone</td>
            <td>Classification</td>
            <td>Start Date</td>
            <td>Expiry Date</td>
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
            </tr>
        <?php
        }
        ?>
    </table>
</div>

<div class="container mt-4">
    <nav style="overflow-x: auto; max-width: 100%; padding-bottom: 10px;">
        <ul class="pagination justify-content-start" style="white-space: nowrap;">
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?page=<?php echo $page - 1; ?>&limit=<?php echo urlencode($limit); ?>">
                        Previous
                    </a>
                </li>
            <?php } ?>

            <?php 
            $total_pages = ceil($total_rows / $limit);
            for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" 
                       href="?page=<?php echo $i; ?>&limit=<?php echo urlencode($limit); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>

            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <a class="page-link" 
                       href="?page=<?php echo $page + 1; ?>&limit=<?php echo urlencode($limit); ?>">
                        Next
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>


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
