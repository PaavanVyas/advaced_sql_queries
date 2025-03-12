<?php
    include './conn.php';
    $sql = "SELECT CONCAT(first_name, ' ', last_name) AS Full_Name, 
    email_address, 
    cell_phone, 
    contacts_classification.classification AS classification, 
    start_date, 
    expiry_date
    FROM contacts 
    JOIN contacts_classification 
    ON contacts.contactid = contacts_classification.contactid"; 

$result = $conn->query($sql);

?>
<html>
    <head>
        <title>Member Classification Data</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
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

while($row=$result->fetch_assoc()){
    ?>
    <tr>
        <td><?php echo decryptItShared($row["Full_Name"]);?></td>
        <td><?php echo decryptItShared($row["email_address"]);?></td>
        <td><?php echo decryptItShared($row["cell_phone"]);?>
        <td><?php echo $row["classification"];?></td>
        <td><?php echo $row["start_date"];?></td>
        <td><?php echo $row["expiry_date"];?></td>
    <?php
}
?>
</table>
</div>
</body>
</html>
<?php

function decryptItShared( $q ) {
	if($q != '') {
		$cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
		$q = explode(' ',rtrim($q));
		$decrypted = [];
		foreach($q as $str) {
			try {
				if(substr(rtrim($str), -1) == '=' || strlen(rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $str ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0")) < strlen($str)) {
					$decrypted[] = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $str ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
				} else {
					$decrypted[] = $str;
				}
			} catch(Exception $err) {
				$decrypted[] = $str;
			}
		}
		return( implode(' ',$decrypted) );
	}
}
?>