<?php



/*



Plugin Name: WP-Predict



Description: Allow users the ability to predict outcomes for specific posts



Author: Pootlepress



Version: 1.0



*/







//error_reporting(E_ALL);



//ini_set('display_errors', '1');











add_action('init', 'predict_textdomain');



register_activation_hook(__FILE__, 'wppreInstall');

        global $wpdb; 		$cur_time = time();        $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictActive = 0 WHERE predictEndDatetime < ".$cur_time ); 













function wppreInstall()



{



        // Get Db Object and required functions for install from core upgrade functions file



        global $wpdb;







       // include_once ABSPATH . '/wp-admin/upgrade-functions.php'; // old







                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');







        $table_names[] = $wpdb->prefix . "wpp_predict_entries";



        $table_names[] = $wpdb->prefix . "wpp_predict_options";



        $table_names[] = $wpdb->prefix . "wpp_predict_votes";











        $create_tables[$wpdb->prefix . "wpp_predict_entries"] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wpp_predict_entries" . " (



          predictId INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,



          predictPostId INT(10) NOT NULL,



          predictHeaderText TEXT NOT NULL,



                                                                                                                                                predictOptionsCount INT(3) NOT NULL,



                                                                                                                                                point INT(3) NOT NULL ,



          predictActive TINYINT(1) NOT NULL,



                                                                                                                                                predictStartDatetime INT(11) NOT NULL,



                                                                                                                                                predictEndDatetime INT(11) NOT NULL)";







        $create_tables[$wpdb->prefix . "wpp_predict_options"] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wpp_predict_options" . " (



          predictOptionId INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,



          predictEntryId INT(10) NOT NULL,



          predictOptions TEXT NOT NULL,



          predictFinalOutcomeOption INT(1) NOT NULL)";







        $create_tables[$wpdb->prefix . "wpp_predict_votes"] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "wpp_predict_votes" . " (



          predictVoteId INT(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,



          predictEntryId INT(10) NOT NULL,



          predictUserId INT(10) NOT NULL,



          predictSelectedOption INT(1) NOT NULL)";







        foreach ($table_names as $table_name)



        {



                if ( $wpdb->get_var ( "show tables like '$table_name'" ) != $table_name )



                {



                                echo $table_name;



                                echo $create_tables[$table_name];



                        dbDelta( $create_tables[$table_name] );



                }



        }











}











function predict_textdomain()



                {



                        load_plugin_textdomain('wp_predict', 'wp-content/plugins/tomorrowtimes');



                }



















function wppDisplayAdminPage()



