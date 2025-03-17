<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown Action with PHP</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container p-3 mt-2">
    <div class="container d-flex justify-content-between mb-2">
        <div>
            <label>Report:</label>
        </div>
        <div class="w-75">
    <select id="mySelect" class="form-select w-100">
        <?php
        // Check if selectedValue is present in the URL
        if (isset($_GET["selectedValue"])) {
            $selectedValue = $_GET["selectedValue"];
            ?>
            <option value="">Select an option</option>
            <option value="Classification Counts Report" <?php echo ($selectedValue == "Classification Counts Report") ? "selected" : ""; ?>>Classification Counts Report</option>
            <option value="Member Classification Report" <?php echo ($selectedValue == "Member Classification Report") ? "selected" : ""; ?>>Member Classification Report</option>
            <?php
        } else {
            ?>
            <option value="">Select an option</option>
            <option value="Classification Counts Report">Classification Counts Report</option>
            <option value="Member Classification Report">Member Classification Report</option>
            <?php
        }
        ?>
    </select>
</div>
    </div>
    <?php
  
    $queryParams = $_SERVER['QUERY_STRING'];

    if (!empty($queryParams)) {
        $fileURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 
        
        if (isset($_GET['selectedValue'])) {
            $selectedValue = $_GET['selectedValue'];
        
            // echo "You selected: " . $selectedValue;  
            if ($selectedValue == 'Classification Counts Report') {
                $_GET['selectedValue'] = $selectedValue; 
                include("display_classification_data.php");
            } elseif ($selectedValue == 'Member Classification Report') {
                include("member_classification_report.php");
            } else {
                echo "Invalid selection.";
            }
        } 
        else {
            echo "No selection made.";
        }


    } else {
       
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

</body>
</html>

<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const selectedValue = urlParams.get('selectedValue');

        // If a value is selected, set it in the dropdown and update the result div
        if (selectedValue) {
            $('#mySelect').val(selectedValue); // Set the dropdown value
           
        }

        // Listen to change on the dropdown
        $('#mySelect').change(function(event) {
            const selectedValue = $(this).val();  // Get selected value
            console.log("Selected Value:", selectedValue);

            // If no selection is made, return
            if (selectedValue === "") {
                return;
            }

            const resultDiv = $('#result');

            const currentUrl = window.location.href.split('?')[0];  // Get the base URL (without any query params)
            const newUrl = currentUrl + '?selectedValue=' + selectedValue;  // Append selectedValue to the URL

            // Reload the page with the updated URL
            window.location.href = newUrl;  // Reload the page with the new URL
        });


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
