<?php
  // Load Prerequisites
  use Google\Spreadsheet\DefaultServiceRequest;
  use Google\Spreadsheet\ServiceRequestFactory;

  require './vendor/autoload.php';

  // Check for Configuration Details
  if(file_exists("./audition.json")){
     $config_raw = file_get_contents('./audition.json');
     $config_data = json_decode($config_raw,true);

     // Authenticate Client
     $client = new Google_Client();
     $client->setAccessToken($config_data["api_token"]);

     if ($client->isAccessTokenExpired()) {
       $client->refreshToken($config_data["api_token"]);

       $config_data["api_token"] = $client->getAccessToken();

       $data = json_encode($config_data);
       file_put_contents('../audition.json',$data);
     }
  }
  else{
    throw new Exception('Audition config file not found.');
  }

?>