{



        // Get Db Object //



        global $wpdb;







        // Handle Post Actions //



        if ( isset( $_POST['postAction'] ) )



        {



                // Setup Error String //



                $errorString = "";







                // Create Switch Cases for different Post Actions //



                switch ( $_POST['postAction'] )



                {



                        // Add New Vote Entry //



                        case "createVote":



 // Get Post Values //



 $headerText = $_POST['headerText'];



 $postId = $_POST['postId'];



                $voteOptionsCount = $_POST['voteOptionsCount'];



                $point = $_POST['point'];



                $days = $_POST['days'];



                $deactivationDate = time() + ($days * 24 * 60 * 60);











 // Make sure the final outcome is not NULL //



 if ( $headerText != "" && strlen( trim( $headerText ) ) > 0 && $postId != "NULL" && $voteOptionsCount > 0 )



 {



         // Update the database //



         $dbResult = @$wpdb->query ( "INSERT INTO " . $wpdb->prefix . "wpp_predict_entries (predictPostId, predictHeaderText, predictOptionsCount, predictActive, predictStartDatetime, predictEndDatetime, point) VALUES(" . $postId . ", '" . mysql_escape_string( $headerText ) . "', " . $voteOptionsCount . ", 1, " . time() . ", " . $deactivationDate . ", ". $point .")" );







         if ( $dbResult )



                 $predictEntryId = $wpdb->insert_id;



         else



                 $errorString = "Vote insertion into database failed!!!";



 }



 else



 {



         // Set Error Strings //



         if ( $headerText == "" || strlen( trim( $headerText ) ) == 0 )



                 $errorString .= "<br />Header Text Cannot Be Empty!!!";







         if ( $postId == "NULL" )



                 $errorString .= "<br />Please select a valid Post from the dropdown!!!";



 }







 break;







                        case "addOptions":



 // Get Post Values //



 $voteOptionsCount = $_POST['voteOptionsCount'];



 $predictEntryId = $_POST['predictEntryId'];







                // Create Options String //



                $optionString = "";















                for ( $i=1; $i<=$voteOptionsCount; $i++ )



                {



                    if ( $_POST['option_'.$i] == "" || strlen( trim( $_POST['option_'.$i] ) ) == 0 )



                        $errorString .= "<br />Option Value Cannot Be Empty !!!";







                    $optionString .= $_POST['option_'.$i] . "||";







                }







                // Temp option string //



                $tempOptionString = str_replace( "|", "", $optionString );







 // Make sure the final outcome is not NULL //



 if ( $optionString != "" && strlen( trim( $optionString ) ) > 0 && $tempOptionString != "" && strlen( trim( $tempOptionString ) ) > 0 && $errorString == "" )



 {



         // Update the database //







         $dbResult = @$wpdb->query ( "INSERT INTO " . $wpdb->prefix . "wpp_predict_options (predictEntryId, predictOptions) VALUES(" . $predictEntryId . ", '" . mysql_escape_string( substr( $optionString, 0, -2 ) ) . "')" );



 }







 break;







                        case "editEntry":



 // Get Post Values //



 $headerText = $_POST['headerText'];



 $voteOptionsCount = $_POST['voteOptionsCount'];



 $predictEntryId = $_POST['predictEntryId'];







                // Create Options String //



                $optionString = "";











                for ( $i=1; $i<=$voteOptionsCount; $i++ )



                {



                    if ( $_POST['option_'.$i] == "" || strlen( trim( $_POST['option_'.$i] ) ) == 0 )



                        $errorString .= "<br />Option Value Cannot Be Empty!!!";







                    $optionString .= $_POST['option_'.$i] . "||";



                }







                // Temp option string //



                $tempOptionString = str_replace( "|", "", $optionString );







 // Make sure the final outcome is not NULL //



 if ( $headerText != "" && strlen( trim( $headerText ) ) > 0 && $optionString != "" && strlen( trim( $optionString ) ) > 0 && $errorString == "" )



 {



         // Update the database //



         $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictHeaderText='" . mysql_escape_string( $headerText ) . "' WHERE predictId=" . $predictEntryId );







         // Update the database //



         $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_options SET predictOptions='" . mysql_escape_string( substr( $optionString, 0, -2 ) ) . "' WHERE predictEntryId=" . $predictEntryId );



 }



 else



 {



         // Set Error Strings //



         if ( $headerText == "" || strlen( trim( $headerText ) ) == 0 )



                 $errorString .= "<br />Header Text Cannot Be Empty!!!";



 }







 break;







                        case "setOutcome":



 // Get Post Values //



 $finalOutcome = $_POST['finalOutcome'];



 $predictEntryId = $_POST['predictEntryId'];







 // Make sure the final outcome is not NULL //



 if ( $finalOutcome != "NULL" )



 {



         // Update the database //



         $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_options SET predictFinalOutcomeOption=" . $finalOutcome . " WHERE predictEntryId=" . $predictEntryId );







         $current_time = strtotime("now");







         $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictEndDatetime=" . $current_time . " WHERE predictId=" . $predictEntryId );



 }



 else



         $errorString = "Please Select Outcome for Vote!!!";







 break;



                }



        }







        // Handle Request Actions //



        if ( isset ( $_REQUEST["action"] ) )



        {



                 if ( $_REQUEST["action"] == "deactivate" )



                {



                        $predictEntryId = intval ( $_REQUEST["pid"] );







                        $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictActive=0, predictEndDateTime=" . time() . " WHERE predictId=" . $predictEntryId );



                        //$dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictActive=0 WHERE predictId=" . $predictEntryId );



                }



        }







        // Display Post Action Status Messages //



         if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "createVote" && $errorString == "" )



        {



                echo '<div id="message" class="updated fade"><p>New Vote Created <strong>Successfully</strong>.</p></div>';



        }



        else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "createVote" && $errorString != "" )



        {



                echo '<div class="updated error"><p>Vote <strong>NOT</strong> Created. Please correct the following errors!!<br /><br />' . $errorString . "</p></div>";



        }



        // Add Options Status Results //



         else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "addOptions" && $errorString == "" )



        {



                echo '<div id="message" class="updated fade"><p>Vote Options Added <strong>Successfully</strong>.</p></div>';



        }



        else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "addOptions" && $errorString != "" )



        {



                echo '<div class="updated error"><p>Vote Options <strong>NOT</strong> Added. Please correct the following errors!!<br /><br />' . $errorString . "</p></div>";



        }



        // Edit Vote Status Results //



         else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "editEntry" && $errorString == "" )



        {



                echo '<div id="message" class="updated fade"><p>Vote Details Updated <strong>Successfully</strong>.</p></div>';



        }



        else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "editEntry" && $errorString != "" )



        {



                echo '<div class="updated error"><p>Vote Details <strong>NOT</strong> Updated. Please correct the following errors!!<br /><br />' . $errorString . "</p></div>";



        }



        // Set Outcome Status Results //



         else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "setOutcome" && $errorString == "" )



        {



                echo '<div id="message" class="updated fade"><p>Final outcome set for vote <strong>Successfully</strong>.</p></div>';



        }



        else if ( isset( $_POST["postAction"] ) && $_POST["postAction"] == "setOutcome" && $errorString != "" )



        {



                echo '<div class="updated error"><p>Outcome <strong>NOT</strong> set. Please correct the following errors!!<br /><br />' . $errorString . "</p></div>";



        }







        // REQUEST Action for deactivating prediction vote //



        if ( isset( $_REQUEST["action"] ) && $_REQUEST["action"] == "deactivate" && ( $dbResult ) )



        {



                echo '<div id="message" class="updated fade"><p>Prediction Vote Deactivated <strong>Successfully</strong>. No further voting will be allowed!!</p></div>';



        }



        else if ( isset( $_REQUEST["action"] ) && $_REQUEST["action"] == "deactivated" && ( !$dbResult ) )



        {



                echo '<div class="updated error"><p>Prediction Vote Could <strong>NOT</strong> be Deactivated.</p></div>';



        }







        // Display Page Content //



        if ( isset( $_REQUEST['do'] ) && $_REQUEST['do'] == "addOptions" )



        {



                // Get the predict entry options to display //



                $predictResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictId=" . $predictEntryId, OBJECT );



                $predictEntry = $predictResults[0];







                // Show the list of already created prediction entries //



                $display .= '<div class="wrap">



                 <h2>Set Options for Prediction Vote</h2>







                 <form name="wppopts" id="wppopts" action="" method="post">







                         <fieldset class="options">







  <legend>Vote Options</legend>







  <table class="optiontable">







          <tr>



                  <th scope="row">Vote Header Text:</th>



                  <td>' . stripslashes( $predictEntry->predictHeaderText ) . '</td>



          </tr>';







        // Loop through the number of options requested and add a field for each of them //



        for ( $i=1; $i<=$voteOptionsCount; $i++ )



        {



            $display .= '           <tr>



                  <th scope="row">Option ' . $i . ':</th>



                  <td><input type="text"name="option_' . $i . '" size="50" value="" /></td>



          </tr>';



        }







        $display .= '           </table>







                         </fieldset>







                         <br style="clear: both;" />







                         <p class="submit">







  <input type="hidden" name="predictEntryId" value="' . $predictEntry->predictId . '" />



 <input type="hidden" name="voteOptionsCount" value="' . $voteOptionsCount . '" />



  <input type="hidden" name="postAction" value="addOptions" />



  <input type="submit" name="submit" value="Add Vote Options &raquo;" />







                         </p>







                 </form>







         </div>';



        }



        else if ( isset( $_REQUEST['do'] ) && $_REQUEST['do'] == "editEntry" )



        {



                // Set the Entry ID //



                $predictEntryId = $_REQUEST['pid'];







                // Get the predict entry options to display //



                $predictResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictId=" . $predictEntryId, OBJECT );



                $predictEntry = $predictResults[0];







                // Get the predict entry options to display //



                $predictResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $predictEntryId, OBJECT );



              $predictOptions = $predictResults[0];







        $optionsArray = explode( "||",stripslashes( $predictOptions->predictOptions ) );







                // Show the list of already created prediction entries //



                $display .= '<div class="wrap">



                 <h2>Edit Vote Properties</h2>







                 <form name="wppopts" id="wppopts" action="" method="post">







                         <fieldset class="options">







  <legend>Edit Vote</legend>







  <table class="optiontable">







          <tr>



                  <th scope="row">Vote Header Text:</th>



                  <td><input type="text"name="headerText" size="50" value="' . stripslashes( $predictEntry->predictHeaderText ) . '" /></td>



          </tr>';







        // Loop through all the options //



        for ( $i=1; $i<=$predictEntry->predictOptionsCount; $i++ )



        {



            $display .= '           <tr>



                  <th scope="row">Option ' . $i . ':</th>



                  <td><input type="text"name="option_' . $i . '" size="50" value="' . $optionsArray[$i-1] . '" /></td>



          </tr>';



        }







        $display .= '           </table>







                         </fieldset>







                         <br style="clear: both;" />







                         <p class="submit">







  <input type="hidden" name="predictEntryId" value="' . $predictEntry->predictId . '" />



 <input type="hidden" name="voteOptionsCount" value="' . $predictEntry->predictOptionsCount . '" />



  <input type="hidden" name="postAction" value="editEntry" />



  <input type="submit" name="submit" value="Update Vote &raquo;" />







                         </p>







                 </form>







         </div>';



        }



        else if ( isset( $_REQUEST['do'] ) && $_REQUEST['do'] == "setOutcome" )



        {



                // Set the Entry ID //



                $predictEntryId = $_REQUEST['pid'];







                // Get the predict entry options to display //



                $predictResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $predictEntryId, OBJECT );



                $predictOptions = $predictResults[0];



        $optionsArray = explode( "||", stripslashes( $predictOptions->predictOptions ) );







                // Show the list of already created prediction entries //



                $display .= '<div class="wrap">



                 <h2>Set Final Outcome for Prediction Vote</h2>







                 <form name="wppopts" id="wppopts" action="" method="post">







                         <fieldset class="options">







  <legend>Vote Options</legend>







  <table class="optiontable">';







        // Loop through all the options //



        foreach ( $optionsArray as $i=>$optionText )



        {



            $display .= '           <tr>



                  <th scope="row">Option ' . ($i+1) . ':</th>



                  <td>' . $optionText . '</td>



          </tr>';



        }







        $display .= '               <tr>



                  <th scope="row">Final Outcome:</th>



                  <td>



                          <select name="finalOutcome">



   <option value="NULL">Select Outcome</option>';







        // Loop through all the options //



        foreach ( $optionsArray as $i=>$optionText )



        {



            $display .= '                       <option value="' . ($i+1) . '">Option ' . ($i+1) . '</option>';



        }







                $display .= '                       </select>



                  </td>



          </tr>







  </table>







                         </fieldset>







                         <br style="clear: both;" />







                         <p class="submit">







  <input type="hidden" name="predictEntryId" value="' . $predictOptions->predictEntryId . '" />



  <input type="hidden" name="postAction" value="setOutcome" />



  <input type="submit" name="submit" value="Set Vote Outcome &raquo;" />







                         </p>







                 </form>







         </div>';



        }



        else



        {



                // Get the posts to display //



                $postResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type='post' ORDER BY post_title ASC", OBJECT );







                // Setup the drop down option for the post //



                $postOptions = '<option value="NULL">Select Post</option>';







                // Create further dropdown options only if we have posts //



                if ( !empty( $postResults ) )



                {



                        // Loop through each post and add the post as an option //



                        foreach ( $postResults as $postEntry )



                        {



 $postOptions = $postOptions . '<option value="' . $postEntry->ID . '">' . stripslashes( $postEntry->post_title ) . '</option>';



                        }



                }







                // Form to create new prediction vote entry //



                $display .= '<div class="wrap">



                 <h2>Create New Prediction Vote</h2>







                 <form name="wppopts" id="wppopts" action="?page=wp-predict.php&do=addOptions" method="post">







                         <fieldset class="options">







  <legend>New Vote</legend>







  <table class="optiontable">







          <tr>



                  <th scope="row">Vote Post:</th>



                  <td>



                          <select name="postId">' . $postOptions . '</select></td>



          </tr>







          <tr>



                  <th scope="row">Vote Header Text:</th>



                  <td><input type="text"name="headerText" size="50" value="" /></td>



          </tr>







          <tr>



                  <th scope="row">Point:</th>



                  <td><input type="text"name="point" size="3" value="" /></td>



          </tr>







          <tr>



                  <th scope="row">After how many days you want the poll to be deactivated automatically:</th>



                  <td><input type="text"name="days" size="3" value="" /></td>



          </tr>







          <tr>



                  <th scope="row">Number Of Vote Options:</th>



                  <td><input type="text"name="voteOptionsCount" size="3" value="" /></td>



          </tr>







  </table>







                         </fieldset>







                         <br style="clear: both;" />







                         <p class="submit">








  <input type="hidden" name="postAction" value="createVote" />



  <input type="submit" name="submit" value="Create Vote &raquo;" />







                         </p>







                 </form>







         </div>';



        }







        // Show the list of already created prediction entries //



    $display .= '<div class="wrap">



         <script type="text/javascript">



                 function confirmDelete (message)



                 {



                         if ( confirm( message ) )



                         {



  return true;



                         }



                         else



                         {



  return false;



                         }



                 }



         </script>



         <h2>Current Prediction Votes</h2>







         <table class="widefat">







                 <thead>



                         <tr>



  <th scope="col" align="left">ID</th>



  <th scope="col" align="left">Post</th>



  <th scope="col" align="left">Header</th>



  <th scope="col" align="left">Status</th>



  <th scope="col"></th>



  <th scope="col"></th>



  <th scope="col" align="left">Deactivation Date</th>



  <th scope="col"></th>



                         </tr>



                 </thead>







                 <tbody id="the-list">';







    $row = "alternate";







    $currentPredicts = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries ORDER BY predictId DESC", OBJECT );







    if ( !empty( $currentPredicts ) )



    {



                foreach ( $currentPredicts as $predictEntry )



                {



                        // Get the post title from the post ID //



                        $postResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "posts WHERE ID=" . $predictEntry->predictPostId, OBJECT );



                        // Make sure the post is still valid and present //



                        if ( !empty( $postResults ) )



                        {



 $postRecord = $postResults[0];



 $postTitle = $postRecord->post_title;



                        }



                        else



 $postTitle = "<strong>Post No Longer Exists in Database!!!!</strong>";











                        $outcomeResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $predictEntry->predictId, OBJECT );



                        //print_r($outcomeResults);



                        if ($outcomeResults[0]->predictFinalOutcomeOption != 0)



                            $fontcolor = "red";



                        else



                            $fontcolor = "black";















                        // Add record row to the table //



                        $display .= '<tr class="' . $row . '">



                         <td scope="row" align="left">' . $predictEntry->predictId . '</td>



                         <td>' . stripslashes ( $postTitle ) . '</td>



                         <td><font color="'.$fontcolor.'">' . stripslashes ( $predictEntry->predictHeaderText ) . '</font></td>



                         <td>' . ( ( $predictEntry->predictActive == 1 ) ? "Active" : "Expired" ) . '</td>



                         <td><a href="?page=wp-predict.php&amp;do=editEntry&amp;pid=' . $predictEntry->predictId . '" class="edit">Edit</a></td>



                         <td><a href="?page=wp-predict.php&amp;do=setOutcome&amp;pid=' . $predictEntry->predictId . '" class="edit">Set Outcome</a></td>



                         <td>' . date("F j, Y", $predictEntry->predictEndDatetime) . '</td>



                         <td>' . ( ( $predictEntry->predictActive == 0 ) ? "" : '<a href="?page=wp-predict.php&amp;action=deactivate&amp;pid=' . $predictEntry->predictId . '" class="delete" onclick="return confirmDelete(' . "'" . 'You are about to deactivate this vote. No one will be able to vote if you do this. \n&quot;OK&quot; to deactivate, &quot;Cancel&quot; to stop.' . "'" . ');">Deactivate</a>' ) . '</td>



                 </tr>';







                        // Toggle Row Class //



                        if ( $row == "alternate" )



 $row = "";



                        else



 $row = "alternate";



                }



    }







    $display .= '</tbody>



 </table>



 <br /><br />



 </div>';







        // Send the page for output //



        echo $display;



}















function wppAddAdminPage()



{



         // Add Manage Page for WP-Predict



      // add_management_page( 'WP Predict', 'WP-Predict', 7, __FILE__, 'wppDisplayAdminPage' ); //old



                //add_submenu_page('wp-predict.php', 'WP-Predict', 'WP-Predict', 10, __FILE__, 'wppDisplayAdminPage');



                add_options_page( 'WP Predict', 'WP Predict', 'manage_options', 'wp-predict.php', 'wppDisplayAdminPage');



}







add_action('admin_menu','wppAddAdminPage');























function wppDisplayVote( $postContent )



