<?php
//-----------------------------------------------------------------------------
// 
// change_user_info.php
// 
// PURPOSE: Displays a screen so that the user can update their information.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      InstructorResource = selected instructor resource
//      makemodel - selected make and model resources
//      pview - set true to build a screen suitable for printing
//      username - username of the member
//      password - password of the member
//      title - title of the member
//      first_name - first name of the member
//      middle_name - middle name of the member
//      last_name - last name of the member
//      email - email of the member
//      address1 - address line 1 of the member
//      address2 - address line 2 of the member
//      city - city of the member
//      state - state of the member
//      zip - zip coee of the member
//      phone_number - phone number of the member
//      Allow_Phone_Number_Display - set to "on" to allow display of the member's phone number
//      Home_Phone - phone number of the member
//      InstructorResourceID - id of the instructor resource
//      InstructorsDescription - instructor's description
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
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
    
    // initialize variables
    $InstructorResource = "";
    $goback = "";
    $GoBackParameters = "";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["makemodel"])) $makemodel = $rdata["makemodel"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    
    if(isset($rdata["username"])) $username = $rdata["username"];
    if(isset($rdata["password"])) $password = $rdata["password"];
    if(isset($rdata["title"])) $title = $rdata["title"];
    if(isset($rdata["first_name"])) $first_name = $rdata["first_name"];
    if(isset($rdata["middle_name"])) $middle_name = $rdata["middle_name"];
    if(isset($rdata["last_name"])) $last_name = $rdata["last_name"];
    if(isset($rdata["email"])) $email = $rdata["email"];
    if(isset($rdata["address1"])) $address1 = $rdata["address1"];
    if(isset($rdata["address2"])) $address2 = $rdata["address2"];
    if(isset($rdata["city"])) $city = $rdata["city"];
    if(isset($rdata["state"])) $state = $rdata["state"];
    if(isset($rdata["zip"])) $zip = $rdata["zip"];
    if(isset($rdata["phone_number"])) $phone_number = $rdata["phone_number"];
    if(isset($rdata["Allow_Phone_Number_Display"])) $Allow_Phone_Number_Display = $rdata["Allow_Phone_Number_Display"];
    if(isset($rdata["Home_Phone"])) $Home_Phone = $rdata["Home_Phone"];
    if(isset($rdata["InstructorResourceID"])) $InstructorResourceID = $rdata["InstructorResourceID"];
    if(isset($rdata["InstructorsDescription"])) $InstructorsDescription = $rdata["InstructorsDescription"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    
    if(isset($rdata["picture"])) $picture = $rdata["picture"];
    if(isset($rdata["delete_picture"])) $delete_picture = $rdata["delete_picture"];
    if(isset($rdata["old_picture"])) $old_picture = $rdata["old_picture"];
    
    if($enablessl && $_SESSION["usessl"] && $HTTPS != "on") { header("Location: https://" . getenv('SERVER_NAME') . $_SERVER["PHP_SELF"]);  }
    
    #If we dont know the right date then make it up 
    if(!isset($day) or !isset($month) or !isset($year))
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
    
    if (empty($resource))
    	$resource = get_default_resource();
    
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    		
    // if the login has timed out
    if (user_logged_on() && 
    		LoginHasTimedOut() && 
    		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelNormal)
    {
    	// login has timed out, logout the user
    	user_logoff();
    	
    	// show the problem
    	showLoginTimedOut($day, $month, $year, $resource, $resource_id, $makemodel);
    	exit();
    }
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    	echo "<H2>You are not authorized to update your user information</H2>";
    	echo "<H2>Please contact the <A href='mailto:$AircraftScheduling_admin_email'>Administrator</A> to get User privledges</H2>";
    	exit();
    }
    
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    $username = isset($username) ? $username : getUserName();
    
    if($username != "" and isset($password)) 
    {
    	$sql = "SELECT password
    			FROM AircraftScheduling_person 
    			WHERE username = '$username'";
    	
    	if(strcasecmp(sql_query1($sql), $password) == 0)
    	{
    		// password valid, save the old first name and last name in case this is an
    		// instructor or we have existing schedule entries we need to update
    		$OldFirstName = sql_query1("
    									SELECT first_name 
    									FROM AircraftScheduling_person
    									WHERE username = '" . addescapes($username) . "'
    									");
    		$OldLastName = sql_query1("
    									SELECT last_name 
    									FROM AircraftScheduling_person
    									WHERE username = '" . addescapes($username) . "'
    									");
    		
    		// set the allow phone number display to a valid value
    		if ($Allow_Phone_Number_Display != 1)
    		    $Allow_Phone_Number_Display = 0;
    		    
    		// update the selected user
    		$sql = "UPDATE AircraftScheduling_person SET ";
    		$sql .= "username = '" . addescapes($username) . "', ";
    		$sql .= "title = '" . addescapes($title) . "', first_name = '" . addescapes($first_name) . "', middle_name = '" . addescapes($middle_name) . "', last_name = '" . addescapes($last_name) . "', ";
    		$sql .= "email = '" . addescapes($email) . "', address1 = '" . addescapes($address1) . "', address2 = '" . addescapes($address2) . "', city = '" . addescapes($city) . "', ";
    		$sql .= "state = '" . addescapes($state) . "', zip = '" . addescapes($zip) . "', phone_number = '" . addescapes($phone_number) . "', ";
    		$sql .= "Allow_Phone_Number_Display = $Allow_Phone_Number_Display, ";
    		$sql .= "Home_Phone = '" . addescapes($Home_Phone) . "' ";
    		$sql .= "WHERE username = '" . addescapes($username) . "'";
    		$UserUpdateRows = sql_command($sql);		
    		
    		// show the results of the change
    		if($UserUpdateRows != -1) 
    		{
        		// if the user is an instructor, update the resource information
        		if ($InstructorResourceID != -1)
        		{
        			// user is an instructor, update the resource record with the name
        			$sql = "UPDATE AircraftScheduling_resource SET ";
        			$sql .= "resource_name='" . buildName($first_name, $last_name) . "' ";
        			$sql .= "WHERE resource_id = $InstructorResourceID";
        			$InstructorRows = sql_command($sql);
        			
        			// set the picture name if one is specified
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
            			$sql .= "phone_number='" . addescapes($EntryPhoneNumber) . "' ";
            			$sql .= "WHERE entry_id = $row[0]";
            			$EntryRows = sql_command($sql);
                    }
                }
    
                // update the user information
                authValidateUser($username, $password);
            		
    			// log the change in to the journal
    			$Description = 
    						"User information changed for user " .
                            GetNameFromUsername($username);
    			CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    			
            	echo "<FORM NAME='UserInfoChanged' ACTION='index.php' METHOD='POST'>";
    			echo "<center>";
    			echo "<H1>User information updated!</H1>";
            
                BuildHiddenInputs("year=$year&month=$month&day=$day"
                                  . "&resource=$resource"
                                  . "&resource_id=$resource_id"
                                  . "&InstructorResource=$InstructorResource");
    	
    			echo "<INPUT TYPE='submit' VALUE='OK'>";
    			echo "</center>";
    			echo "</FORM>";
    		}
    		else
    		{
    			echo "<H1>Unable to update user information. Database error: " . "</H1>";
    			echo "<BR>" . sql_error() . "<BR>";
    			echo "<BR>sql: " . $sql . "<BR>";
    		}
    	}	
    	else
    	{
    		// password invalid, tell the user
            if (isset($goback)) 
        		echo "<FORM NAME='UserInfoChanged' ACTION='$goback' METHOD='POST'>";
            else 
        		echo "<FORM NAME='UserInfoChanged' ACTION='index.php' METHOD='POST'>";
    		echo "<center>";
    		echo "<H1>Unable to update user information. Password incorrect!</H1>";
            
            BuildHiddenInputs("year=$year&month=$month&day=$day"
                              . "&resource=$resource"
                              . "&resource_id=$resource_id"
                              . "&InstructorResource=$InstructorResource");
    
    		echo "<INPUT TYPE='submit' VALUE='OK'>";
    		echo "</center>";
    		echo "</FORM>";
    	}
    }
    else 
    {
        echo "<form name='change_user_info' ENCTYPE='multipart/form-data' action=" . getenv("SCRIPT_NAME") . " method='POST'>";
    
    	// get the user's information for the screen
    	$sql = "SELECT 
    				username, first_name, last_name, email, person_id, user_level, 
    				counter, middle_name,	password, title, address1,
    				address2, city, state, zip, phone_number, Allow_Phone_Number_Display,
    				Home_Phone
    			FROM AircraftScheduling_person 
    			WHERE username = '" . addescapes($username) . "'";
    	
    	$res = sql_query($sql);
    	if(0 != ($row = sql_row_keyed($res, 0))) 
    	{
    		// get the user information
    		$row = sql_row($res, 0); 
    	
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
    								"change_user_info",
    								$UserLevelNormal,
    								$row[0],				// username
    								$row[1],				// first_name
    								$row[2],				// last_name
    								$row[3],				// email
    								$row[4],				// person_id
    								$row[5],				// user_level
    								$row[6],				// counter
    								$row[7],				// $middle_name
    								$row[8],				// $password
    								$row[9],				// title
    								$row[10],				// address1
    								$row[11],				// address2
    								$row[12],				// city
    								$row[13],				// state
    								$row[14],				// zip
    								$row[15],				// phone_number
    								$row[16],               // Allow_Phone_Number_Display
    								$row[17],               // Home_Phone
                                    $InstructorResourceID,
                                    $InstructorsDescription,
                                    $InstructorPicture
    								);
        
            BuildHiddenInputs("year=$year&month=$month&day=$day"
                      . "&resource=$resource"
                      . "&goback=$goback"
                      . "&resource_id=$resource_id"
                      . "&InstructorResource=$InstructorResource");
    	}
    	
    	?>
    		
    	<SCRIPT LANGUAGE="JavaScript">
    		document.writeln ( '<CENTER><INPUT type="button" value="<?php echo $lang["change"] ?>" ONCLICK="do_submit()"></CENTER>' );
    	</SCRIPT>
    	<NOSCRIPT>
    		<INPUT TYPE="submit" VALUE="<?php echo $lang["change"]?>">
    	</NOSCRIPT>
    	
    	<?php
    }
    include "trailer.inc";
?>
