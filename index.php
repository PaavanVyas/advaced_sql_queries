<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown Action with PHP</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>


    <select id="mySelect">
        <option value="">Select an option</option>
        <option value="classificationData">Classification Data</option>
        <option value="classificationReport">Member classification_report_</option>
    </select>
    <?php
    if (isset($_GET["selectedValue"])) {
        $selectedValue = $_GET["selectedValue"];
        echo $selectedValue;  // Output the selected value
    } else {
        echo "No selected value available.";  // Handle the case when 'selectedValue' is not set
    }
    $queryParams = $_SERVER['QUERY_STRING'];

    if (!empty($queryParams)) {
        $fileURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
        echo $queryParams;
        
        if (isset($_GET['selectedValue'])) {
            $selectedValue = $_GET['selectedValue'];
        
            // echo "You selected: " . $selectedValue;  
            if ($selectedValue == 'classificationData') {
                $_GET['selectedValue'] = $selectedValue; 
                include("display_classification_data.php");
            } elseif ($selectedValue == 'classificationReport') {
                include("member_classification_report.php");
            } else {
                echo "Invalid selection.";
            }
        } 
        else {
            echo "No selection made.";
        }


    } else {
        echo "No parameters passed.";
    }
    
$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if(isset($_POST["from_date"])){   
        $from_date = $_POST["from_date"];
    }
    if(isset($_POST["to_date"])){
        $to_date = $_POST["to_date"];
    }
    if(isset($_POST["classification_search"])){
        $classification_search = $_POST["classification_search"];
    }
    if(isset($_POST["category_search"])){
        $category_search = $_POST["category_search"];
    }
    
    
    
  }
  if(isset($_POST["from_date"])){
    echo $from_date;
  }
  else  if(isset($_POST["to_date"])){
  echo $to_date;
}
?>
    <div id="result"></div>

    <script>
        $(document).ready(function() {
            // Listen to change on the dropdown
            $('#mySelect').change(function(event) {
                const selectedValue = $(this).val();  // Get selected value

                console.log("Selected Value:", selectedValue);

                // If no selection is made, return
                if (selectedValue === "") {
                    return;
                }

                const resultDiv = $('#result');
                resultDiv.text(`You selected: ${selectedValue}`);

                // Update the URL with the selected value
                const currentUrl = window.location.href.split('?')[0];  // Get the base URL (without any query params)
                const newUrl = currentUrl + '?selectedValue=' + selectedValue;  // Append selectedValue to the URL
                window.history.pushState(null, '', newUrl);  // Update the URL without reloading the page
            });

            // Ensure that the form submission includes the selected value in the URL query parameters
            $('#classificationForm').submit(function(event) {
                const selectedValue = $('#mySelect').val();  // Get the selected value from dropdown

                // If a selection has been made
                if (selectedValue !== "") {
                    // Append the selectedValue to the form action URL
                    const actionUrl = $(this).attr('action');  // Get the form's action URL
                    const newActionUrl = actionUrl + '?selectedValue=' + selectedValue;  // Append selectedValue to the URL
                    $(this).attr('action', newActionUrl);  // Update form action URL dynamically
                }
            });
        });
    </script>

</body>
</html>