{



        // Get Db Object //



        global $wpdb;

         //$dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictActive = 0 WHERE predictEndDatetime < ".time() );        





        // Global Logged In User ID //



        global $user_ID;







        // Setup Post Information //



        if ( is_single() )



        {



                global $posts;







                $post = $posts[0];



        }



        else



                //global $post;



        $post = array ();







        if ( !empty( $post ) && isset( $post->ID ) )



        {



                // Set Return Content //



                $returnContent = "";







                // Get the Predict Entry if one exists for this post //



                $postPredicts = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictPostId=" . $post->ID . " AND predictActive=1 ORDER BY predictId DESC", OBJECT );







                // Proceed only if we have valid predicts for this post //



                if ( !empty( $postPredicts ) )



                {



                        // Loop through each of them and add the form for predicting as well as the current vote tally //



                        foreach ( $postPredicts as $postPredict )



                        {



 $voteOptions = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $postPredict->predictId, OBJECT );



 $voteOptions = $voteOptions[0];



                $optionsArray = explode( "||", stripslashes( $voteOptions->predictOptions ) );







 // Vote Options -- Only if a valid user is logged in //



 if ( $user_ID )



 {



         // Handle the vote submission now //



         if ( isset( $_POST['postAction'] ) )



         {



                 // Check if it is set to submitVote & aslo the predict ID is the same as the one we are about to process //



                 if ( $_POST['postAction'] == "submitVote" && intval( $_POST['predictId'] ) == $postPredict->predictId )



                 {



                         $submitPredictId = $_POST['predictId'];



                         $selectedOption = $_POST['predictSelection'];







                         // Insert the vote in to the database //



                         $dbResult = @$wpdb->query( "INSERT INTO " . $wpdb->prefix . "wpp_predict_votes (predictEntryId, predictUserId, predictSelectedOption) VALUES (" . $submitPredictId . ", " . $user_ID . ", " . $selectedOption . ")" );



                 }



         }








         if ( isset( $_POST['postAction'] ) && $_POST['postAction'] == "submitVote" && intval( $_POST['predictId'] ) == $postPredict->predictId && $dbResult )



         {



                 $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



                 $returnContent .= '<p><strong>Thank you for your vote!</strong></p>';



         }



         else



         {



                 // Check if this user has already voted on this //



                 $userVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictUserId=" . $user_ID . " AND predictEntryId=" . $postPredict->predictId, OBJECT );







                 // If voted show just message, otherwise show the vote form //



                 if ( !empty( $userVotes ) )



                 {



                         $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



                         $returnContent .= '<p>You have already cast your vote!</p>';



                 }



                 else



                 {



                         $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



                         $returnContent .= '<form id="predict-form" name="predict-form" method="post" action="" style="padding-bottom: 10px;">';



                            foreach ( $optionsArray as $index=>$optionText )



                            {



 $returnContent .= '<input type="radio" name="predictSelection" value="' . ( $index + 1 ) . '" />&nbsp;' . $optionText . '<br />';



                            }







                         $returnContent .= '<br /><input type="hidden" name="predictId" value="' . $postPredict->predictId . '" />';



                         $returnContent .= '<input type="hidden" name="postAction" value="submitVote" />';



                         //$returnContent .= '<input type="submit" name="submitVote" value="Vote Now!" />';



                         $returnContent .= '<input name="submitVote" type="image" src="http://www.tomorrowtimes.com/wp-content/themes/times1/images/vote-now.jpg" value="submitVote" border="0" />';



                         $returnContent .= '</form>';



                 }



         }



 }



 else



 {



         $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



         $returnContent .= '<h3><a href="' . get_option('siteurl') . '/wp-login.php?redirect_to=' . $_SERVER['REQUEST_URI'] . '" >Login</a> or <a href="' . get_option('siteurl') . '/wp-login.php?action=register&redirect_to=' . $_SERVER['REQUEST_URI'] . '" >Register</a> to submit your vote.</h3>';



 }







 // Current Votes Tally -- Log in not required //



 // Get the total number of votes for this entry //



 $entryVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictEntryId=" . $postPredict->predictId, OBJECT );







                $tallyContent = "";







 // Show votes tallies only if at least one vote has been logged //



 if ( !empty( $entryVotes ) )



 {



         // Set total votes //



         $totalVotes = count( $entryVotes );







                    // Set Option Votes Array //



                    $optionVotesArray = array ();







         // Get Option Votes //



                    foreach ( $optionsArray as $index=>$optionText )



                    {



                        $optionTempVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictSelectedOption=" . ( $index+1 ) . " AND predictEntryId=" . $postPredict->predictId, OBJECT );







                        if ( !empty( $optionTempVotes ) )



                            $optionVotesArray[$index+1] = count( $optionTempVotes );



                        else



                            $optionVotesArray[$index+1] = 0;



                    }







         // Get Option Votes //



                    foreach ( $optionsArray as $index=>$optionText )



                    {



                 $percent = ( floatval( $optionVotesArray[$index+1] )/floatval( $totalVotes ) ) * 100.0;



                 $tallyContent .= '<span class="vote-percentage">' . number_format( $percent, 1 ) . '%</span> of the people predicted &quot;' . $optionText . '&quot;<br />';



                    }



 }



 else



         $tallyContent = "<strong>No predictions yet!</strong><br />Be the first one to predict the outcome!";







 $returnContent .= '<p>' . $tallyContent . '</p>';







 if ($postPredict->point > 1) $pts = " points"; else $pts = " point";



 $returnContent .= "<h3 class='posted'>Closing Date: ". date("F j, Y", $postPredict->predictEndDatetime) ." | ". $postPredict->point . $pts."</h3>";



                        }



                }







                // Closed out votes //



                // Get the Predict Entry if one exists for this post //



                $postPredicts = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictPostId=" . $post->ID . " AND predictActive=0 ORDER BY predictId DESC", OBJECT );







                // Proceed only if we have valid predicts for this post //



                if ( !empty( $postPredicts ) )



                {



            $tallyContent = "";







                        // Loop through each of them and add the form for predicting as well as the current vote tally //



                        foreach ( $postPredicts as $postPredict )



                        {



 $voteOptions = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $postPredict->predictId, OBJECT );



 $voteOptions = $voteOptions[0];



                $optionsArray = explode( "||", stripslashes( $voteOptions->predictOptions ) );







                // Set Final Outcome String //



                $finalOutcome = $optionsArray[$voteOptions->predictFinalOutcomeOption-1];



                $finalOutcomeIndex = $voteOptions->predictFinalOutcomeOption-1;







 $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



 $returnContent .= '<p>Voting is closed now!</p>';



                $returnContent .= '<p>The correct prediction was: <strong>' . $finalOutcome . '</strong></p>';







 // Current Votes Tally -- Log in not required //



 // Get the total number of votes for this entry //



 $entryVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictEntryId=" . $postPredict->predictId, OBJECT );







 // Show votes tallies only if at least one vote has been logged //



 if ( !empty( $entryVotes ) )



 {



         // Set total votes //



         $totalVotes = count( $entryVotes );







                    // Set Option Votes Array //



                    $optionVotesArray = array ();







         // Get Option Votes //



                    foreach ( $optionsArray as $index=>$optionText )



                    {



                        $optionTempVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictSelectedOption=" . ( $index+1 ) . " AND predictEntryId=" . $postPredict->predictId, OBJECT );







                        if ( !empty( $optionTempVotes ) )



                            $optionVotesArray[$index+1] = count( $optionTempVotes );



                        else



                            $optionVotesArray[$index+1] = 0;



                    }







         // Get Option Votes //



                    foreach ( $optionsArray as $index=>$optionText )



                    {



                        $percent = ( floatval( $optionVotesArray[$index+1] )/floatval( $totalVotes ) ) * 100.0;







                        if ( $index == $finalOutcomeIndex )



                            $boldOutcome = "<strong>" . number_format( $percent, 1 ) . "%</strong>";



                        else



                            $boldOutcome = number_format( $percent, 1 ) . "%";







                 $tallyContent .= '<span class="vote-percentage">' . $boldOutcome . '</span> of the people predicted &quot;' . $optionText . '&quot;<br />';



                    }



 }



 else



         $tallyContent = "<strong>No predictions were made!</strong>";







 $returnContent .= '<p>' . $tallyContent . '</p>';







 $tallyContent = "";







 if ($postPredict->point > 1) $pts = " points"; else $pts = " point";



 $returnContent .= "<h3 class='posted'>Closing Date: ". date("F j, Y", $postPredict->predictEndDatetime) ." | ". $postPredict->point . $pts."</h3>";







                        }



                }







                return $postContent . "<blockquote>". $returnContent. "</blockquote>";



        }



        else



                return $postContent;



}



























/*



*        Function to display the top predictors all time



*



*        Can be used by setting a tag in the page content where this needs to be display [[WP-Predict::CompleteLeaderboard]]



*/











function format_number($str,$decimal_places='2',$decimal_padding="0"){



        /* firstly format number and shorten any extra decimal places */



        /* Note this will round off the number pre-format $str if you dont want this fucntionality */



        $str           =  number_format($str,$decimal_places,'.','');     // will return 12345.67



        $number       = explode('.',$str);



        $number[1]     = (isset($number[1]))?$number[1]:''; // to fix the PHP Notice error if str does not contain a decimal placing.



        $decimal     = str_pad($number[1],$decimal_places,$decimal_padding);



        return (float) $number[0].'.'.$decimal;



}







function wppDisplayCompleteLeaderboard( $postContent )



{



        // Get Db Object //



        global $wpdb;
 		//$cur_time = time();       // $dbResult = @$wpdb->query ( "UPDATE " . $wpdb->prefix . "wpp_predict_entries SET predictActive = 0 WHERE predictEndDatetime < ".$cur_time ); 






    // Lets first check if we have the Tag we are looking for //



    if ( strpos( $postContent, "<p>[[WP-Predict::CompleteLeaderboard]]</p>" ) !== FALSE )



    {



        // Get all the votes which have been closed and were started in the last limit time frame //



        $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $tfVotes ) )



        {



            // Create the Post ID string //



            $postIdString = "";







            // Loop through each entry and append to post ID String //



            foreach ( $tfVotes as $tfVote )



            {



                if ( $postIdString == "" )



                    $postIdString = $postIdString . $tfVote->predictId;



                else



                    $postIdString = $postIdString . ", " . $tfVote->predictId;



            }







            // Get the stats results //



              $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT sum(point) FROM " . $wpdb->prefix . "wpp_predict_entries AS wpEntries, " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption AND wpV.predictUserId=wpVotes.predictUserId AND wpEntries.predictId = ".$wpdb->prefix . "wpp_predict_options.predictEntryId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ") GROUP BY wpVotes.predictUserId ORDER BY userCount DESC", OBJECT );











            // Make sure we have stats //



            if ( !empty( $statResults ) )



            {



                // Loop through and create return string //



                $returnString = '<div class="vote-leaderboard-list"><h2>Prediction Leaderboard (all time)</h2><table  width="350" cellspacing="2" cellpadding="5">';







                $returnString .= '<tr bgcolor="#326799" align="center" style="color: #FFFFFF"><td>Reader</td><td>Points</td><td>% Correct</td></tr>';







                $i = 1;



                foreach ( $statResults as $index=>$statResult )



                {















        $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE predictUserId=" . $statResult->ID . " ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $userVotes ) )



        {



            // Setup total number of correct predictions so far //



            $correctPredictions = 0;



            $wrongPredictions = 0;



            foreach ( $userVotes as $userVote )



            {



                // Make sure we check the predictActive first //



                if ( $userVote->predictActive == 0 )



                {



                    // Check if the final outcome option and selected option match //



                    if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



                        $correctPredictions = $correctPredictions + 1;



                    else if ($userVote->predictFinalOutcomeOption != 0)



                        $wrongPredictions = $wrongPredictions + 1;



                }



            }



        }







         if ($correctPredictions+$wrongPredictions == 0) $wrongPredictions = 1 ;







                     // $returnString = $returnString . '<li><a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $point . ')</li>';



                     $returnString = $returnString . '<tr ><td>' . $i++ . '.&nbsp;&nbsp;<a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '"  style="color: #326799">' .  $statResult->display_name . '</a> </td><td align=center style="color: #326799">' . $statResult->userCount . ' Pts</td><td align=center style="color: #326799">' .format_number (round(($correctPredictions*100)/( $correctPredictions+$wrongPredictions ),2)). '%</td></tr>';



                     $returnString .= '<tr><td colspan=3 style="border-bottom: 1px solid #eee;"></td></tr>';



                }







                $returnString = $returnString . "</table></div>";







                // return the string now //



                return str_replace( "<p>[[WP-Predict::CompleteLeaderboard]]</p>", "", $postContent ) . $returnString;



            }



            // Return empty string otherwise //



            else



                // Return Post Content as is //



                return $postContent;



        }



        // Return Empty String Otherwise //



        else



            // Return Post Content as is //



            return $postContent;



    }



    else



        // Return the post string as it is //



        return $postContent;



}







add_filter( "the_content", "wppDisplayCompleteLeaderboard" );











/*



*        Function to display the top predictors for given month



*



*        Can be used by setting a tag in the page content where this needs to be display [[WP-Predict::MonthlyLeaderboard::200803]]



*/







