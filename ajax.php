<?php

  /* Handles AJAX Requests for Scheduling */
  require "./config.php";

  $sheets = new Google_Service_Sheets($client);

  // Step 1 : Check Post Data
    if (isset($_POST['andrewid']) and isset($_POST['first']) and isset($_POST['last']) and isset($_POST['email']) and isset($_POST['grade']) and isset($_POST['major'])):

  // Step 2 : Get Existing User Data
    $range = "Schedule!A:K";
    $documentId = $config_data["document_id"];
    $response = $sheets->spreadsheets_values->get($documentId, $range);
    $values = $response->getValues();

    // Remove Header Row
    array_shift($values);

  // Step 3 : Find Existing Appointment Time
    $andrews = array_column($values, 5);
    $current_appt = array_search($andrews,$_POST['andrewid']);

  // Step 4 : If Informational Query, check that they have bearer token_name
    if 

?>
