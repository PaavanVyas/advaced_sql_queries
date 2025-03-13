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

    <div id="result"></div>

    <script>
        // jQuery to handle the change event
        $('#mySelect').change(function() {
            const selectedValue = $(this).val();
            const resultDiv = $('#result');

            if (selectedValue) {
                // Make an AJAX request to the server based on the selected value
                $.ajax({
                    url: 'process_selection.php', // This will be the PHP script handling the request
                    type: 'GET',
                    data: { selectedValue: selectedValue }, // Send the selected value to the server
                    success: function(response) {
                        resultDiv.html(response); // Display the response (content) in the result div
                    },
                    error: function() {
                        resultDiv.html('An error occurred while processing your request.');
                    }
                });
            } else {
                resultDiv.html('');
            }
        });
    </script>

</body>
</html>