function wppDisplayMonthlyLeaderboard( $postContent )



{



        // Get Db Object //



        global $wpdb;







    // Lets first check if we have the Tag we are looking for //



    if ( preg_match( '~\[\[WP-Predict::MonthlyLeaderboard::([0-9]+)\]\]~', $postContent, $tagMatches ) !== FALSE && preg_match( '~\[\[WP-Predict::MonthlyLeaderboard::([0-9]+)\]\]~', $postContent, $tagMatches ) !== 0 )



    //if ( strpos( $postContent, "<p>[[WP-Predict::CompleteLeaderboard]]</p>" ) !== FALSE )



    {



        // Get the month from the given tag //



        $year = substr( $tagMatches[1], 0, 4 );



        $month = substr( $tagMatches[1], -2 );







        $startDatetime = mktime( 0, 0, 0, intval( $month ), 1, intval( $year ) );



        if ( intval( $month ) == 12 )



            $endDatetime = mktime( 0, 0, 0, 1, 1, intval( $year+1 ) );



        else



            $endDatetime = mktime( 0, 0, 0, intval( $month+1 ), 1, intval( $year ) );







        // Get all the votes which have been closed and were started in the last limit time frame //



        $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 AND predictStartDatetime>=" . $startDatetime . " AND predictEndDatetime<=" . $endDatetime . " ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $tfVotes ) )



        {



            // Create the Post ID string //



            $postIdString = "";







            // Loop through each entry and append to post ID String //



            foreach ( $tfVotes as $tfVote )



            {



                if ( $postIdString == "" )



                    $postIdString = $postIdString . $tfVote->predictId;



                else



                    $postIdString = $postIdString . ", " . $tfVote->predictId;



            }







            // Get the stats results //



            $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ") GROUP BY wpVotes.predictUserId ORDER BY userCount DESC", OBJECT );







            // Make sure we have stats //



            if ( !empty( $statResults ) )



            {



                // Loop through and create return string //



                $returnString = '<div class="vote-leaderboard-list"><h2>Prediction Leaderboard - ' . date( "F, Y", $startDatetime ) . '</h2><table  width="350" cellspacing="2" cellpadding="5">';







                $returnString .= '<tr bgcolor="#326799" align="center" style="color: #FFFFFF"><td>Reader</td><td>Points</td><td>% Correct</td></tr>';







                foreach ( $statResults as $index=>$statResult )



                {











                        $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE predictUserId=" . $statResult->ID . " ORDER BY predictId DESC", OBJECT );







                        // Make sure we have something to display //



                        if ( !empty( $userVotes ) )



                        {



                            // Setup total number of correct predictions so far //



                            $correctPredictions = 0;



                            $wrongPredictions = 0;



                            foreach ( $userVotes as $userVote )



                            {



 // Make sure we check the predictActive first //



 if ( $userVote->predictActive == 0 )



 {



     // Check if the final outcome option and selected option match //



     if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



         $correctPredictions = $correctPredictions + 1;



     else if ($userVote->predictFinalOutcomeOption != 0)



         $wrongPredictions = $wrongPredictions + 1;



 }



                            }



                        }











                    //$returnString = $returnString . '<li><a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



                     $returnString = $returnString . '<tr ><td>' . $i++ . '.&nbsp;&nbsp;<a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '"  style="color: #326799">' .  $statResult->display_name . '</a> </td><td align=center style="color: #326799">' . $statResult->userCount . ' Pts</td><td align=center style="color: #326799">' .format_number (round(($correctPredictions*100)/( $correctPredictions+$wrongPredictions ),2)). '%</td></tr>';



                     $returnString .= '<tr><td colspan=3 style="border-bottom: 1px solid #eee;"></td></tr>';











                }







                $returnString = $returnString . "</table></div>";







                // return the string now //



                return str_replace( "<p>" . $tagMatches[0] . "</p>", "", $postContent ) . $returnString;



            }



            // Return empty string otherwise //



            else



                // Return Post Content as is //



                return $postContent;



        }



        // Return Empty String Otherwise //



        else



            // Return Post Content as is //



            return $postContent;



    }



    else



        // Return the post string as it is //



        return $postContent;



}







add_filter( "the_content", "wppDisplayMonthlyLeaderboard" );











/*



*        Function to display the top predictors in given time frame



*



*        Can be used by setting a tag in the page content where this needs to be display [[WP-Predict::TimespanLeaderboard::6::months]]



*/







function wppDisplayTimespanLeaderboard( $postContent )



{



        // Get Db Object //



        global $wpdb;







    // Lets first check if we have the Tag we are looking for //



    if ( preg_match( '~\[\[WP-Predict::TimespanLeaderboard::([0-9]+)::([a-zA-Z]+)\]\]~', $postContent, $tagMatches ) !== FALSE && preg_match( '~\[\[WP-Predict::TimespanLeaderboard::([0-9]+)::([a-zA-Z]+)\]\]~', $postContent, $tagMatches ) !== 0 && isset( $tagMatches[2] ) )



    //if ( strpos( $postContent, "<p>[[WP-Predict::CompleteLeaderboard]]</p>" ) !== FALSE )



    {



        // Get the timespan number //



        $timespanNumber = $tagMatches[1];



        $timespanType = strtolower( $tagMatches[2] );







        // Set current datetime //



        $currentDatetime = time();







        // Get the previous limit //



        $limitDatetime = strtotime( "-" . $timespanNumber . " " . $timespanType );







        // Get all the votes which have been closed and were started in the last limit time frame //



        $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 AND predictStartDatetime>=" . $limitDatetime . " ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $tfVotes ) )



        {



            // Create the Post ID string //



            $postIdString = "";







            // Loop through each entry and append to post ID String //



            foreach ( $tfVotes as $tfVote )



            {



                if ( $postIdString == "" )



                    $postIdString = $postIdString . $tfVote->predictId;



                else



                    $postIdString = $postIdString . ", " . $tfVote->predictId;



            }







            // Get the stats results //



            $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ") GROUP BY wpVotes.predictUserId ORDER BY userCount DESC", OBJECT );







            // Make sure we have stats //



            if ( !empty( $statResults ) )



            {



                // Loop through and create return string //



                $returnString = '<div class="vote-leaderboard-list"><h2>Prediction Leaderboard - Last ' . $timespanNumber . ' ' . ucfirst( $timespanType ) . '</h2><ol class="vote-list">';







                foreach ( $statResults as $index=>$statResult )



                {



                    $returnString = $returnString . '<li><a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



                }







                $returnString = $returnString . "</ol></div>";







                // return the string now //



                return str_replace( "<p>" . $tagMatches[0] . "</p>", "", $postContent ) . $returnString;



            }



            // Return empty string otherwise //



            else



                // Return Post Content as is //



                return $postContent;



        }



        // Return Empty String Otherwise //



        else



            // Return Post Content as is //



            return $postContent;



    }



    else



        // Return the post string as it is //



        return $postContent;



}







add_filter( "the_content", "wppDisplayTimespanLeaderboard" );











/*



*        Function to display the top predictors for given category ID



*



*        Can be used by setting a tag in the page content where this needs to be display [[WP-Predict::CategoryLeaderboard::1]]



*/







function wppDisplayCategoryLeaderboard ( $postContent )



{



        // Get Db Object //



        global $wpdb;







    // Lets first check if we have the Tag we are looking for //



    if ( preg_match( '~\[\[WP-Predict::CategoryLeaderboard::([0-9]+)\]\]~', $postContent, $tagMatches ) !== FALSE && preg_match( '~\[\[WP-Predict::CategoryLeaderboard::([0-9]+)\]\]~', $postContent, $tagMatches ) !== 0 && isset( $tagMatches[1] ) )



    {



        // Get the category ID from the given tag //



        $categoryId = intval( $tagMatches[1] );







        // Get category ID String for votes query //



        $categoryResults = $wpdb->get_results( "SELECT wpTR.* FROM " . $wpdb->prefix . "term_relationships AS wpTR LEFT JOIN " . $wpdb->prefix . "term_taxonomy AS wpTT USING (term_taxonomy_id) WHERE wpTT.taxonomy='category' AND wpTT.term_id=" . $categoryId, OBJECT );







        // Make sure we have valid category posts //



        if ( !empty( $categoryResults ) )



        {



            // Setup the category String //



            $categoryString = ' AND predictPostId IN (';







            // Loop through each category-post and add the ID to the string //



            foreach ( $categoryResults as $categoryResult )



            {



                $categoryString = $categoryString . $categoryResult->object_id . ', ';



            }







            $categoryString = substr( $categoryString, 0, -2 ) . ') ';



        }



        else



            $categoryString = ' ';







        // Get category details //



        $categoryDetails = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id=" . $categoryId, OBJECT );



        $categoryDetail = $categoryDetails[0];



        $categoryName = $categoryDetail->name;







        // Get all the votes which have been closed and were started in the last limit time frame //



        $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0" . $categoryString . "ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $tfVotes ) )



        {



            // Create the Post ID string //



            $postIdString = "";







            // Loop through each entry and append to post ID String //



            foreach ( $tfVotes as $tfVote )



            {



                if ( $postIdString == "" )



                    $postIdString = $postIdString . $tfVote->predictId;



                else



                    $postIdString = $postIdString . ", " . $tfVote->predictId;



            }







            // Get the stats results //



            $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpE ON (wpE.predictId=wpV.predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption" . $categoryString . "AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpEntries.predictId=wpVotes.predictEntryId) LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ")" . $categoryString . "GROUP BY wpVotes.predictUserId ORDER BY userCount DESC", OBJECT );







            // Make sure we have stats //



            if ( !empty( $statResults ) )



            {



                // Loop through and create return string //



                $returnString = '<div class="vote-leaderboard-list"><h2>Prediction Leaderboard - for \'' . $categoryName . '\'</h2><ol class="vote-list">';







                foreach ( $statResults as $index=>$statResult )



                {



                    $returnString = $returnString . '<li><a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



                }







                $returnString = $returnString . "</ol></div>";







                // return the string now //



                return str_replace( "<p>" . $tagMatches[0] . "</p>", "", $postContent ) . $returnString;



            }



            // Return empty string otherwise //



            else



                // Return Post Content as is //



                return $postContent;



        }



        // Return Empty String Otherwise //



        else



            // Return Post Content as is //



            return $postContent;



    }



    else



        // Return the post string as it is //



        return $postContent;



}







add_filter( "the_content", "wppDisplayCategoryLeaderboard" );











/*



*        Function to display the user predictions



*



*        Can be used by setting a tag in the page content where this needs to be display [[WP-Predict::UserPredictions]]



*/







function wppDisplayUserPredictions( $postContent )



