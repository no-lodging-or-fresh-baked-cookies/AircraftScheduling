<?php
//-----------------------------------------------------------------------------
// 
// UserInformation.php
// 
// PURPOSE: Displays the user information form so that the user can update
//          their user information. This form is called when a password has
//          expired to force the user to update their information.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      InstructorResource - resource for the selected instructor
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      email - email address for the user
//      username - username for the user
//      password - password (first entry) for the user
//      password2 - password (second entry) for the user
//      title - title for the user
//      first_name - first name for the user
//      middle_name - middle name for the user
//      last_name - last name for the user
//      address1 - first address line for the user
//      address2 - second address line for the user
//      city - city for the user
//      state - state for the user
//      zip - zip code for the user
//      user_level - user level for the user
//      Allow_Phone_Number_Display - true to allow phones to be displayed for the user
//      phone_number - first phone number for the user
//      Home_Phone - second phone number for the user
//      InstructorPicture - instructor picture for the user
//      InstructorResourceID - instructor recourse ID for the user
//      InstructorsDescription - istructor desctiption for the user
//      picture - upload information for the picture file
//      delete_picture - set to "on" to delete the instructor picture
//      old_picture - name of the old picture (if any)
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------
    
    include "global_def.inc";
    include "config.inc";
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    
    // initialize data
    $InstructorResource = "";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["email"])) $email = $rdata["email"];
    if(isset($rdata["username"])) $username= $rdata["username"];
    if(isset($rdata["password"])) $password = $rdata["password"];
    if(isset($rdata["password2"])) $password2 = $rdata["password2"];
    if(isset($rdata["title"])) $title = $rdata["title"];
    if(isset($rdata["first_name"])) $first_name = $rdata["first_name"];
    if(isset($rdata["middle_name"])) $middle_name = $rdata["middle_name"];
    if(isset($rdata["last_name"])) $last_name = $rdata["last_name"];
    if(isset($rdata["address1"])) $address1 = $rdata["address1"];
    if(isset($rdata["address2"])) $address2 = $rdata["address2"];
    if(isset($rdata["city"])) $city = $rdata["city"];
    if(isset($rdata["state"])) $state = $rdata["state"];
    if(isset($rdata["zip"])) $zip = $rdata["zip"];
    if(isset($rdata["user_level"])) $user_level = $rdata["user_level"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["Allow_Phone_Number_Display"])) $Allow_Phone_Number_Display = $rdata["Allow_Phone_Number_Display"];
    if(isset($rdata["phone_number"])) $phone_number = $rdata["phone_number"];
    if(isset($rdata["Home_Phone"])) $Home_Phone = $rdata["Home_Phone"];
    if(isset($rdata["InstructorPicture"])) $InstructorPicture = $rdata["InstructorPicture"];
    if(isset($rdata["InstructorResourceID"])) $InstructorResourceID = $rdata["InstructorResourceID"];
    if(isset($rdata["InstructorsDescription"])) $InstructorsDescription = $rdata["InstructorsDescription"];
    
    if(isset($rdata["picture"])) $picture = $rdata["picture"];
    if(isset($rdata["delete_picture"])) $delete_picture = $rdata["delete_picture"];
    if(isset($rdata["old_picture"])) $old_picture = $rdata["old_picture"];
    
    global $day, $month, $year;
    
    #If we dont know the right date then make it up 
    if (!isset($day) or !isset($month) or !isset($year))
    {
    	$day   = date("d");
    	$month = date("m");
    	$year  = date("Y");
    } 
    else
    {
        // make sure the date values are valid
        ValidateDate($day, $month, $year);
    }
    
    if(isset($pview)) unset ($pview);
    
    if (empty($resource))
    	$resource = get_default_resource();
    
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else if(empty($certificate)) { $all=1; $makemodel = "&all=1"; }
    
    // if the updated values are not set
    if(!isset($first_name) or !isset($last_name) or !isset($email) or !isset($username) or !isset($password) or
         $username == "" or $password == "") 
    { 
        // show the form so the user can fill it in
    	print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    	ShowUpdateForm();
    }
    else 
    {
    	// update the user, save the old first name and last name in case this is an
    	// instructor
    	$OldFirstName = sql_query1("
    								SELECT first_name 
    								FROM AircraftScheduling_person
    								WHERE username = $username
    								");
    	$OldLastName = sql_query1("
    								SELECT last_name 
    								FROM AircraftScheduling_person
    								WHERE username = $username
    								");
    	
    	// if the user is an instructor, update the resource information
    	if ($InstructorResourceID != -1)
    	{
    		// user is an instructor, update the resource record with the name
    		$sql = "UPDATE AircraftScheduling_resource SET ";
    		$sql .= "resource_name='" . buildName($first_name, $last_name) . "' ";
    		$sql .= "WHERE resource_id = '$InstructorResourceID'";
    		$InstructorRows=sql_command($sql);
    			
    		// set the description
        	$sql1 = "UPDATE 
        	            AircraftScheduling_instructors 
        	         SET 
        	            description='$InstructorsDescription'
        	        ";
            
            // handle the uploading of an instructor picture file
    		$tmp = $_FILES['picture']['tmp_name'];
            if(is_uploaded_file($tmp)) 
            {
                if(isset($old_picture) && !@unlink($ImageRootPath . "$old_picture"))
                {
                    // error, unable to delete old picture
                    $ErrorMessage .= "Unable to delete " . $ImageRootPath . "$old_picture";
                    $InstructorPicture = "";
                }
                else if(!move_picture_into_position($tmp, $first_name . $last_name . ".jpg"))
                {
                    // error, unable to copy uploaded file
                    $ErrorMessage .= "Unable to move new picture: " . 
                                        $_FILES['picture']['name'] . 
                                        " to img/$first_name$last_name.jpg";
                    $InstructorPicture = "";
                }
                else 
                {
                    // good upload
                    $InstructorPicture = "on";
                }
            } 
            else if($delete_picture == "on")
            {
                // don't use the picture file
                // delete the picture file if it exists
                if (isset($old_picture))
                {
                    @unlink($ImageRootPath . "$old_picture");
                }
                $InstructorPicture = "";
            }
            else
            {
                // no changes, leave the picture setting alone
                if (!empty($old_picture))
                {
                    // picture is not being changed
                    $InstructorPicture = "on";
                }
                else
                {
                    // no picture for this aircraft
                    $InstructorPicture = "";
                }
            }
        	
        	if($InstructorPicture == "on") 
        		$sql1 .= ", picture='$first_name$last_name.jpg'";
        	else
        		$sql1 .= ", picture=null";
        	
        	// execute the sql statement to update the instructors information
        	$sql1 .= " WHERE resource_id=$InstructorResourceID ";
        	$sql1_result = sql_command($sql1);			
         	if ($sql1_result == -1) 
        	{
        		echo "<BR>SQL Error updating instructor info $sql1" . sql_error() . "<BR>";
        	}
    	}
    	
    	// set the allow phone number display to a valid value
    	if ($Allow_Phone_Number_Display != 1)
    	    $Allow_Phone_Number_Display = 0;
    	
    
    	// update the selected user
    	$sql = "UPDATE AircraftScheduling_person SET ";
    	$sql .= "username = '$username', password = '$password', ";
    	$sql .= "title = '$title', first_name = '$first_name', middle_name = '$middle_name', last_name = '$last_name', ";
    	$sql .= "email = '$email', address1 = '$address1', address2 = '$address2', city = '$city', ";
    	$sql .= "state = '$state', zip = '$zip', phone_number = '$phone_number', user_level = '$user_level', ";
    	$sql .= "Allow_Phone_Number_Display = $Allow_Phone_Number_Display, ";
    	$sql .= "Home_Phone = '$Home_Phone', ";
    	$sql .= "Password_Expires_Date = '" . 
    	    date("Y-m-d", adodb_mktime(0, 0, 0, date("m")  , date("d") + $PasswordTimeoutDays, date("Y"))) .
    	    "' ";
    	$sql .= "WHERE username='$username'";
    	
    	// update the user information
    	if($UserUpdateRows=sql_command($sql) != -1)
    	{
    		// user data updated, log the change in the journal
        	$Description = 
        				"User information updated after password expiration";
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    	
    		// update any existing scheduled entries with the new name and phone number
            $sql = "SELECT entry_id, name, phone_number
            		FROM AircraftScheduling_entry
            		WHERE name= '" . buildName($OldFirstName, $OldLastName) . "' ";
            $res = sql_query($sql);
             
            // format the phone numbers
            $EntryPhoneNumber = FormatPhoneNumber($phone_number, $Home_Phone);
            
            // if we have any existing entries for this user, update the name and phonenumber fields
            if(0 != ($row = sql_row_keyed($res, 0))) 
            {
                // we have some entries for this user, change the phone number name
                for($i=0; $row = sql_row($res, $i); $i++)
                {
        			$sql = "UPDATE AircraftScheduling_entry SET ";
        			$sql .= "name='" . buildName($first_name, $last_name) . "', ";
        			$sql .= "phone_number='$EntryPhoneNumber' ";
        			$sql .= "WHERE entry_id = '$row[0]'";
        			$EntryRows = sql_command($sql);
                }
            }
     
            // update the user information
            authValidateUser($username, $password);
        
        	// show the results of the update
        	print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
        	ShowRegistrationResults();
        	exit;
    	}
    	else
        {
    		print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    		echo "<H2>Error!  Unable to update your information in the database!  " . 
    		    "Please contact the systems administrator</H2>" .
    		    sql_error();
    		ShowUpdateForm();
    		include "trailer.inc";
    		exit();
    	}
    }
    
    function ShowUpdateForm() 
    {
    	global $lang, $first_name, $last_name, $middle_name, $title, $email, $username, $enablessl, $SCRIPT_NAME, $HTTPS;
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	
    	// get the user information from the database
    	$sql = "SELECT 
    	            user_level, 
    				first_name,
    				last_name,
    				email,
    				person_id,
    				user_level,
    				counter,
    				middle_name,
    				password,
    				title,
    				address1,
    				address2,
    				city,
    				state,
    				zip,
    				phone_number,
    				Allow_Phone_Number_Display,
    				Home_Phone 
    	        FROM AircraftScheduling_person 
    	        WHERE username = '$username'";
    	if(0 != ($res = sql_query($sql))) 
    	{ 
    		// database information is valid
    	    $row = sql_row($res,0);
    
        	// display the form
            echo "<form name='UserInformation' ENCTYPE='multipart/form-data' action=" . getenv("SCRIPT_NAME") . " method='POST'>";
        	echo "<CENTER><H4>" . 
        					"Your user information needs to updated. <BR>" . 
        					"Please fill in the following information to continue access." .
        					"</H4></CENTER>";
     	
        	// see if this user is an instructor
        	$FirstName = $row[1];
        	$LastName = $row[2];
        	$InstructorResourceID = sql_query1("
        								SELECT resource_id 
        								FROM AircraftScheduling_resource 
        								WHERE resource_name='" . buildName($FirstName, $LastName) . "'
        								");
        	if ($InstructorResourceID != -1)
        	{
                // user is an instructor, give them the extra fields to update
                $sql = "SELECT 
                            description, picture 
                        FROM 
                            AircraftScheduling_person a, 
                            AircraftScheduling_instructors b 
                        WHERE 
                            a.person_id=b.person_id AND
                            resource_id = '$InstructorResourceID'";
                
                $InstructorResult = sql_query($sql);
                if ($InstructorResult)
                {
                    // this user is an instructor, get the instructor information
                    $InstructorRow = sql_row($InstructorResult, 0);
                    $InstructorsDescription = $InstructorRow[0];
                    $InstructorPicture = $InstructorRow[1];
                } 
                else
                {
                    // user is not an instructor
                    $InstructorResourceID = -1;
                    $InstructorsDescription = "";
                    $InstructorPicture = 0;
                }
        	}
            else
            {
                // user is not an instructor
                $InstructorResourceID = -1;
                $InstructorsDescription = "";
                $InstructorPicture = 0;
            }
     
    		// put up the information form with the user information filled in
       	    displayUserInformation(
        	                        false, 
        	                        "UserInformation", 
        	                        $row[0], 
        	                        strtoupper($username), // username                     
        	                        $row[1],               // first_name                   
        	                        $row[2],               // last_name                    
        	                        $row[3],               // email                        
        	                        $row[4],               // person_id                    
        	                        $row[5],               // user_level                   
        	                        $row[6],               // counter                      
        	                        $row[7],               // $middle_name                 
        	                        $row[8],               // $password                    
        	                        $row[9],               // title                        
        	                        $row[10],              // address1                     
        	                        $row[11],              // address2                     
        	                        $row[12],              // city                         
        	                        $row[13],              // state                        
        	                        $row[14],              // zip                          
        	                        $row[15],              // phone_number                 
        	                        $row[16],              // Allow_Phone_Number_Display   
        	                        $row[17],              // Home_Phone                   
                                    $InstructorResourceID,
                                    $InstructorsDescription,
                                    $InstructorPicture
    								);
            echo "</center>";                                   
        	
        	// display the SSL checkbox if enabled
        	if($enablessl && !isset($HTTPS)) 
        	{
        		echo "<H2><INPUT NAME=\"usessl\" TYPE=\"checkbox\"> Use SSL (Select this option to protect your personal information)</H2>";
        	}
        }
    	?>
    	
    	<SCRIPT LANGUAGE="JavaScript">
    		document.writeln ( '<INPUT type="button" value="Submit" ONCLICK="do_submit()">' );
    	</SCRIPT>
    	<NOSCRIPT>
    		<INPUT TYPE="submit" VALUE="Submit">
    	</NOSCRIPT>
    	
    	</FORM>
    	
    	<?php
    	
    }
    
    ?>
    
    <?php
    function ShowRegistrationResults() 
    {
    	global $day, $month, $year;
    ?>
    <FORM NAME="UserInformation" ACTION=<?php 
    			echo "\"index.php?year=$year&month=$month&day=$day";
    			if (isset($resource)) echo "&resource=$resource";
    			if (isset($InstructorResource)) echo "&InstructorResource=$InstructorResource\"" 
    			?> METHOD="POST">
    <center>
    
    <td width="70%">
      <table border=0>
        <tr>
          <td><H1>Your information has been updated in the database.</H1></td>
        </tr>
      </table>
    
      <INPUT TYPE="submit" VALUE="OK">
    </center>
    </FORM>
    
    <?php
    
    }
    
    include "trailer.inc"

?>
