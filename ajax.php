<?php

  /* Handles AJAX Requests for Scheduling */
  require "./config.php";
  require "./group/conf.php";

  $sheets = new Google_Service_Sheets($client);

  // Step 1 : Check Post Data
    if (isset($_POST['andrewid']) and isset($_POST['first']) and isset($_POST['last']) and isset($_POST['email']) and isset($_POST['grade']) and isset($_POST['major'])){

  // Step 2 : Get Existing User Data
    $range = "Schedule!A:K";
    $documentId = $config_data["document_id"];
    $response = $sheets->spreadsheets_values->get($documentId, $range);
    $values = $response->getValues();

    // Remove Header Row
    $hrow = array_shift($values);

  // Step 3 : Find Existing Appointment Time
    $andrews = array();
    foreach ($values as $aid){
      if (isset($aid[5])){
          array_push($andrews,$aid[5]);
      }
      else{
        array_push($andrews,"");
      }
    }

    $existing = (in_array($_POST['andrewid'],$andrews)) ? array_search($_POST['andrewid'],$andrews,true) : -1;

  // Step 4 : Determine if Authorized to Request
    if (($existing == -1) or (isset($_POST['access_code']) and strcmp($_POST['access_code'],$values[$existing][4]) == 0)){

  // Step 5 : Write to Database if Needed
    if(isset($_POST['slotno'])){

      // Write to New Slot
      if(!isset($values[$_POST['slotno']][4]) or ($values[$_POST['slotno']][4] == "")){

        // Erase Old Slot
        if ($existing != -1){
          $values[$existing][4] = "";
          $values[$existing][5] = "";
          $values[$existing][6] = "";
          $values[$existing][7] = "";
          $values[$existing][8] = "";
          $values[$existing][9] = "";
          $values[$existing][10] = "";
          $values[$existing][11] = "";
        }


        // Write Data to New Slot
          $hash = (isset($_POST['access_code']) and $_POST['access_code'] != "") ? $_POST['access_code'] : bin2hex(mcrypt_create_iv(8, MCRYPT_DEV_URANDOM));
          $values[$_POST['slotno']][4] = $hash;
          $values[$_POST['slotno']][5] = $_POST['andrewid'];
          $values[$_POST['slotno']][6] = $_POST['first'];
          $values[$_POST['slotno']][7] = $_POST['last'];
          $values[$_POST['slotno']][8] = $_POST['email'];
          $values[$_POST['slotno']][9] = $_POST['grade'];
          $values[$_POST['slotno']][10] = $_POST['major'];
          $values[$_POST['slotno']][11] = $_POST['beatbox'];

        // Post Update to Google Drive
          array_unshift($values,$hrow); // Put Header Back On

          $documentUpdate = new  Google_Service_Sheets_BatchUpdateValuesRequest(array(
              "valueInputOption" => "USER_ENTERED",
              "data" => array(
                "range" => "Schedule!A:K",
                "majorDimension" => "ROWS",
                "values" => $values
              )
          ));

          $sheets->spreadsheets_values->batchUpdate($config_data["document_id"], $documentUpdate);

      }
      else{
        $error = '{"Failed":"Slot Reserved"}';
      }
    }

  // Step 6 : Return Appointment
    if (isset($_POST['slotno'])){
        $date = $values[$_POST['slotno']+1][0]." from ".$values[$_POST['slotno']+1][1]." to ".$values[$_POST['slotno']+1][2];
        $data = '{"Success":"'.$_POST['slotno'].'", "DateString" : "'.$date.'"}';

        // Step 6B : Send E-Mail
            // Fetching Values from URL.
              $email = $_POST['email'];
              $email = filter_var($email, FILTER_SANITIZE_EMAIL); // Sanitizing E-mail.

            // After sanitization Validation is performed
              $subject = $group_name." A Cappella Audition";

            // To send HTML mail, the Content-type header must be set.
              $headers = 'MIME-Version: 1.0' . "\r\n";
              $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
              $headers .= 'From:contact@cmuacappella.org\r\n'; // Sender's Email
              $headers .= 'Reply-to:' . $reply_email . "\r\n"; // Sender's Email

              $template = '<div style="padding:50px;"><br/>'
              . $email_message  . '<br/>'
              . $url_root . $hash .'<br/>'
              . '<br/>'
              . '</div>';

              $sendmessage = "<div>" . $template . "</div>";

            // Message lines should not exceed 70 characters (PHP rule), so wrap it.
              $sendmessage = wordwrap($sendmessage, 70);

            // Send mail by PHP Mail Function.
              mail($email, $subject, $sendmessage, $headers,'-fcontact@cmuacappella.org');

    }
    else{
      if ($existing == 1)
        $data = '{"Query":"None Found"}';
      else
        $data = '{"Query":"'.$existing.'"}';
    }

  // Step 7 : Handle Errors
    } else{
      $error = '{"Failed":"Unauthorized.  Try using the link in your email."}';
    }
    } else {
      $error = '{"Failed":"Malformed request. "}';
    }


  // Step 8 : Output Data
    if (isset($error)) {
      echo $error;
    }
    else {
      echo $data;
    }

?>