{



        // Get Db Object //



        global $wpdb;







        // Global Logged In User ID //



        global $user_ID;







    // Check if we have an ID from the URL //



    if ( isset( $_GET['id'] ) && $_GET['id'] != '' && $_GET['id'] != 0 )



    {



        if ( $_GET['id'] == $user_ID )



        {



            $personString = "Your";



            $personStringTwo = "You were";



        }



        else



        {



            $personString = "User";



            $personStringTwo = "User was";



            $personStringThree = "User has";



        }







        $userId = $_GET['id'];



    }



    else



    {



        $personString = "Your";



        $personStringTwo = "You were";



        $personStringThree = "You have";



        $userId = $user_ID;



    }







    // Lets first check if we have the Tag we are looking for //



    if ( strpos( $postContent, "<p>[[WP-Predict::UserPredictions]]</p>" ) !== FALSE && isset( $userId ) && $userId != 0 )



    {



        // Get all the votes which which the user has made a prediction //



        $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE predictUserId=" . $userId . " ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $userVotes ) )



        {



            // Setup total number of correct predictions so far //



            $correctPredictions = 0;



            $wrongPredictions = 0;



            foreach ( $userVotes as $userVote )



            {



                // Make sure we check the predictActive first //



                if ( $userVote->predictActive == 0 )



                {



                    // Check if the final outcome option and selected option match //



                    if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



                        $correctPredictions = $correctPredictions + 1;



                    else if ($userVote->predictFinalOutcomeOption != 0)



                        $wrongPredictions = $wrongPredictions + 1;



                }



            }







            // Lets get the user details to show the header //



            //$userResults = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "users WHERE ID=" . $userId, OBJECT );



            //$userDetail = $userResults[0];



            $res = mysql_query("select * from wp_users where ID=".$userId) or die (mysql_error());

            $row = mysql_fetch_assoc($res);



            //$returnString .= $row['display_name'];

            // Add the header with user display name //                strtolower( $userDetail->display_name )



            $returnString .= '<div class="vote-leaderboard"><h2>' . $row['display_name'] . ' Prediction Stats</h2></div><br />';







            // Show User Predictions Meta Information //



            //$returnString .= '<div class="vote-leaderboard-list"><h2>Prediction Stats</h2><br />';



            $returnString .= '<p>Total Predictions Made: <strong>' . count( $userVotes ) . '</strong></p>';



            $returnString .= '<p>Correct Predictions: <strong>' . $correctPredictions . '</strong></p>';



            $returnString .= '<p>Incorrect Predictions: <strong>' . $wrongPredictions . '</strong></p></div>';







            $returnString .= '<div class="vote-leaderboard-list"><h2>Prediction Information</h2><ul class="vote-list">';







            // Loop through each prediction and show the list //



            foreach ( $userVotes as $userVote )



            {



                // Lets get the options exploded //



                $optionsArray = explode( "||", stripslashes( $userVote->predictOptions ) );







                // Check if the vote is stil active //



                if ( $userVote->predictActive == 1 )



                    $liClassString = ' class="wpp-pending"';



                else



                {



                    if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



                        $liClassString = ' class="wpp-correct"';



                    else if ($userVote->predictFinalOutcomeOption == 0)



                         $liClassString = ' class="wpp-pending"';



                    else



                        $liClassString = ' class="wpp-incorrect"';



                }







                $returnString .= '<li' . $liClassString . '>' . $userVote->predictHeaderText . ' - ' . $personString . ' prediction was &quot;' . $optionsArray[$userVote->predictSelectedOption-1] . '&quot;';







                // Check if the vote is stil active //



                if ( $userVote->predictActive == 1 )



                    $returnString .= ' - Result pending.</li>';



                else



                {



                    if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



                        $returnString .= ' - ' . $personStringTwo . ' <strong>correct.</strong></li>';



                    else if ($userVote->predictFinalOutcomeOption == 0)



                        $returnString .= ' - Result pending.</li>';



                    else



                        $returnString .= ' - ' . $personStringTwo . ' <strong>incorrect.</strong></li>';



                }



            }







            $returnString .= '</ul></div>';







            // Return the proper list now //



            return str_replace( "<p>[[WP-Predict::UserPredictions]]</p>", "", $postContent ) . $returnString;



        }



        // Return Empty String Otherwise //



        else



        {



            // Set no predictions text //



            $returnString = '<p>' . $personStringThree . ' made no predictions yet!!</p>';







            // Return Post Content as is //



            return str_replace( "<p>[[WP-Predict::UserPredictions]]</p>", "", $postContent ) . $returnString;



        }



    }



    else if ( strpos( $postContent, "<p>[[WP-Predict::UserPredictions]]</p>" ) !== FALSE && $userId == 0 )



    {



        // Add login information to the post content and remove the tag name //



        $loginInformation = "<p>You need to be logged in to access this page.</p>";



        $loginInformation .= '<p>Please login <a href="' . get_option('siteurl') . '/wp-login.php?redirect_to=' . $_SERVER['REQUEST_URI'] . '">here</a></p>';







        // return the string now //



        return str_replace( "<p>[[WP-Predict::UserPredictions]]</p>", "", $postContent ) . $loginInformation;



    }



    else



        // Return the post string as it is //



        return $postContent;



}







add_filter( "the_content", "wppDisplayUserPredictions" );















/*



*        Function to display the top X predictors of all time in the sidebar



*/







function wppDisplaySidebarLeaderboard( $numberToShow = 10 )



{



        // Get Db Object //



        global $wpdb;







    // Get all the votes which have been closed and were started in the last limit time frame //



    $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 ORDER BY predictId DESC", OBJECT );







    // Make sure we have something to display //



    if ( !empty( $tfVotes ) )



    {



        // Create the Post ID string //



        $postIdString = "";







        // Loop through each entry and append to post ID String //



        foreach ( $tfVotes as $tfVote )



        {



            if ( $postIdString == "" )



                $postIdString = $postIdString . $tfVote->predictId;



            else



                $postIdString = $postIdString . ", " . $tfVote->predictId;



        }







        // Get the stats results //



        $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT sum(point) FROM " . $wpdb->prefix . "wpp_predict_entries AS wpEntries, " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption AND wpV.predictUserId=wpVotes.predictUserId AND wpEntries.predictId = ".$wpdb->prefix . "wpp_predict_options.predictEntryId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ") GROUP BY wpVotes.predictUserId ORDER BY userCount DESC LIMIT 0, " . $numberToShow, OBJECT );







        // Make sure we have stats //



        if ( !empty( $statResults ) )



        {



            // Loop through and create return string //



            $returnString = '<div class="widget"><h2 class="widgettitle">Predictors Leaderboard</h2><table  width="320" cellspacing="2" cellpadding="5">';







            $returnString .= '<tr bgcolor="#326799" align="center" style="color: #FFFFFF"><td>Reader</td><td>Points</td><td>% Correct</td></tr>';







            foreach ( $statResults as $index=>$statResult )



            {











        $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE predictUserId=" . $statResult->ID . " ORDER BY predictId DESC", OBJECT );







        // Make sure we have something to display //



        if ( !empty( $userVotes ) )



        {



            // Setup total number of correct predictions so far //



            $correctPredictions = 0;



            $wrongPredictions = 0;



            foreach ( $userVotes as $userVote )



            {



                // Make sure we check the predictActive first //



                if ( $userVote->predictActive == 0 )



                {



                    // Check if the final outcome option and selected option match //



                    if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



                        $correctPredictions = $correctPredictions + 1;



                    else if ($userVote->predictFinalOutcomeOption != 0)



                        $wrongPredictions = $wrongPredictions + 1;



                }



            }



        }















                $returnString = $returnString . '<tr ><td>' . ($index + 1) . '. <a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '" style="color: #326799">' . $statResult->display_name . '</a> </td><td align=center style="color: #326799">' . $statResult->userCount. ' Pts</td><td align=center style="color: #326799">' .format_number (round(($correctPredictions*100)/( $correctPredictions+$wrongPredictions ),2)). '%</td></tr>';



                $returnString .= '<tr><td colspan=3 style="border-bottom: 1px solid #eee;"></td></tr>';



            }







            $returnString = $returnString . '</table></div><div class="fix" style="height:15px !important;"></div>';







            // echo the details now //



            echo $returnString;



        }



    }



}











/*



*        Function to display the top predictors given month in the sidebar



*/







function wppDisplaySidebarMonthlyLeaderboard( $month, $year, $numberToShow = 10 )



{



        // Get Db Object //



        global $wpdb;







    $startDatetime = mktime( 0, 0, 0, $month, 1, $year );



    if ( $month == 12 )



        $endDatetime = mktime( 0, 0, 0, 1, 1, $year+1 );



    else



        $endDatetime = mktime( 0, 0, 0, ( $month+1 ), 1, $year );











        //$endDatetime = strtotime("now");







    // Get all the votes which have been closed and were started in the last limit time frame //



    $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 AND (predictEndDatetime>=" . $startDatetime . " AND predictEndDatetime<=" . $endDatetime . ") ORDER BY predictId DESC", OBJECT );







    if ( !empty( $tfVotes ) )



    {



        // Create the Post ID string //



        $postIdString = "";







        // Loop through each entry and append to post ID String //



        foreach ( $tfVotes as $tfVote )



        {



            if ( $postIdString == "" )



                $postIdString = $postIdString . $tfVote->predictId;



            else



                $postIdString = $postIdString . ", " . $tfVote->predictId;



        }















              //correct















                          $strq="SELECT User.*, wpVotes.predictUserId, (SELECT sum(point) FROM " . $wpdb->prefix . "wpp_predict_entries AS wpEntries, " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption AND wpV.predictUserId=wpVotes.predictUserId AND wpEntries.predictId = ".$wpdb->prefix . "wpp_predict_options.predictEntryId AND  wpEntries.predictId IN (" . $postIdString . ")) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN  $wpdb->users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ") GROUP BY wpVotes.predictUserId ORDER BY userCount DESC LIMIT 0, " . $numberToShow;







             $statResults = $wpdb->get_results( $strq, OBJECT );























        if ( !empty( $statResults ) )



        {



            // Loop through and create return string //



            $returnString = '<div class="widget"><h2 class="widgettitle">Leaderboard - ' . date( "F, Y", $startDatetime ) . '</h2><table  width="320" cellspacing="2" cellpadding="5">';







            $returnString .= '<tr bgcolor="#326799" align="center" style="color: #FFFFFF"><td>Reader</td><td>Points</td><td>% Correct</td></tr>';







            foreach ( $statResults as $index=>$statResult )



            {















                        $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE  (wpEntries.predictEndDatetime>=" . $startDatetime . " AND wpEntries.predictEndDatetime<=" . $endDatetime . ") AND wpEntries.predictActive = 0 AND predictUserId=" . $statResult->ID . " ORDER BY predictId DESC", OBJECT );







                        // Make sure we have something to display //



                        if ( !empty( $userVotes ) )



                        {



                            // Setup total number of correct predictions so far //



                            $correctPredictions = 0;



                            $wrongPredictions = 0;



                            foreach ( $userVotes as $userVote )



                            {



 // Make sure we check the predictActive first //



 if ( $userVote->predictActive == 0 )



 {



     // Check if the final outcome option and selected option match //



     if ( $userVote->predictSelectedOption == $userVote->predictFinalOutcomeOption )



         $correctPredictions = $correctPredictions + 1;



     else if ($userVote->predictFinalOutcomeOption != 0)







         $wrongPredictions = $wrongPredictions + 1;



 }



                            }



                        }











                     $returnString = $returnString . '<tr ><td>' . ($index + 1) . '.&nbsp;&nbsp;<a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '"  style="color: #326799">' .  $statResult->display_name . '</a> </td><td align=center style="color: #326799">' . $statResult->userCount . ' Pts</td><td align=center style="color: #326799">' .format_number (round(($correctPredictions*100)/( $correctPredictions+$wrongPredictions ),2)). '%</td></tr>';



                     $returnString .= '<tr><td colspan=3 style="border-bottom: 1px solid #eee;"></td></tr>';



                }







                $returnString = $returnString . '</table></div><div class="fix" style="height:15px !important;"></div>';








            // echo the details now //



            echo $returnString;



        }



    }



    //else echo "not found!";



}











/*



*        Function to display the top predictors given timespan in the sidebar



*/







function wppDisplaySidebarTimespanLeaderboard( $timespanNumber, $timespanType, $numberToShow = 10 )



