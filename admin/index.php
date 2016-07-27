<?php

  use Google\Spreadsheet\DefaultServiceRequest;
  use Google\Spreadsheet\ServiceRequestFactory;

  require '../vendor/autoload.php';
  require '../group/conf.php';

?>

<!DOCTYPE html>
<html lang="en-us">
  <head>
      <!-- META -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?php echo $group_name; ?> - Audition</title>

      <!-- UIKit -->
      <script src="../bower_components/jquery/dist/jquery.min.js"></script>
      <link rel="stylesheet" href="../bower_components/uikit/css/uikit.min.css" />
      <script src="../bower_components/uikit/js/uikit.min.js"></script>
      <script src="../js/urlParam.js"></script>

      <!-- Style -->
      <link rel="stylesheet" href="../css/audition.css" />

  </head>
  <body>

<?php

    // Variables To define
       $spreadsheetId = "";

    if (!file_exists("../audition.json")):


      /* Step 1 : Get URL */
        $current_url = preg_replace('/\\?.*/', '', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

      /* Step 2 : Obtain API Access */
        $client = new Google_Client();
        $client->setAuthConfigFile('client_secrets.json');
        $client->setApprovalPrompt('force');
        $client->setAccessType("offline");
        $client->addScope(Google_Service_Drive::DRIVE_FILE);
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $client->setRedirectUri($current_url);

      /* Step 3 : Set Authentication Token */
        if (! isset ($_GET['code'])):
           $auth_url = $client->createAuthUrl();
           header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        else:

        $client->authenticate($_GET['code']);

      /* Step 4 : Create Google Sheet File */
        $drive = new Google_Service_Drive($client);
        $fileMeta = new Google_Service_Drive_DriveFile(array(
                      'name' => 'IAC Audition',
                      'mimeType' => 'application/vnd.google-apps.spreadsheet')
                      );
        $createdFile = $drive->files->create($fileMeta, array('mimeType' => 'application/vnd.google-apps.spreadsheet'));

        $spreadsheetId = $createdFile->getId();

      /* Step 5 : Configure Google Sheet File */
        $sheets = new Google_Service_Sheets($client);

        // Update Sheet Name
          $requests = array();
          $requests[] = new Google_Service_Sheets_Request(array(
            'updateSheetProperties' => array(
              'properties' => array('sheetId' => 0, 'title' => 'Schedule'),
              'fields' => 'title'
            )
          ));

          $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(array(
            'requests' => $requests
          ));

          $sheets->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);

        // Update Header Row
        $headerRowRequest = new  Google_Service_Sheets_BatchUpdateValuesRequest(array(
            "valueInputOption" => "USER_ENTERED",
            "data" => array(
              "range" => "Schedule!A1:L1",
              "majorDimension" => "ROWS",
              "values" => array(array("Date","Start Time","End Time","Location","Access Code","Andrew ID","First Name","Last Name","E-Mail Address", "Grade Level", "Major","Beatbox?"))
            )
        ));

        $sheets->spreadsheets_values->batchUpdate($spreadsheetId, $headerRowRequest);

      /* Step 6 : Save Audition Conf File */
         $data = json_encode(array("api_token" => $client->getAccessToken(), "document_id" => $spreadsheetId));
         file_put_contents('../audition.json',$data);

        endif;
      else:
        $json = file_get_contents('../audition.json');
        $data = json_decode($json,true);
        $spreadsheetId = $data["document_id"];

      endif;

?>




    <!-- Step 4 : Goodbye Message -->
      <section class="audition-info">
        <div class="logo-block uk-width-medium-1-2 uk-container-center">
          <center><a href="."><img src="../group/logo.png" /></a></center>
          <div class="uk-text-bold uk-text-center">Your audition form has been configured.  You can view your audition results at the link below:</div>
        </br>
          <div class="uk-text-bold uk-text-center"><a href="<?php echo "https://docs.google.com/spreadsheets/d/".$spreadsheetId; ?>"><?php echo "https://docs.google.com/spreadsheets/d/".$spreadsheetId; ?></a></div>
       </div>
      </section>



  </body>
</html>
