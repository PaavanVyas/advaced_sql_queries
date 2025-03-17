<?php
if (isset($_GET['selectedValue'])) {
    $selectedValue = $_GET['selectedValue'];
    echo $selectedValue;

    if ($selectedValue == 'classificationData') {
        $nextURL = "display_classification_data.php"; // Path for classification data
        // Include the file only if not an AJAX request
        if (!isset($_GET['ajax'])) {
            include($nextURL);
        }
    } elseif ($selectedValue == 'classificationReport') {
        $nextURL = "member_classification_report.php";
        // Include the file only if not an AJAX request
        if (!isset($_GET['ajax'])) {
            include($nextURL);
        }
    } else {
        echo "No valid option selected.";
    }
} else {
    echo "No option selected.";
}
?>

<script>
    document.getElementById('classificationForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the form from submitting normally

    var formData = new FormData(this);
    var urldata = "<?php echo $nextURL; ?>"; // Using PHP to output the URL

    // Append a query parameter to mark this as an AJAX request
    urldata += "?ajax=true";

    var xhr = new XMLHttpRequest();
    xhr.open("POST", urldata, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log(xhr.responseText);
            document.getElementById('contentContainer').innerHTML = xhr.responseText;
        }
    };

    // Send the form data via AJAX
    xhr.send(formData);
});

</script>