{



        // Get Db Object //



        global $wpdb;







    // Set current datetime //



    $currentDatetime = time();







    // Get the previous limit //



    $limitDatetime = strtotime( "-" . $timespanNumber . " " . $timespanType );







    // Get all the votes which have been closed and were started in the last limit time frame //



    $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 AND predictStartDatetime>=" . $limitDatetime . " ORDER BY predictId DESC", OBJECT );







    // Make sure we have something to display //



    if ( !empty( $tfVotes ) )



    {



        // Create the Post ID string //



        $postIdString = "";







        // Loop through each entry and append to post ID String //



        foreach ( $tfVotes as $tfVote )



        {



            if ( $postIdString == "" )



                $postIdString = $postIdString . $tfVote->predictId;



            else



                $postIdString = $postIdString . ", " . $tfVote->predictId;



        }







        // Get the stats results //



        $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ") GROUP BY wpVotes.predictUserId ORDER BY userCount DESC LIMIT 0, " . $numberToShow, OBJECT );







        // Make sure we have stats //



        if ( !empty( $statResults ) )



        {



            // Loop through and create return string //



            $returnString = '<div class="widget"><h2 class="widgettitle">Leaderboard - Last ' . $timespanNumber . ' ' . ucfirst( $timespanType ) . '</h2><ul>';







            foreach ( $statResults as $index=>$statResult )



            {



                $returnString = $returnString . '<li>' . ($index + 1) . '. <a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



            }







            $returnString = $returnString . '</ul></div><div class="fix" style="height:15px !important;"></div>';







            // echo the details now //



            echo $returnString;



        }



    }



}











/*



*        Function to display the top X predictors of all time in the sidebar for given category ID



*/







function wppDisplaySidebarCategoryLeaderboard( $categoryId, $numberToShow = 10 )



{



        // Get Db Object //



        global $wpdb;







    // Get category ID String for votes query //



    $categoryResults = $wpdb->get_results( "SELECT wpTR.* FROM " . $wpdb->prefix . "term_relationships AS wpTR LEFT JOIN " . $wpdb->prefix . "term_taxonomy AS wpTT USING (term_taxonomy_id) WHERE wpTT.taxonomy='category' AND wpTT.term_id=" . $categoryId, OBJECT );







    // Make sure we have valid category posts //



    if ( !empty( $categoryResults ) )



    {



        // Setup the category String //



        $categoryString = ' AND predictPostId IN (';







        // Loop through each category-post and add the ID to the string //



        foreach ( $categoryResults as $categoryResult )



        {



            $categoryString = $categoryString . $categoryResult->object_id . ', ';



        }







        $categoryString = substr( $categoryString, 0, -2 ) . ') ';



    }



    else



        $categoryString = ' ';







    // Get category details //



    $categoryDetails = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id=" . $categoryId, OBJECT );



    $categoryDetail = $categoryDetails[0];



    $categoryName = $categoryDetail->name;







    // Get all the votes which have been closed and were started in the last limit time frame //



    $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0" . $categoryString . "ORDER BY predictId DESC", OBJECT );







    // Make sure we have something to display //



    if ( !empty( $tfVotes ) )



    {



        // Create the Post ID string //



        $postIdString = "";







        // Loop through each entry and append to post ID String //



        foreach ( $tfVotes as $tfVote )



        {



            if ( $postIdString == "" )



                $postIdString = $postIdString . $tfVote->predictId;



            else



                $postIdString = $postIdString . ", " . $tfVote->predictId;



        }







        // Get the stats results //



          $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpE ON (wpE.predictId=wpV.predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption" . $categoryString . "AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpEntries.predictId=wpVotes.predictEntryId) LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ")" . $categoryString . "GROUP BY wpVotes.predictUserId ORDER BY userCount DESC LIMIT 0, " . $numberToShow, OBJECT );











        // Make sure we have stats //



        if ( !empty( $statResults ) )



        {



            // Loop through and create return string //



            $returnString = '<div class="widget"><h2 class="widgettitle">Leaderboard - \'' . $categoryName . '\'</h2><ul>';







            foreach ( $statResults as $index=>$statResult )



            {



                $returnString = $returnString . '<li>' . ($index + 1) . '. <a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



            }







            $returnString = $returnString . '</ul></div><div class="fix" style="height:15px !important;"></div>';







            // echo the details now //



            echo $returnString;



        }



    }



}











/*



*        Function to display the top predictors given month in the sidebar for the given category ID



*/







function wppDisplaySidebarCategoryMonthlyLeaderboard( $categoryId, $month, $year, $numberToShow = 10 )



{



        // Get Db Object //



        global $wpdb;







    // Get category ID String for votes query //



    $categoryResults = $wpdb->get_results( "SELECT wpTR.* FROM " . $wpdb->prefix . "term_relationships AS wpTR LEFT JOIN " . $wpdb->prefix . "term_taxonomy AS wpTT USING (term_taxonomy_id) WHERE wpTT.taxonomy='category' AND wpTT.term_id=" . $categoryId, OBJECT );







    // Make sure we have valid category posts //



    if ( !empty( $categoryResults ) )



    {



        // Setup the category String //



        $categoryString = ' AND predictPostId IN (';







        // Loop through each category-post and add the ID to the string //



        foreach ( $categoryResults as $categoryResult )



        {



            $categoryString = $categoryString . $categoryResult->object_id . ', ';



        }







        $categoryString = substr( $categoryString, 0, -2 ) . ') ';



    }



    else



        $categoryString = ' ';







    // Get category details //



    $categoryDetails = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id=" . $categoryId, OBJECT );



    $categoryDetail = $categoryDetails[0];



    $categoryName = $categoryDetail->name;







    $startDatetime = mktime( 0, 0, 0, $month, 1, $year );



    if ( $month == 12 )



        $endDatetime = mktime( 0, 0, 0, 1, 1, $year+1 );



    else



        $endDatetime = mktime( 0, 0, 0, ( $month+1 ), 1, $year );







    // Get all the votes which have been closed and were started in the last limit time frame //



    $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 AND predictStartDatetime>=" . $startDatetime . " AND predictEndDatetime<=" . $endDatetime . "" . $categoryString . "ORDER BY predictId DESC", OBJECT );







    // Make sure we have something to display //



    if ( !empty( $tfVotes ) )



    {



        // Create the Post ID string //



        $postIdString = "";







        // Loop through each entry and append to post ID String //



        foreach ( $tfVotes as $tfVote )



        {



            if ( $postIdString == "" )



                $postIdString = $postIdString . $tfVote->predictId;



            else



                $postIdString = $postIdString . ", " . $tfVote->predictId;



        }







        // Get the stats results //



        $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpE ON (wpE.predictId=wpV.predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption" . $categoryString . "AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpEntries.predictId=wpVotes.predictEntryId) LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ")" . $categoryString . "GROUP BY wpVotes.predictUserId ORDER BY userCount DESC LIMIT 0, " . $numberToShow, OBJECT );











        // Make sure we have stats //



        if ( !empty( $statResults ) )



        {



            // Loop through and create return string //



            $returnString = '<div class="widget"><h2 class="widgettitle">Leaderboard - ' . date( "F, Y", $startDatetime ) . ' (' . $categoryName . ')</h2><ul>';







            foreach ( $statResults as $index=>$statResult )



            {



                $returnString = $returnString . '<li>' . ($index + 1) . '. <a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



            }







            $returnString = $returnString . '</ul></div><div class="fix" style="height:15px !important;"></div>';







            // echo the details now //



            echo $returnString;



        }



    }



}











/*



*        Function to display the top predictors given timespan in the sidebar for given category ID



*/







function wppDisplaySidebarCategoryTimespanLeaderboard( $categoryId, $timespanNumber, $timespanType, $numberToShow = 10 )



{



        // Get Db Object //



        global $wpdb;







    // Get category ID String for votes query //



    $categoryResults = $wpdb->get_results( "SELECT wpTR.* FROM " . $wpdb->prefix . "term_relationships AS wpTR LEFT JOIN " . $wpdb->prefix . "term_taxonomy AS wpTT USING (term_taxonomy_id) WHERE wpTT.taxonomy='category' AND wpTT.term_id=" . $categoryId, OBJECT );







    // Make sure we have valid category posts //



    if ( !empty( $categoryResults ) )



    {



        // Setup the category String //



        $categoryString = ' AND predictPostId IN (';







        // Loop through each category-post and add the ID to the string //



        foreach ( $categoryResults as $categoryResult )



        {



            $categoryString = $categoryString . $categoryResult->object_id . ', ';



        }







        $categoryString = substr( $categoryString, 0, -2 ) . ') ';



    }



    else



        $categoryString = ' ';







    // Get category details //



    $categoryDetails = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "terms WHERE term_id=" . $categoryId, OBJECT );



    $categoryDetail = $categoryDetails[0];



    $categoryName = $categoryDetail->name;







    // Set current datetime //



    $currentDatetime = time();







    // Get the previous limit //



    $limitDatetime = strtotime( "-" . $timespanNumber . " " . $timespanType );







    // Get all the votes which have been closed and were started in the last limit time frame //



    $tfVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictActive=0 AND predictStartDatetime>=" . $limitDatetime . "" . $categoryString . "ORDER BY predictId DESC", OBJECT );







    // Make sure we have something to display //



    if ( !empty( $tfVotes ) )



    {



        // Create the Post ID string //



        $postIdString = "";







        // Loop through each entry and append to post ID String //



        foreach ( $tfVotes as $tfVote )



        {



            if ( $postIdString == "" )



                $postIdString = $postIdString . $tfVote->predictId;



            else



                $postIdString = $postIdString . ", " . $tfVote->predictId;



        }







        // Get the stats results //



        $statResults = $wpdb->get_results( "SELECT User.*, wpVotes.predictUserId, (SELECT COUNT(*) FROM " . $wpdb->prefix . "wpp_predict_votes AS wpV LEFT JOIN " . $wpdb->prefix . "wpp_predict_options USING (predictEntryId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpE ON (wpE.predictId=wpV.predictEntryId) WHERE wpV.predictSelectedOption=" . $wpdb->prefix . "wpp_predict_options.predictFinalOutcomeOption" . $categoryString . "AND wpV.predictUserId=wpVotes.predictUserId) AS userCount FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpEntries.predictId=wpVotes.predictEntryId) LEFT JOIN " . $wpdb->prefix . "users AS User ON (User.ID=wpVotes.predictUserId) WHERE wpVotes.predictEntryId IN (" . $postIdString . ")" . $categoryString . "GROUP BY wpVotes.predictUserId ORDER BY userCount DESC LIMIT 0, " . $numberToShow, OBJECT );











        // Make sure we have stats //



        if ( !empty( $statResults ) )



        {



            // Loop through and create return string //



            $returnString = '<div class="widget"><h2 class="widgettitle">Leaderboard - Last ' . $timespanNumber . ' ' . ucfirst( $timespanType ) . ' (' . $categoryName . ')</h2><ul>';







            foreach ( $statResults as $index=>$statResult )



            {



                $returnString = $returnString . '<li>' . ($index + 1) . '. <a href="' . get_settings('home') . '/mypredictions/?id=' . $statResult->ID . '">' . $statResult->display_name . '</a> (' . $statResult->userCount . ')</li>';



            }







            $returnString = $returnString . '</ul></div><div class="fix" style="height:15px !important;"></div>';







            // echo the details now //



            echo $returnString;



        }



    }



}



















/*



*        Function to display the most recent 10 prediction quesions in the sidebar



*        eh.rajib@gmail.com



*/







function wppDisplaySidebarPredictions( $numberToShow = 15 )



