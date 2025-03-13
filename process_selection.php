<?php
if (isset($_GET['selectedValue'])) {
    $selectedValue = $_GET['selectedValue'];

    if ($selectedValue === 'classificationData') {
        include("display_classification_data.php");
    } elseif ($selectedValue === 'classificationReport') {
        include("member_classification_report.php");
    } else {
        echo "No valid option selected.";
    }
} else {
    echo "No option selected.";
}
?>
