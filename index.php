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
    $queryParams = $_SERVER['QUERY_STRING']; // Get the full query string from the URL (e.g., ?id=123&name=example)

    if (!empty($queryParams)) {
        // Construct the URL with query parameters for the included file
        $fileURL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // The full URL of index.php with the query string
    

        include("display_classification_data.php");
    } else {
        echo "No parameters passed.";
    }
    
$current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
echo $current_url;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form handling code here
    $from_date =    $_POST["from_date"];
    $to_date =      $_POST["to_date"];
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
    $('#mySelect').change(function(event) {
        const selectedValue = $(this).val();
        console.log(selectedValue);  // Log the selected value

        // Check if the selected value is empty
        if (selectedValue === "") {
            console.log("No value selected");
            event.preventDefault();  // Prevent default behavior (if needed)
        }

        // You can perform additional actions here if needed
        const resultDiv = $('#result');
        resultDiv.text(`You selected: ${selectedValue}`); // Display the selected value

        // Perform an AJAX request if a value is selected
        if (selectedValue) {
            $.ajax({
                url: 'process_selection.php', 
                type: 'GET',
                data: { selectedValue: selectedValue }, 
                success: function(response) {
                    resultDiv.html(response);  // Display the server response
                },
                error: function() {
                    resultDiv.html('An error occurred while processing your request.');  // Error handling
                }
            });
        } else {
            resultDiv.html('');  // Clear the result div if no selection is made
        }
    });
});
    </script>

</body>
</html>