{



        // Get Db Object //



        global $wpdb;







        global $user_ID;







    // Get all the votes which have been closed and were started in the last limit time frame //











            $openVotes = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries as wpEnt, " . $wpdb->prefix . "wpp_predict_options as wpOpt WHERE predictId = predictEntryId AND wpOpt.predictFinalOutcomeOption = 0 AND wpEnt.predictEndDatetime >= ". strtotime('now')."  ORDER BY wpEnt.predictId DESC LIMIT 0, " . $numberToShow, OBJECT );











    // Make sure we have something to display //



    if ( !empty( $openVotes ) )



    {







            // Loop through and create return string //



            // wppDisplaySidebarPredictions Heading //



            $returnString = '<div class="widget"><h2 class="widgettitle">Make predictions now!</h2><ul>';







            foreach ( $openVotes as $openVote )



            {



                if ( $user_ID ) {



                $tmp = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes as wpVot WHERE predictEntryId = '".$openVote->predictId."' AND wpVot.predictUserId = '".$user_ID."'" , OBJECT );







                if ( empty( $tmp ) ){



                        $returnString = $returnString . '<li><a href="'.get_permalink($openVote->predictPostId).'">' . $openVote->predictHeaderText . '</a></li>';



                }







                }







                else {



                        $returnString = $returnString . '<li><a href="'.get_permalink($openVote->predictPostId).'">' . $openVote->predictHeaderText . '</a></li>';



                }







            }







            $returnString = $returnString . '</ul></div><div class="fix" style="height:15px !important;"></div>';







            // echo the details now //



            echo $returnString;







    }



}























function postcontentvote($contents)



{











global $post, $wpdb;







  global $user_ID;







 if ( !empty( $post ) && isset( $post->ID ) )



         {



                 // Set Return Content //



                 $returnContent = "";







                 // Get the Predict Entry if one exists for this post //



                 $postPredicts = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictPostId=" . $post->ID . " AND predictActive=1 ORDER BY predictId DESC", OBJECT );







                 // Proceed only if we have valid predicts for this post //



                 if ( !empty( $postPredicts ) )



                 {



                         // Loop through each of them and add the form for predicting as well as the current vote tally //



                         foreach ( $postPredicts as $postPredict )



                         {



  $voteOptions = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $postPredict->predictId, OBJECT );



  $voteOptions = $voteOptions[0];



                 $optionsArray = explode( "||", stripslashes( $voteOptions->predictOptions ) );







  // Vote Options -- Only if a valid user is logged in //



  if ( $user_ID )



  {



          // Handle the vote submission now //



          if ( isset( $_POST['postAction'] ) )



          {



                  // Check if it is set to submitVote & aslo the predict ID is the same as the one we are about to process //



                  if ( $_POST['postAction'] == "submitVote" && intval( $_POST['predictId'] ) == $postPredict->predictId )



                  {



                          $submitPredictId = $_POST['predictId'];



                          $selectedOption = $_POST['predictSelection'];







                          // Insert the vote in to the database //



                          $dbResult = @$wpdb->query( "INSERT INTO " . $wpdb->prefix . "wpp_predict_votes (predictEntryId, predictUserId, predictSelectedOption) VALUES (" . $submitPredictId . ", " . $user_ID . ", " . $selectedOption . ")" );



                  }



          }







          if ( isset( $_POST['postAction'] ) && $_POST['postAction'] == "submitVote" && intval( $_POST['predictId'] ) == $postPredict->predictId && $dbResult )



          {



                  $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



                  $returnContent .= '<p><strong>Thank you for your vote!</strong></p>';



          }



          else



          {



                  // Check if this user has already voted on this //



                  $userVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictUserId=" . $user_ID . " AND predictEntryId=" . $postPredict->predictId, OBJECT );







                  // If voted show just message, otherwise show the vote form //



                  if ( !empty( $userVotes ) )



                  {



                          $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



                          $returnContent .= '<p>You have already cast your vote!</p>';



                  }



                  else



                  {



                          $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



                          $returnContent .= '<form id="predict-form" name="predict-form" method="post" action="" style="padding-bottom: 10px;">';



                             foreach ( $optionsArray as $index=>$optionText )



                             {



  $returnContent .= '<input type="radio" name="predictSelection" value="' . ( $index + 1 ) . '" />&nbsp;' . $optionText . '<br />';



                             }







                          $returnContent .= '<br /><input type="hidden" name="predictId" value="' . $postPredict->predictId . '" />';



                          $returnContent .= '<input type="hidden" name="postAction" value="submitVote" />';



                          //$returnContent .= '<input type="submit" name="submitVote" value="Vote Now!"/>';



                          $returnContent .= '<input name="submitVote" type="image" src="http://www.tomorrowtimes.com/wp-content/themes/times1/images/vote-now.jpg" value="submitVote" border="0" />';



                          $returnContent .= '</form>';



                  }



          }



  }



  else



  {



          $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



          $returnContent .= '<h3><a href="' . get_option('siteurl') . '/wp-login.php?redirect_to=' . $_SERVER['REQUEST_URI'] . '">Login</a> or <a href="' . get_option('siteurl') . '/register">Register</a> to submit your vote.</h3>';



  }







  // Current Votes Tally -- Log in not required //



  // Get the total number of votes for this entry //



  $entryVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictEntryId=" . $postPredict->predictId, OBJECT );







                 $tallyContent = "";







  // Show votes tallies only if at least one vote has been logged //



  if ( !empty( $entryVotes ) )



  {



          // Set total votes //



          $totalVotes = count( $entryVotes );







                     // Set Option Votes Array //



                     $optionVotesArray = array ();







          // Get Option Votes //



                     foreach ( $optionsArray as $index=>$optionText )



                     {



                         $optionTempVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictSelectedOption=" . ( $index+1 ) . " AND predictEntryId=" . $postPredict->predictId, OBJECT );







                         if ( !empty( $optionTempVotes ) )



                             $optionVotesArray[$index+1] = count( $optionTempVotes );



                         else



                             $optionVotesArray[$index+1] = 0;



                     }







          // Get Option Votes //



                     foreach ( $optionsArray as $index=>$optionText )



                     {



                  $percent = ( floatval( $optionVotesArray[$index+1] )/floatval( $totalVotes ) ) * 100.0;



                  $tallyContent .= '<span class="vote-percentage">' . number_format( $percent, 1 ) . '%</span> of the people predicted &quot;' . $optionText . '&quot;<br />';



                     }



  }



  else



          $tallyContent = "<strong>No predictions yet!</strong><br />Be the first one to predict the outcome!";







  $returnContent .= '<p>' . $tallyContent . '</p>';















      // Lets first check if we have the Tag we are looking for //



      if (isset( $user_ID ) && $user_ID != 0 )



      {



          // Get all the votes which which the user has made a prediction //



          $userVote = $wpdb->get_row ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE predictUserId=" . $user_ID . " AND wpVotes.predictEntryId=" . $postPredict->predictId, OBJECT );







          $finalOutcome2 = $optionsArray[$userVote->predictSelectedOption-1];



          $returnContentTmp = '';



          // Make sure we have something to display //







          $returnContentTmp .= "Your prediction: " ;







          if ( !empty( $userVote ) )



          {











                  $finalOutcome2 = $optionsArray[$userVote->predictSelectedOption-1];



                  $returnContentTmp .= "<b>".$finalOutcome2."</b>";







          }



          else



                  $returnContentTmp .= "<b>Pending</b>" ;







      }







  if ($postPredict->point > 1) $pts = " points"; else $pts = " point";



  $returnContent .= "<h3 class='posted'><div align=left>" . $returnContentTmp . "</div> Closing Date: ". date("F j, Y", $postPredict->predictEndDatetime) ." | ". $postPredict->point . $pts."</h3>";











  // Get all the votes which which the user has made a prediction //



          $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE wpVotes.predictEntryId=" . $postPredict->predictId, OBJECT );







          $userPredictions = '';







          // Make sure we have something to display //



          if ( !empty( $userVotes ) )



          {



                foreach ( $userVotes as $userVote )



                     {



                             $userName2 = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID=". $userVote->predictUserId, OBJECT );











                             $finalOutcome2 = $optionsArray[$userVote->predictSelectedOption-1];



                             $userPredictions .= $userName2->display_name . " is predicting \"" . $finalOutcome2 . "\"...";



                     }



          }







  $returnContent .= "<h3 class='posted'><div align=left><font color='#FF0000'>What people are predicting (".count($userVotes)."):</font><marquee behavior='scroll' onmouseover='javascript:this.stop();' onmouseout='javascript:this.start();' scrollamount='2' scrolldelay='5'> " . $userPredictions . "</marquee></div></h3>";















                         }



                 }







                 // Closed out votes //



                 // Get the Predict Entry if one exists for this post //



                 $postPredicts = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_entries WHERE predictPostId=" . $post->ID . " AND predictActive=0 ORDER BY predictId DESC", OBJECT );







                 // Proceed only if we have valid predicts for this post //



                 if ( !empty( $postPredicts ) )



                 {



             $tallyContent = "";







                         // Loop through each of them and add the form for predicting as well as the current vote tally //



                         foreach ( $postPredicts as $postPredict )



                         {



  $voteOptions = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_options WHERE predictEntryId=" . $postPredict->predictId, OBJECT );



  $voteOptions = $voteOptions[0];



                 $optionsArray = explode( "||", stripslashes( $voteOptions->predictOptions ) );







                 // Set Final Outcome String //



                 $finalOutcome = $optionsArray[$voteOptions->predictFinalOutcomeOption-1];



                 $finalOutcomeIndex = $voteOptions->predictFinalOutcomeOption-1;







  $returnContent .= '<p class="vote-header">' . stripslashes( $postPredict->predictHeaderText ) . '</p>';



  $returnContent .= '<p>Voting is closed now!</p>';



                 $returnContent .= '<p>The correct prediction was: <strong>' . $finalOutcome . '</strong></p>';











  // Current Votes Tally -- Log in not required //



  // Get the total number of votes for this entry //



  $entryVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictEntryId=" . $postPredict->predictId, OBJECT );







  // Show votes tallies only if at least one vote has been logged //



  if ( !empty( $entryVotes ) )



  {



          // Set total votes //



          $totalVotes = count( $entryVotes );







                     // Set Option Votes Array //



                     $optionVotesArray = array ();







          // Get Option Votes //



                     foreach ( $optionsArray as $index=>$optionText )



                     {



                         $optionTempVotes = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "wpp_predict_votes WHERE predictSelectedOption=" . ( $index+1 ) . " AND predictEntryId=" . $postPredict->predictId, OBJECT );







                         if ( !empty( $optionTempVotes ) )



                             $optionVotesArray[$index+1] = count( $optionTempVotes );



                         else



                             $optionVotesArray[$index+1] = 0;



                     }







          // Get Option Votes //



                     foreach ( $optionsArray as $index=>$optionText )



                     {



                         $percent = ( floatval( $optionVotesArray[$index+1] )/floatval( $totalVotes ) ) * 100.0;







                         if ( $index == $finalOutcomeIndex )



                             $boldOutcome = "<strong>" . number_format( $percent, 1 ) . "%</strong>";



                         else



                             $boldOutcome = number_format( $percent, 1 ) . "%";







                  $tallyContent .= '<span class="vote-percentage">' . $boldOutcome . '</span> of the people predicted &quot;' . $optionText . '&quot;<br />';



                     }



  }



  else



          $tallyContent = "<strong>No predictions were made!</strong>";







  $returnContent .= '<p>' . $tallyContent . '</p>';







  $tallyContent = "";















      // Lets first check if we have the Tag we are looking for //



      if (isset( $user_ID ) && $user_ID != 0 )



      {



          // Get all the votes which which the user has made a prediction //



          $userVote = $wpdb->get_row ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE predictUserId=" . $user_ID . " AND wpVotes.predictEntryId=" . $postPredict->predictId, OBJECT );







          $finalOutcome2 = $optionsArray[$userVote->predictSelectedOption-1];



          $returnContentTmp = '';



          // Make sure we have something to display //







          $returnContentTmp .= "Your prediction: " ;







          if ( !empty( $userVote ) )



          {











                  $finalOutcome2 = $optionsArray[$userVote->predictSelectedOption-1];



                  $returnContentTmp .= "<b>".$finalOutcome2."</b>";







          }



          else



                  $returnContentTmp .= "<b>Pending</b>" ;







      }



















  if ($postPredict->point > 1) $pts = " points"; else $pts = " point";



  $returnContent .= "<h3 class='posted'><div align=left>" .$returnContentTmp."</div>Closing Date: ". date("F j, Y", $postPredict->predictEndDatetime) ." | ". $postPredict->point . $pts."</h3>";











  // Get all the votes which which the user has made a prediction //



          $userVotes = $wpdb->get_results ( "SELECT wpVotes.predictUserId, wpVotes.predictSelectedOption, wpEntries.predictActive, wpOptions.predictFinalOutcomeOption, wpOptions.predictOptions, wpEntries.predictId, wpEntries.predictPostId, wpEntries.predictHeaderText FROM " . $wpdb->prefix . "wpp_predict_votes AS wpVotes LEFT JOIN " . $wpdb->prefix . "wpp_predict_entries AS wpEntries ON (wpVotes.predictEntryId=wpEntries.predictId) LEFT JOIN " . $wpdb->prefix . "wpp_predict_options AS wpOptions ON (wpVotes.predictEntryId=wpOptions.predictEntryId) WHERE wpVotes.predictEntryId=" . $postPredict->predictId, OBJECT );







          $userPredictions = '';







          // Make sure we have something to display //



          if ( !empty( $userVotes ) )



          {



                foreach ( $userVotes as $userVote )



                     {



                             $userName2 = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID=". $userVote->predictUserId, OBJECT );











                             $finalOutcome2 = $optionsArray[$userVote->predictSelectedOption-1];



                             $userPredictions .= $userName2->display_name . " is predicting \"" . $finalOutcome2 . "\"...";



                     }



          }







  $returnContent .= "<h3 class='posted'><div align=left><font color='#FF0000'>What people are predicting (".count($userVotes)."):</font><marquee behavior='scroll' onmouseover='javascript:this.stop();' onmouseout='javascript:this.start();' scrollamount='2' scrolldelay='5'> " . $userPredictions . "</marquee></div></h3>";







                         }



                 }















                 //echo $postContent . "<blockquote>". $returnContent. "</blockquote>";



         }







                 return $contents. "<blockquote>". $returnContent. "</blockquote>";







}







