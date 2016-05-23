<!DOCTYPE html>
<html lang="en-us">
  <head>
      <!-- META -->
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title></title>

      <!-- UIKit -->
      <script src="bower_components/jquery/dist/jquery.min.js"></script>
      <link rel="stylesheet" href="bower_components/uikit/css/uikit.min.css" />
      <script src="bower_components/uikit/js/uikit.min.js"></script>

      <!-- Style -->
      <link rel="stylesheet" href="css/audition.css"

  </head>
  <body>

   <!-- Step 1 : Autofill Data from AndrewID -->
     <section class="audition-autofill">
       <div class="logo-block uk-width-medium-1-2 uk-container-center">
         <center><a href="."><img src="img/logo.png" /></a></center>
         <form id="autofill-andrew" class=" uk-form">
            <div class="uk-form-row"><input type="text" placeholder="AndrewID"></div>
        </form>
      </div>
     </section>
     <script>
        $("#autofill-andrew").submit(function(e){
            var andrewid = $("#autofill-andrew input[type=text]").val();
            if (andrewid){

              // Get From ScottyLabs API
              $.getJSON("http://apis.scottylabs.org/directory/v1/andrewID/"+andrewid,function(result){

                console.log(result);


                // AutoFill into Form
                $("#audition-form input[name=andrewid]").val(result.andrewID);
                $("#audition-form input[name=first]").val(result.first_name);
                $("#audition-form input[name=last]").val(result.last_name);
                $("#audition-form input[name=email]").val(result.preferred_email);
                $("#audition-form input[name=grade]").val(result.student_class);
                $("#audition-form input[name=major]").val(result.department);

                // Transition Away
                $(".audition-autofill").fadeOut();
                $(".audition-form").fadeIn();

              });
            }
            e.preventDefault();
        })
     </script>

    <!-- Step 2 : Display Form Data -->
    <section class="audition-form" style="display:none;">
      <div class="logo-block uk-width-medium-1-2 uk-container-center">
        <center><a href="."><img src="img/logo.png" /></a></center>
        <form id="audition-form" class=" uk-form">
           <div class="uk-form-row"><input name="andrewid" type="text" placeholder="AndrewID" style="width:100%;"></div>
           <div class="uk-form-row"><input name="first" type="text" placeholder="First Name" style="width:48%;float: left;"><input name="last" type="text" placeholder="Last Name" style="width:48%; float:right;"></div>
           <div class="uk-form-row"><input name="email" type="text" placeholder="E-Mail" style="width:100%;"></div>
           <div class="uk-form-row"><center><button class="uk-button uk-button-large">Schedule an Audition</button></center></div>
           <input name="grade" type="hidden">
           <input name="major" type="hidden">
       </form>
     </div>
    </section>
    <script>
       $("#audition-form").submit(function(e){
           $(".audition-form").fadeOut();
           $(".audition-schedule").fadeIn();
           e.preventDefault();
       })
    </script>


    <!-- Step 3 : Display Audition Schedule -->
    <section class="audition-schedule" style="display:none;">
        <div class="audition-view-wrapper">
           <ul class="audition-view">
            <?php

                  require "./config.php";

                  // Get Calendar Listing
                  $sheets = new Google_Service_Sheets($client);
                  $range = "Schedule!A:E";
                  $documentId = $config_data["document_id"];
                  $response = $sheets->spreadsheets_values->get($documentId, $range);
                  $values = $response->getValues();

                  // Remove Header Row
                  array_shift($values);

                  // Iterate Through Schedule
                  $current_day = new DateTime("2016-05-22");
                  foreach ($values as $slot => $row){
                     $day = new DateTime($row[0]);

                  if ($current_day != $day and $current_day != new DateTime("2016-05-22")){
                     echo "</div>";
                  }

                  if ($current_day != $day):
                      $current_day = $day;      ?>

               <div class="audition-day">
                 <div class="audition-day-label">
                   <month><?php echo date_format($day, 'F'); ?></month>
                   <number><?php echo date_format($day, 'd'); ?></number>
                   <day><?php echo date_format($day, 'l'); ?></day>
                 </div>

             <?php endif; ?>
                 <div class="audition-slot" slotno="1">
                   <time><?php echo $row[1]; ?> - <?php echo $row[2]; ?></time>
                   <room><?php echo $row[3]; ?></room>
                   <up slot="<?php echo $slot; ?>">Sign Up</up>
                 </div>

             <?php
                  }
             ?>
           </div>
         </ul>
       </div>
    </section>

    <script>
      $(document).ready(function(){
        $("up").on('click',function(e){
            slot = $(this).parent(".audition-slot");

            // Change Style
              slot.addClass("loading");
              $(this).text('Processing...');

           // Request Slot From Server
            console.log(slot.attr("slotno"));

            $(".audition-schedule").fadeOut();
            $(".audition-goodbye").fadeIn();
        })
      })
    </script>

    <!-- Step 4 : Goodbye Message -->
      <section class="audition-goodbye" style="display:none;">
        <div class="logo-block uk-width-medium-1-2 uk-container-center">
          <center><a href="."><img src="img/logo.png" /></a></center>
          <div class="uk-text-bold uk-text-center"> You have been scheduled for an audition on </div>
          <div class="uk-text-center uk-text-large" style="font-size:36px; line-height:60px;">July 4th, 2016 at 10:00am</div>
          <div class="uk-text-bold">You have been emailed a link allowing you to cancel or reschedule your audition.  If you have any questions, feel free to ask.</div>
       </div>
      </section>



  </body>
</html>
