<?php
if (isset($_GET['selectedValue'])) {
  $selectedValue =  $_GET['selectedValue'];
  echo $selectedValue;

    if ($selectedValue === 'classificationData') {

        $selectedValue = isset($_GET['selectedValue']) ? $_GET['selectedValue'] : null;


    $nextURL = "display_classification_data.php" ;
    //  echo $nextURL;
     echo $selectedValue;
     ?>
     <!-- <script>
        let var selected_value = <?php echo $selectedValue;?>;
        console.log(selected_value);
    </script> -->
     <?php
     include($nextURL);
     

    }

    elseif ($selectedValue === 'classificationReport') {
        include("member_classification_report.php");
    } 
    else {
        echo "No valid option selected.";
    }
}
 else {
    echo "No option selected.";
}

?>


<script>

    document.getElementById('classificationForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting normally

        // Get the form data
        var formData = new FormData(this);

        // Perform an AJAX request to process the form data without leaving the page
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "display_classification_data.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Process the response from display_classification_data.php
                console.log(xhr.responseText); // You can display the response or handle it as needed
            }
        };
        xhr.send(formData);
    });
</script>