add_action('the_content','postcontentvote');































//-----------------------------Sidebar widget---------------------------------------------



global $wp_version;







$version = $wp_version;



$version =  floatval($version);











if( $version >= 2.8 )



{







function wp_widget_prediction($args) {



        extract($args);







        //echo $before_widget . $before_title . $title . $after_title;







         wppDisplaySidebarPredictions();







        //echo $after_widget;



}











function wp_widget_prediction_control()



 {



 echo "Display the most recent 10 prediction quesions in the sidebar";



}











$widget_ops = array('classname' => 'sidebar_prediction', 'description' => __( "Sidebar Prediction") );



        wp_register_sidebar_widget('prediction', __('Sidebar Prediction'), 'wp_widget_prediction', $widget_ops);



        wp_register_widget_control('prediction', __('Sidebar Prediction'), 'wp_widget_prediction_control' );











// widget class







class WP_Widget_Predict extends WP_Widget {







        function WP_Widget_Predict() {



                $widget_ops = array('classname' => 'widget_predict', 'description' => __( 'SidebarMonthlyLeaderboard widget') );



                $this->WP_Widget('predict', __('SidebarMonthlyLeaderboard'), $widget_ops);



        }







        function widget( $args, $instance ) {



                extract( $args );







        $month = $instance['prediction-month'];



        $year = $instance['prediction-year'];







        echo $before_widget;







        wppDisplaySidebarMonthlyLeaderboard( $month, $year );







        echo $after_widget;



        }















        function update( $new_instance, $old_instance )



        {



                $instance = $old_instance;







                $instance['prediction-month'] = strip_tags($new_instance['prediction-month']);







                $instance['prediction-year'] = strip_tags( $new_instance['prediction-year'] );











                return $instance;



        }











        function form( $instance )



        {



                //Defaults



                $instance = wp_parse_args( (array) $instance, array( 'prediction-month' => '', 'prediction-year' => '') );







                $month = esc_attr( $instance['prediction-month'] );



                $year = esc_attr( $instance['prediction-year'] );



        ?>











        <p><label for="<?php echo $this->get_field_id('prediction-month'); ?>"><?php _e('Month:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('prediction-month'); ?>" name="<?php echo $this->get_field_name('prediction-month'); ?>" type="text" value="<?php echo $month; ?>" /></label><br/>







        <label for="<?php echo $this->get_field_id('prediction-year'); ?>"><?php _e('Year:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('prediction-year'); ?>" name="<?php echo $this->get_field_name('prediction-year'); ?>" type="text" value="<?php echo $year; ?>" /></label>







        </p>



<?php



        }











}











add_action('widgets_init','rw');











function rw()



{



register_widget('WP_Widget_Predict');



}







} //end if







//-------------------------------------------End for 2.8 + ---------------------







if( $version== 2.7 )



{



//**********************start*******************************











function wp_widget_prediction($args) {



        extract($args);







        //echo $before_widget . $before_title . $title . $after_title;







         wppDisplaySidebarPredictions();







        //echo $after_widget;



}











function wp_widget_prediction_control()



 {



 echo "Display the most recent 10 prediction quesions in the sidebar";



}







function wpwpr()



{



  $widget_ops = array('classname' => 'sidebar_prediction', 'description' => __( "Sidebar Prediction") );



        wp_register_sidebar_widget('calendar', __('Sidebar Prediction'), 'wp_widget_prediction', $widget_ops);



        wp_register_widget_control('calendar', __('Sidebar Prediction'), 'wp_widget_prediction_control' );







}







add_action( 'widgets_init', 'wpwpr' );



















//-------------------------







function widget_SidebarMonthlyLeaderboard( $args, $widget_args = 1 ) {



        extract( $args, EXTR_SKIP );



        if ( is_numeric($widget_args) )



                $widget_args = array( 'number' => $widget_args );



        $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );



        extract( $widget_args, EXTR_SKIP );







        // Data should be stored as array:  array( number => data for that instance of the widget, ... )



        $options = get_option('widget_smlb');



        if ( !isset($options[$number]) )



                return;







        //echo $before_widget;







        // Do stuff for this widget, drawing data from $options[$number]







        $month = attribute_escape($options[$number]['month']);



        $year = attribute_escape($options[$number]['year']);







        wppDisplaySidebarMonthlyLeaderboard((int)$month, (int)$year);











        //echo $after_widget;



}











function widget_SidebarMonthlyLeaderboard_control( $widget_args = 1 ) {



        global $wp_registered_widgets;



        static $updated = false; // Whether or not we have already updated the data after a POST submit







        if ( is_numeric($widget_args) )



                $widget_args = array( 'number' => $widget_args );



        $widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );



        extract( $widget_args, EXTR_SKIP );







        // Data should be stored as array:  array( number => data for that instance of the widget, ... )



        $options = get_option('widget_smlb');



        if ( !is_array($options) )



                $options = array();







        // We need to update the data



        if ( !$updated && !empty($_POST['sidebar']) ) {



                // Tells us what sidebar to put the data in



                $sidebar = (string) $_POST['sidebar'];







                $sidebars_widgets = wp_get_sidebars_widgets();



                if ( isset($sidebars_widgets[$sidebar]) )



                        $this_sidebar =& $sidebars_widgets[$sidebar];



                else



                        $this_sidebar = array();







                foreach ( $this_sidebar as $_widget_id ) {



                        // Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data



                        // since widget ids aren't necessarily persistent across multiple updates



                        if ( 'widget_smlb' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {



                                $widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];



                                if ( !in_array( "smlb-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "many-$widget_number" is "{id_base}-{widget_number}



                                        unset($options[$widget_number]);



                        }



                }







                foreach ( (array) $_POST['widget-smlb'] as $widget_number => $widget_many_instance ) {



                        // compile data from $widget_many_instance



                        if ( !isset($widget_many_instance['month']) && isset($options[$widget_number]) ) // user clicked cancel



                                continue;



                        $month = wp_specialchars( $widget_many_instance['month'] );



                        $year= wp_specialchars( $widget_many_instance['year'] );



                        $options[$widget_number] = array( 'month' => $month, 'year'=>$year );  // Even simple widgets should store stuff in array, rather than in scalar



                }







                update_option('widget_smlb', $options);







                $updated = true; // So that we don't go through this more than once



        }











        // Here we echo out the form



        if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS



                $something = '';



                $number = '%i%';



        } else {



                $month = attribute_escape($options[$number]['month']);



                $year = attribute_escape($options[$number]['year']);







        }







        // The form has inputs with names like widget-many[$number][something] so that all data for that instance of



        // the widget are stored in one $_POST variable: $_POST['widget-many'][$number]



?>



                <p>Month:



                        <input class="widefat" id="widget-smlb-month<?php echo $number; ?>" name="widget-smlb[<?php echo $number; ?>][month]" type="text" value="<?php echo $month; ?>" />







                        Year:



                        <input class="widefat" id="widget-smlb-year<?php echo $number; ?>" name="widget-smlb[<?php echo $number; ?>][year]" type="text" value="<?php echo $year; ?>" />



                        <input type="hidden" id="widget-smlb-submit-<?php echo $number; ?>" name="widget-smlb[<?php echo $number; ?>][submit]" value="1" />



                </p>



<?php



}











function widget_SidebarMonthlyLeaderboard_register() {



        if ( !$options = get_option('widget_smlb') )



                $options = array();







        $widget_ops = array('classname' => 'widget_smlb', 'description' => __('Widget SidebarMonthlyLeaderboard'));



        $control_ops = array('width' => 400, 'height' => 350, 'id_base' => 'smlb');



        $name = __('SidebarMonthlyLeaderboard');







        $registered = false;



        foreach ( array_keys($options) as $o ) {



                // Old widgets can have null values for some reason



                if ( !isset($options[$o]['month']) ) // we used 'something' above in our exampple.  Replace with with whatever your real data are.



                        continue;







                // $id should look like {$id_base}-{$o}



                $id = "smlb-$o"; // Never never never translate an id



                $registered = true;



                wp_register_sidebar_widget( $id, $name, 'widget_SidebarMonthlyLeaderboard', $widget_ops, array( 'number' => $o ) );



                wp_register_widget_control( $id, $name, 'widget_SidebarMonthlyLeaderboard_control', $control_ops, array( 'number' => $o ) );



        }







        // If there are none, we register the widget's existance with a generic template



        if ( !$registered ) {



                wp_register_sidebar_widget( 'smlb-1', $name, 'widget_SidebarMonthlyLeaderboard', $widget_ops, array( 'number' => -1 ) );



                wp_register_widget_control( 'smlb-1', $name, 'widget_SidebarMonthlyLeaderboard_control', $control_ops, array( 'number' => -1 ) );



        }



}







// This is important



add_action( 'widgets_init', 'widget_SidebarMonthlyLeaderboard_register' );



















//***********************end******************************







} //end if 2.7?>